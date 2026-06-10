@extends('layouts.app')

@section('title', 'Presensi Wajah')
@section('page_title', 'Presensi & Verifikasi Wajah')

@section('content')
<div class="space-y-6" x-data="faceScanner()">

    <!-- Main Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left Side: Camera Box -->
        <div class="lg:col-span-7 bg-white rounded-3xl border border-coffee-latte p-6 coffee-card flex flex-col items-center justify-between min-h-[500px] relative overflow-hidden">
            <div class="w-full flex items-center justify-between border-b border-coffee-latte pb-4 mb-4">
                <div>
                    <h3 class="font-extrabold text-coffee-dark">Kamera Pemindai</h3>
                    <p class="text-[10px] text-coffee-medium font-semibold uppercase tracking-wider">Arahkan wajah Anda ke kamera</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full" :class="isScanning ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'"></span>
                    <span class="text-xs font-bold text-coffee-medium" x-text="isScanning ? 'Scanner Aktif' : 'Scanner Nonaktif'"></span>
                </div>
            </div>

            <!-- Video Viewport -->
            <div class="w-full max-w-md aspect-video bg-coffee-dark/5 rounded-2xl border-2 border-dashed border-coffee-latte flex items-center justify-center relative overflow-hidden group shadow-inner">
                
                <!-- Laser Scan Animation -->
                <div x-show="isScanning && !isLoadingModels" 
                     class="absolute left-0 right-0 h-0.5 bg-gradient-to-r from-transparent via-amber-500 to-transparent shadow-[0_0_8px_rgba(245,158,11,0.8)] z-10 pointer-events-none animate-scan-line"></div>

                <!-- Video Element -->
                <video id="webcam" autoplay muted playsinline class="w-full h-full object-cover rounded-2xl transform -scale-x-100" style="display: none;"></video>

                <!-- Canvas for face-api overlays (optional) -->
                <canvas id="overlayCanvas" class="absolute inset-0 w-full h-full object-cover rounded-2xl pointer-events-none transform -scale-x-100"></canvas>

                <!-- Loading State or Standby Placeholder -->
                <div x-show="!isCameraOn" class="flex flex-col items-center justify-center p-8 text-center space-y-3 z-0">
                    <div class="w-16 h-16 rounded-full bg-coffee-cream flex items-center justify-center text-coffee-medium border border-coffee-latte/30">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z"/>
                        </svg>
                    </div>
                    <p class="text-xs font-bold text-coffee-medium">Kamera Mati / Belum Dimulai</p>
                    <p class="text-[10px] text-coffee-light max-w-xs">Tekan "Buka Kamera & Mulai Scan" di bawah untuk mengaktifkan pemindai wajah.</p>
                </div>

                <!-- Camera starting state -->
                <div x-show="isCameraOn && isCameraLoading" class="flex flex-col items-center justify-center p-8 text-center space-y-3 z-0">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-coffee-dark"></div>
                    <p class="text-xs font-bold text-coffee-medium">Menyalakan kamera...</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="w-full flex flex-col sm:flex-row gap-3 mt-6 border-t border-coffee-latte pt-4">
                <button 
                    @click="toggleCamera()" 
                    :disabled="isLoadingModels"
                    class="w-full sm:w-1/2 py-3 bg-coffee-dark text-white rounded-xl font-bold text-xs hover:bg-coffee-medium transition shadow flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z"/>
                    </svg>
                    <span x-text="isCameraOn ? 'Matikan Kamera' : 'Buka Kamera & Mulai Scan'"></span>
                </button>
                <div class="w-full sm:w-1/2 flex items-center justify-center p-3 rounded-xl bg-coffee-cream text-center">
                    <p class="text-[10px] text-coffee-medium font-bold leading-normal">
                        Karyawan terdaftar: <strong class="text-coffee-dark text-sm">{{ count($karyawans) }}</strong> dengan foto wajah.
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Side: Details / Logs -->
        <div class="lg:col-span-5 flex flex-col gap-6">

            <!-- Matched Employee Details Card -->
            <div class="bg-white rounded-3xl border border-coffee-latte p-6 coffee-card flex-grow flex flex-col justify-center min-h-[300px]">
                
                <!-- Initial State / Scan Queue -->
                <div x-show="!matchedEmployee && !isLoadingModels" class="text-center p-6 space-y-4">
                    <div class="w-20 h-20 mx-auto rounded-full bg-coffee-cream flex items-center justify-center text-coffee-light border border-coffee-latte/30 animate-pulse">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-coffee-dark text-sm">Menunggu Scan</h4>
                        <p class="text-xs text-coffee-medium mt-1">Posisikan wajah Anda pada kotak pemindai untuk memulai identifikasi wajah.</p>
                    </div>
                </div>

                <!-- Unknown Employee State -->
                <div x-show="matchedEmployee === 'unknown' && !isLoadingModels" class="text-center p-6 space-y-4">
                    <div class="w-20 h-20 mx-auto rounded-full bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-500 animate-bounce">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-rose-600 text-sm">Wajah Tidak Dikenali</h4>
                        <p class="text-xs text-coffee-medium mt-1">Wajah terdeteksi tetapi tidak cocok dengan data foto karyawan terdaftar mana pun.</p>
                    </div>
                </div>

                <!-- Matched Employee Profile Details -->
                <div x-show="matchedEmployee && matchedEmployee !== 'unknown' && !isLoadingModels" class="space-y-6">
                    <div class="text-center pb-4 border-b border-coffee-latte">
                        <div class="relative w-28 h-28 mx-auto rounded-2xl overflow-hidden border-4 border-coffee-medium shadow-md">
                            <img :src="'/' + matchedEmployee.foto" alt="Profile" class="w-full h-full object-cover animate-fade-in">
                        </div>
                        <div class="inline-block mt-3 px-3 py-1 bg-emerald-100 border border-emerald-200 text-emerald-800 font-bold text-[10px] rounded-full uppercase tracking-wider">
                            Match: <span x-text="matchConfidence"></span>% Cocok
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-coffee-light font-medium uppercase tracking-wide">Nama Karyawan</span>
                            <span class="text-coffee-dark font-extrabold text-right" x-text="matchedEmployee.name"></span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-coffee-light font-medium uppercase tracking-wide">Jabatan / Pekerjaan</span>
                            <span class="px-2 py-0.5 bg-amber-50 border border-amber-200 text-coffee-medium rounded text-[10px] font-bold uppercase tracking-wider" x-text="matchedEmployee.pekerjaan"></span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-coffee-light font-medium uppercase tracking-wide">Gaji Bulanan</span>
                            <span class="text-coffee-medium font-bold" x-text="formatRupiah(matchedEmployee.gaji)"></span>
                        </div>
                    </div>

                    <div class="p-3 bg-emerald-50 border border-emerald-100 rounded-xl text-emerald-800 flex items-center gap-2.5 text-xs font-semibold">
                        <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Presensi Sukses Diverifikasi!</span>
                    </div>
                </div>

                <!-- Model Loading Status -->
                <div x-show="isLoadingModels" class="text-center p-6 space-y-4">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-coffee-dark mx-auto"></div>
                    <div>
                        <h4 class="font-extrabold text-coffee-dark text-sm" x-text="loadingTitle">Memuat Face-API AI...</h4>
                        <p class="text-[10px] text-coffee-medium mt-1 font-bold uppercase tracking-wider" x-text="loadingStep"></p>
                    </div>
                </div>
            </div>

            <!-- Terminal / Loading Logs Console -->
            <div class="bg-coffee-dark rounded-3xl border border-coffee-medium p-5 shadow-inner flex flex-col gap-2 max-h-[180px] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-coffee-medium pb-2">
                    <span class="text-[9px] font-bold text-coffee-light uppercase tracking-wider">Log Pemindai & Model AI</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                </div>
                <div class="space-y-1.5 font-mono text-[10px] text-coffee-latte/80 leading-normal" id="logsBox">
                    <template x-for="log in logs">
                        <div class="flex gap-2">
                            <span class="text-coffee-gold font-semibold">[LOG]</span>
                            <span x-text="log"></span>
                        </div>
                    </template>
                </div>
            </div>

        </div>
        
    </div>

