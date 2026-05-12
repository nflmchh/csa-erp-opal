@extends('layouts.app')
@section('title', 'Role & Permission')
@section('page-title', 'Role & Permission')
@section('breadcrumb', 'Administrasi / Role & Permission')

@section('content')
<div class="space-y-4">

    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $roles->count() }} role terdaftar</p>
        <a href="{{ route('admin.roles.create') }}"
            class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-lg text-sm">
            + Tambah Role
        </a>
    </div>

    <div class="space-y-3">
        @forelse($roles as $role)
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-2">
                        <h3 class="font-semibold text-gray-800 capitalize">{{ $role->name }}</h3>
                        <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">
                            {{ $role->permissions->count() }} permission
                        </span>
                    </div>
                    @if($role->permissions->count())
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($role->permissions->sortBy('name') as $perm)
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-md">{{ $perm->name }}</span>
                        @endforeach
                    </div>
                    @else
                    <p class="text-xs text-gray-400 italic">Tidak ada permission</p>
                    @endif
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('admin.roles.edit', $role) }}"
                        class="text-xs text-indigo-600 border border-indigo-200 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg">
                        Edit
                    </a>
                    @if($role->name !== 'superadmin')
                    <form method="POST" action="{{ route('admin.roles.destroy', $role) }}"
                        onsubmit="return confirm('Hapus role {{ $role->name }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-600 border border-red-200 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg">
                            Hapus
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-200 py-16 text-center text-gray-400">
            Belum ada role
        </div>
        @endforelse
    </div>

</div>
@endsection
