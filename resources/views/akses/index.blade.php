@extends('layouts.app')

@section('title', 'Hak Akses')
@section('page_title', 'Pengaturan Hak Akses')

@section('content')
<div class="space-y-6">

    <!-- Permissions Form Matrix -->
    <form action="{{ route('akses.update') }}" method="POST" class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card space-y-6">
        @csrf
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-coffee-latte text-xs font-bold text-coffee-light uppercase tracking-wider">
                        <th class="pb-3 pr-4">Akses</th>
                        @foreach($roles as $role)
                            <th class="pb-3 text-center uppercase tracking-wide px-3">{{ $role->role }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-coffee-latte font-medium text-coffee-dark">
                    @foreach($modules as $moduleKey => $moduleLabel)
                        <tr>
                            <td class="py-4">
                                <div class="font-bold text-coffee-dark">{{ $moduleLabel }}</div>
                            </td>
                            @foreach($roles as $role)
                                @php
                                    $rule = $role->accesses->where('modul', $moduleKey)->first();
                                    $isAllowed = $rule && $rule->allowed === '1';
                                @endphp
                                <td class="py-4 text-center px-3">
                                    <div class="inline-flex items-center justify-center">
                                        <!-- Hidden input to submit 0 if checkbox unchecked -->
                                        <input type="hidden" name="access[{{ $role->id_role }}][{{ $moduleKey }}]" value="0">
                                        <input 
                                            type="checkbox" 
                                            name="access[{{ $role->id_role }}][{{ $moduleKey }}]" 
                                            value="1" 
                                            {{ $isAllowed ? 'checked' : '' }}
                                            class="w-4.5 h-4.5 rounded border-coffee-latte text-coffee-dark focus:ring-coffee-light bg-coffee-cream accent-amber-950 cursor-pointer"
                                        >
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-coffee-latte">
            <button type="submit" class="px-5 py-2.5 bg-coffee-dark text-white rounded-xl text-xs font-bold hover:bg-coffee-medium transition shadow cursor-pointer">
                Simpan Perubahan
            </button>
        </div>
    </form>

</div>
@endsection