</div>

<!-- Styles for animations -->
<style>
    @keyframes scan-line {
        0%, 100% { top: 0%; }
        50% { top: 100%; }
    }
    .animate-scan-line {
        animation: scan-line 3s linear infinite;
    }
</style>

<!-- Load Face-API.js from jsDelivr CDN -->
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

<script>
    function faceScanner() {
        return {
            isCameraOn: false,
            isCameraLoading: false,
            isScanning: false,
            isLoadingModels: false,
            isProcessingFace: false,
            loadingTitle: '',
            loadingStep: '',
            logs: [],
            matchedEmployee: null,
            matchConfidence: 0,
            labeledDescriptors: [],
            faceMatcher: null,
            stream: null,
            scanInterval: null,

            init() {
                this.addLog("Aplikasi pemindai wajah siap.");
            },

            addLog(msg) {
                this.logs.push(msg);
                this.$nextTick(() => {
                    const box = document.getElementById('logsBox');
                    if (box) box.parentElement.scrollTop = box.parentElement.scrollHeight;
                });
            },

            async loadModelsAndProcessImages() {
                if (this.labeledDescriptors.length > 0) return true;

                this.isLoadingModels = true;
                this.loadingTitle = 'Mengunduh Model AI';
                this.addLog("Memulai pengunduhan model AI face-api.js (Versi Ringan)...");

                const MODEL_URL = 'https://justadudewhohacks.github.io/face-api.js/models';

                try {
                    this.loadingStep = 'Memuat TinyFaceDetector Model (1/3)';
                    this.addLog("Memuat model deteksi wajah ringan TinyFaceDetector...");
                    await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);

                    this.loadingStep = 'Memuat FaceLandmark68 Model (2/3)';
                    this.addLog("Memuat model penanda wajah (Landmark 68)...");
                    await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);

                    this.loadingStep = 'Memuat FaceRecognition Model (3/3)';
                    this.addLog("Memuat model pengenalan/vektor wajah...");
                    await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);

                    this.addLog("Semua model AI ringan berhasil dimuat.");
                    
                    // Warm up TensorFlow.js to compile shaders before processing photos
                    this.loadingStep = 'Menghangatkan Mesin AI...';
                    this.addLog("Menginisialisasi backend AI (Warming up)...");
                    const dummyCanvas = document.createElement('canvas');
                    dummyCanvas.width = 10;
                    dummyCanvas.height = 10;
                    try {
                        await faceapi.detectSingleFace(dummyCanvas, new faceapi.TinyFaceDetectorOptions({ inputSize: 128 }))
                            .withFaceLandmarks()
                            .withFaceDescriptor();
                    } catch(e) {
                        console.warn("Warm up warning:", e);
                    }
                    this.addLog("Mesin AI siap.");

                    // Now process registered worker photos
                    this.loadingTitle = 'Memproses Foto Karyawan';
                    const employees = @json($karyawans);

                    if (employees.length === 0) {
                        this.addLog("Peringatan: Tidak ada karyawan dengan foto terdaftar.");
                        this.isLoadingModels = false;
                        return true;
                    }

                    for (let i = 0; i < employees.length; i++) {
                        const emp = employees[i];
                        this.loadingStep = `Memuat Referensi (${i + 1}/${employees.length}): ${emp.nama_karyawan}`;
                        this.addLog(`[Database] Membaca foto referensi: ${emp.nama_karyawan}...`);

                        // Yield control to the browser UI thread to keep spinner and logs updating smoothly
                        await new Promise(resolve => setTimeout(resolve, 50));

                        try {
                            // Run the image fetch and face detection inside a 6-second timeout race
                            const detections = await Promise.race([
                                (async () => {
                                    const imgUrl = '/' + emp.foto;
                                    
                                    // Use browser's native Image loader (avoids fetch() CORS & cache resolution delays)
                                    const img = await new Promise((resolve, reject) => {
                                        const i = new Image();
                                        i.onload = () => resolve(i);
                                        i.onerror = () => reject(new Error('Gagal memuat file gambar'));
                                        i.src = imgUrl;
                                    });
                                    
                                    // Draw onto a small 160x160 offscreen canvas.
                                    // This reduces pixel count by up to 99%, making detection instant and memory-efficient.
                                    const canvas = document.createElement('canvas');
                                    canvas.width = 160;
                                    canvas.height = 160;
                                    const ctx = canvas.getContext('2d');
                                    ctx.drawImage(img, 0, 0, 160, 160);
                                    
                                    return await faceapi.detectSingleFace(canvas, new faceapi.TinyFaceDetectorOptions({ inputSize: 160, scoreThreshold: 0.3 }))
                                        .withFaceLandmarks()
                                        .withFaceDescriptor();
                                })(),
                                new Promise((_, reject) => setTimeout(() => reject(new Error('Waktu pemrosesan habis (6 detik)')), 6000))
                            ]);

                            if (detections) {
                                const labelObj = {
                                    id: emp.id_karyawan,
                                    name: emp.nama_karyawan,
                                    pekerjaan: emp.jabatan ? emp.jabatan.nama_jabatan : emp.pekerjaan,
                                    gaji: emp.gaji,
                                    foto: emp.foto
                                };
                                this.labeledDescriptors.push(new faceapi.LabeledFaceDescriptors(
                                    JSON.stringify(labelObj),
                                    [detections.descriptor]
                                ));
                                this.addLog(`[Database] Foto ${emp.nama_karyawan} siap dicocokkan.`);
                            } else {
                                this.addLog(`[Database] Gagal mendeteksi wajah pada foto referensi ${emp.nama_karyawan}.`);
                            }
                        } catch (err) {
                            this.addLog(`[Database] Gagal memuat foto referensi ${emp.nama_karyawan}: ${err.message}`);
                        }
                    }

                    if (this.labeledDescriptors.length > 0) {
                        this.faceMatcher = new faceapi.FaceMatcher(this.labeledDescriptors, 0.68); // 0.68 distance threshold (more forgiving for TinyFaceDetector)
                        this.addLog("Penilai kemiripan wajah (FaceMatcher) terinisialisasi.");
                    } else {
                        this.addLog("Error: Tidak ada wajah karyawan terdaftar yang valid.");
                    }

                    this.isLoadingModels = false;
                    return true;
                } catch (e) {
                    this.addLog(`Error memuat model AI: ${e.message}`);
                    this.isLoadingModels = false;
                    alert("Gagal memuat model AI. Silakan periksa koneksi internet Anda.");
                    return false;
                }
            },

            async toggleCamera() {
                if (this.isCameraOn) {
                    this.stopCameraAndScanning();
                } else {
                    const success = await this.loadModelsAndProcessImages();
                    if (success) {
                        await this.startCameraAndScanning();
                    }
                }
            },

            async startCameraAndScanning() {
                this.isCameraOn = true;
                this.isCameraLoading = true;
                this.addLog("Membuka perangkat kamera...");

                const video = document.getElementById('webcam');

                try {
                    this.stream = await navigator.mediaDevices.getUserMedia({ 
                        video: { width: 640, height: 480 } 
                    });
                    video.srcObject = this.stream;
                    video.style.display = 'block';
                    this.isCameraLoading = false;
                    this.isScanning = true;
                    this.addLog("Kamera berhasil terhubung. Mulai pemindaian...");

                    // Start scanning loop
                    this.scanInterval = setInterval(async () => {
                        await this.scanFace(video);
                    }, 800);

                } catch (err) {
                    this.addLog(`Gagal membuka kamera: ${err.message}`);
                    this.isCameraOn = false;
                    this.isCameraLoading = false;
                    alert("Akses kamera ditolak atau perangkat kamera tidak ditemukan.");
                }
            },

            stopCameraAndScanning() {
                this.isScanning = false;
                this.isCameraOn = false;
                this.isProcessingFace = false;
                this.matchedEmployee = null;

                if (this.scanInterval) {
                    clearInterval(this.scanInterval);
                    this.scanInterval = null;
                }

                if (this.stream) {
                    this.stream.getTracks().forEach(track => track.stop());
                    this.stream = null;
                }

                const video = document.getElementById('webcam');
                if (video) {
                    video.srcObject = null;
                    video.style.display = 'none';
                }

                const canvas = document.getElementById('overlayCanvas');
                if (canvas) {
                    const ctx = canvas.getContext('2d');
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                }

                this.addLog("Kamera dan pemindaian dihentikan.");
            },

            async scanFace(video) {
                if (!this.isScanning || !this.faceMatcher || this.isProcessingFace) return;
                this.isProcessingFace = true;

                try {
                    const canvas = document.getElementById('overlayCanvas');
                    const displaySize = { width: video.videoWidth || 640, height: video.videoHeight || 480 };
                    
                    if (canvas.width !== displaySize.width || canvas.height !== displaySize.height) {
                        canvas.width = displaySize.width;
                        canvas.height = displaySize.height;
                    }

                    // Detect single face using lightweight TinyFaceDetector (inputSize 224 is fast, but much better at distance/details)
                    const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.3 }))
                        .withFaceLandmarks()
                        .withFaceDescriptor();

                    const ctx = canvas.getContext('2d');
                    ctx.clearRect(0, 0, canvas.width, canvas.height);

                    if (detection) {
                        // Draw landmark box for user feedback
                        const resizedDetections = faceapi.resizeResults(detection, displaySize);
                        faceapi.draw.drawDetections(canvas, resizedDetections);

                        // Compare
                        const bestMatch = this.faceMatcher.findBestMatch(detection.descriptor);
                        const distance = bestMatch.distance;
                        this.matchConfidence = Math.round((1 - distance) * 100);

                        if (bestMatch.label !== 'unknown') {
                            try {
                                const empData = JSON.parse(bestMatch.label);
                                this.matchedEmployee = empData;
                                this.addLog(`Wajah dikenali sebagai: ${empData.name} (${this.matchConfidence}% Cocok, Jarak: ${distance.toFixed(2)})`);
                            } catch (e) {
                                this.addLog("Gagal memparsing data wajah terdeteksi.");
                            }
                        } else {
                            this.matchedEmployee = 'unknown';
                            
                            // Log details of the closest mismatch candidate to help debug thresholds
                            let closestName = 'Tidak Dikenal';
                            if (bestMatch.label && bestMatch.label !== 'unknown') {
                                try {
                                    closestName = JSON.parse(bestMatch.label).name;
                                } catch(e) {}
                            }
                            this.addLog(`Wajah terdeteksi tetapi tidak dikenal. Paling mendekati: ${closestName} (Jarak: ${distance.toFixed(2)}, butuh < 0.68)`);
                        }
                    } else {
                        this.matchedEmployee = null;
                    }
                } catch (e) {
                    console.error("Face scan execution error:", e);
                } finally {
                    this.isProcessingFace = false;
                }
            },

            formatRupiah(amount) {
                if (!amount) return 'Rp 0';
                return 'Rp ' + amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }
        };
    }
</script>
@endsection
