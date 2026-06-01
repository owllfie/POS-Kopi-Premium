<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class BackupController extends Controller
{
    private function getActiveUser()
    {
        if (session()->has('simulated_user_id')) {
            return User::find(session('simulated_user_id'));
        }
        return Auth::user();
    }

    private function ensureBackupDirectory()
    {
        $dir = storage_path('app/backups');
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    public function index()
    {
        $dir = $this->ensureBackupDirectory();
        $files = [];
        
        $allFiles = scandir($dir);
        foreach ($allFiles as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $filePath = $dir . '/' . $file;
            $files[] = [
                'filename' => $file,
                'size' => round(filesize($filePath) / 1024, 2), // KB
                'created_at' => date('d M Y H:i:s', filemtime($filePath)),
            ];
        }

        // Sort backups by creation date descending
        usort($files, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        // Paginate backup files array
        $perPage = 10;
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $currentItems = array_slice($files, ($currentPage - 1) * $perPage, $perPage);
        $files = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems, 
            count($files), 
            $perPage, 
            $currentPage, 
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => request()->query()]
        );

        return view('backup.index', compact('files'));
    }

    public function create(Request $request)
    {
        $superadmin = $this->getActiveUser();
        $dir = $this->ensureBackupDirectory();
        
        $backupName = 'POS_backup_' . date('Ymd_His') . '.sql';
        $destination = $dir . '/' . $backupName;

        try {
            $connection = \Illuminate\Support\Facades\DB::connection();
            $driver = $connection->getDriverName();
            
            $sqlContent = "-- POS Restaurant Database Dump\n";
            $sqlContent .= "-- Generated on " . date('Y-m-d H:i:s') . "\n";
            $sqlContent .= "-- Driver: " . $driver . "\n\n";
            
            if ($driver === 'mysql') {
                $sqlContent .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
                
                // Get all tables
                $tables = [];
                $dbName = $connection->getDatabaseName();
                $rows = $connection->select("SHOW TABLES");
                $key = 'Tables_in_' . $dbName;
                foreach ($rows as $row) {
                    $tables[] = $row->$key;
                }
                
                foreach ($tables as $table) {
                    $sqlContent .= "-- ------------------------------------------------------\n";
                    $sqlContent .= "-- Table structure for table `{$table}`\n";
                    $sqlContent .= "-- ------------------------------------------------------\n";
                    $sqlContent .= "DROP TABLE IF EXISTS `{$table}`;\n";
                    
                    $createRow = $connection->select("SHOW CREATE TABLE `{$table}`");
                    if (!empty($createRow)) {
                        $sqlContent .= $createRow[0]->{'Create Table'} . ";\n\n";
                    }
                    
                    $sqlContent .= "-- Dumping data for table `{$table}`\n";
                    $data = $connection->table($table)->get();
                    if ($data->isNotEmpty()) {
                        foreach ($data as $item) {
                            $arrayItem = (array)$item;
                            $columns = array_keys($arrayItem);
                            $escapedColumns = array_map(function($c) { return "`$c`"; }, $columns);
                            
                            $escapedValues = array_map(function($val) {
                                if (is_null($val)) return 'NULL';
                                return "'" . addslashes($val) . "'";
                            }, array_values($arrayItem));
                            
                            $sqlContent .= "INSERT INTO `{$table}` (" . implode(', ', $escapedColumns) . ") VALUES (" . implode(', ', $escapedValues) . ");\n";
                        }
                    }
                    $sqlContent .= "\n";
                }
                
                $sqlContent .= "SET FOREIGN_KEY_CHECKS=1;\n";
            } else {
                // Fallback for sqlite
                if ($driver === 'sqlite') {
                    $tables = [];
                    $rows = $connection->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
                    foreach ($rows as $row) {
                        $tables[] = $row->name;
                    }
                    foreach ($tables as $table) {
                        $sqlContent .= "DROP TABLE IF EXISTS `{$table}`;\n";
                        $createRow = $connection->select("SELECT sql FROM sqlite_master WHERE type='table' AND name='{$table}'");
                        if (!empty($createRow)) {
                            $sqlContent .= $createRow[0]->sql . ";\n\n";
                        }
                        $data = $connection->table($table)->get();
                        foreach ($data as $item) {
                            $arrayItem = (array)$item;
                            $columns = array_keys($arrayItem);
                            $escapedColumns = array_map(function($c) { return "`$c`"; }, $columns);
                            $escapedValues = array_map(function($val) {
                                if (is_null($val)) return 'NULL';
                                return "'" . addslashes($val) . "'";
                            }, array_values($arrayItem));
                            $sqlContent .= "INSERT INTO `{$table}` (" . implode(', ', $escapedColumns) . ") VALUES (" . implode(', ', $escapedValues) . ");\n";
                        }
                        $sqlContent .= "\n";
                    }
                } else {
                    throw new \Exception("Unsupported database driver: " . $driver);
                }
            }

            file_put_contents($destination, $sqlContent);

            ActivityLog::create([
                'id_user' => $superadmin->id_user,
                'aktivitas' => 'DATABASE_BACKUP',
                'detail_aktivitas' => "Created SQL database backup: {$backupName}",
                'ip_address' => $request->ip(),
            ]);

            return back()->with('success', "Database berhasil di-backup. File: {$backupName}");

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mem-backup database: ' . $e->getMessage());
        }
    }

    public function download($filename)
    {
        $dir = $this->ensureBackupDirectory();
        $filePath = $dir . '/' . $filename;

        // Prevent directory traversal attacks
        if (str_contains($filename, '..') || str_contains($filename, '/') || str_contains($filename, '\\')) {
            abort(403, 'Akses ilegal.');
        }

        if (file_exists($filePath)) {
            return response()->download($filePath);
        }

        return back()->with('error', 'File backup tidak ditemukan.');
    }

    public function delete(Request $request, $filename)
    {
        $superadmin = $this->getActiveUser();
        $dir = $this->ensureBackupDirectory();
        $filePath = $dir . '/' . $filename;

        if (str_contains($filename, '..') || str_contains($filename, '/') || str_contains($filename, '\\')) {
            abort(403, 'Akses ilegal.');
        }

        if (file_exists($filePath)) {
            unlink($filePath);
            
            ActivityLog::create([
                'id_user' => $superadmin->id_user,
                'aktivitas' => 'DELETE_BACKUP',
                'detail_aktivitas' => "Deleted backup file: {$filename}",
                'ip_address' => $request->ip(),
            ]);

            return back()->with('success', 'File backup berhasil dihapus.');
        }

        return back()->with('error', 'File backup tidak ditemukan.');
    }
}
