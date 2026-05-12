@extends('layouts.app')
@section('title', 'Audit Log')
@section('page-title', 'Audit Log')
@section('breadcrumb', 'Administrasi / Audit Log')

@section('content')
<div class="space-y-4">
    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap gap-3">
        <select name="module" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Semua Modul</option>
            @foreach(['users','brands','categories','product_types','warehouses','stores','products','shipments','transfers','sales','returns','opname','finance'] as $mod)
            <option value="{{ $mod }}" {{ request('module') == $mod ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$mod)) }}</option>
            @endforeach
        </select>
        <select name="action" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Semua Aksi</option>
            @foreach(['create','update','delete','login','export','print','approve','receive','transfer','return','adjust'] as $act)
            <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>{{ ucfirst($act) }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-gray-800 text-white text-sm px-4 py-2 rounded-lg">Filter</button>
        <a href="{{ route('admin.audit-logs.index') }}" class="bg-gray-100 text-gray-700 text-sm px-4 py-2 rounded-lg">Reset</a>
    </form>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Waktu</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">User</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Modul</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Deskripsi</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php
                    $actionColors = [
                        'create'   => 'bg-green-100 text-green-700',
                        'update'   => 'bg-blue-100 text-blue-700',
                        'delete'   => 'bg-red-100 text-red-700',
                        'login'    => 'bg-purple-100 text-purple-700',
                        'approve'  => 'bg-teal-100 text-teal-700',
                        'export'   => 'bg-yellow-100 text-yellow-700',
                    ];
                    @endphp
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-gray-400 text-xs whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td class="px-4 py-2 text-gray-700 text-xs">{{ $log->user?->name ?? 'System' }}</td>
                        <td class="px-4 py-2">
                            <span class="{{ $actionColors[$log->action] ?? 'bg-gray-100 text-gray-700' }} text-xs px-2 py-0.5 rounded-full">{{ $log->action }}</span>
                        </td>
                        <td class="px-4 py-2 text-gray-500 text-xs">{{ $log->module }}</td>
                        <td class="px-4 py-2 text-gray-600 text-xs max-w-xs truncate" title="{{ $log->description }}">{{ $log->description }}</td>
                        <td class="px-4 py-2 text-gray-400 text-xs font-mono">{{ $log->ip_address }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">Belum ada audit log</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div class="border-t border-gray-200 px-4 py-3">{{ $logs->links() }}</div>
        @endif
    </div>
</div>
@endsection
