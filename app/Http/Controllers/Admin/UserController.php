<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use App\Models\Warehouse; // Pastikan Model Warehouse di-import
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Menambahkan 'stores' dan 'warehouses' ke eager loading untuk optimasi query di view
        $users = User::with(['roles', 'stores', 'warehouses'])
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")->orWhere('email', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $roles = Role::orderBy('name')->get();
        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles  = Role::orderBy('name')->get();
        $stores = Store::where('is_active', true)->orderBy('name')->get();
        
        // Mengambil data gudang untuk dikirim ke View
        $warehouses = Warehouse::orderBy('name')->get(); 
        
        return view('admin.users.create', compact('roles', 'stores', 'warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:100'],
            'email'           => ['required', 'email', 'unique:users,email'],
            'password'        => ['required', 'string', 'min:8', 'confirmed'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'role'            => ['required', 'exists:roles,name'],
            'store_id'        => ['nullable', 'exists:stores,id'],
            // Validasi untuk array ID Gudang
            'warehouse_ids'   => ['nullable', 'array'], 
            'warehouse_ids.*' => ['exists:warehouses,id'],
            'is_active'       => ['boolean'],
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'phone'     => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $user->assignRole($validated['role']);

        // Penugasan Toko
        if (!empty($validated['store_id'])) {
            $user->stores()->attach($validated['store_id'], ['is_primary' => true]);
        }

        // Penugasan Gudang (Bisa lebih dari 1)
        if (!empty($validated['warehouse_ids'])) {
            $user->warehouses()->sync($validated['warehouse_ids']);
        }

        AuditLogService::log('create', 'users', "User '{$user->name}' dibuat", null, ['role' => $validated['role']], User::class, $user->id);
        return redirect()->route('admin.users.index')->with('success', "Akun '{$user->name}' berhasil dibuat.");
    }

    public function edit(User $user)
    {
        $roles  = Role::orderBy('name')->get();
        $stores = Store::where('is_active', true)->orderBy('name')->get();
        
        // Mengambil data gudang untuk dikirim ke View
        $warehouses = Warehouse::orderBy('name')->get(); 
        
        return view('admin.users.edit', compact('user', 'roles', 'stores', 'warehouses'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:100'],
            'email'           => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password'        => ['nullable', 'string', 'min:8'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'role'            => ['required', 'exists:roles,name'],
            'store_id'        => ['nullable', 'exists:stores,id'],
            // Validasi untuk array ID Gudang
            'warehouse_ids'   => ['nullable', 'array'], 
            'warehouse_ids.*' => ['exists:warehouses,id'],
            'is_active'       => ['boolean'],
        ]);

        $updateData = [
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'phone'     => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ];
        
        if (filled($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);
        $user->syncRoles([$validated['role']]);

        // Update Relasi Toko
        if (!empty($validated['store_id'])) {
            $user->stores()->sync([$validated['store_id'] => ['is_primary' => true]]);
        } else {
            $user->stores()->detach();
        }

        // Update Relasi Gudang (Sync otomatis menambah yang baru dan menghapus yang tidak dicentang)
        if (isset($validated['warehouse_ids'])) {
            $user->warehouses()->sync($validated['warehouse_ids']);
        } else {
            // Jika kosong/tidak ada centang sama sekali
            $user->warehouses()->detach(); 
        }

        AuditLogService::log('update', 'users', "User '{$user->name}' diperbarui", null, null, User::class, $user->id);
        return redirect()->route('admin.users.index')->with('success', "Akun '{$user->name}' berhasil diperbarui.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }
        $name = $user->name;
        $user->delete();
        AuditLogService::log('delete', 'users', "User '{$name}' dihapus");
        return redirect()->route('admin.users.index')->with('success', "Akun '{$name}' berhasil dihapus.");
    }
}