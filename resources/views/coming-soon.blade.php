@extends('layouts.app')

@section('title', $title ?? 'Segera Hadir')
@section('page-title', $title ?? 'Segera Hadir')

@section('content')
<div class="flex flex-col items-center justify-center py-20 text-center">
    <div class="w-20 h-20 bg-indigo-100 rounded-2xl flex items-center justify-center mb-6">
        <svg class="w-10 h-10 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
        </svg>
    </div>
    <h2 class="text-xl font-bold text-gray-800 mb-2">{{ $title ?? 'Modul Ini' }} — Sedang Dikembangkan</h2>
    <p class="text-gray-500 max-w-md">Modul ini sedang dalam proses pengembangan. Akan segera tersedia di milestone berikutnya.</p>
    <a href="{{ route('dashboard') }}" class="mt-6 inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali ke Dashboard
    </a>
</div>
@endsection
