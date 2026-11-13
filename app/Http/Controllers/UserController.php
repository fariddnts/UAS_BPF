<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dataUser = User::query()
            ->orderByRaw(
                'CASE role WHEN ? THEN 0 WHEN ? THEN 1 WHEN ? THEN 2 WHEN ? THEN 3 ELSE 4 END',
                [User::ROLE_OWNER, User::ROLE_ADMIN, User::ROLE_MEKANIK, User::ROLE_USER]
            )
            ->orderBy('name')
            ->get();

        return view('admin.user.index', compact('dataUser'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.user.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi di luar try agar error validasi tampil lewat $errors
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'role'     => ['required', Rule::in(User::ROLES)],
            'password' => 'required|min:8|confirmed',
        ]);

        try {
            $data['password'] = Hash::make($request->password);
            User::create($data);

            return redirect()->route('user.index')
                ->with('success', 'Data user berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Data user gagal disimpan.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Tidak dipakai (route didaftarkan dengan ->except('show'))
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $dataUser = User::findOrFail($id);
        return view('admin.user.edit', compact('dataUser'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'role'     => ['required', Rule::in(User::ROLES)],
            'password' => 'nullable|min:8|confirmed',
        ]);

        // Admin tidak boleh menurunkan role akunnya sendiri
        if ($user->id === $request->user()->id && $data['role'] !== User::ROLE_ADMIN) {
            return back()->withInput()
                ->with('error', 'Anda tidak dapat mengubah role akun Anda sendiri.');
        }

        try {
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            } else {
                unset($data['password']);
            }

            $user->update($data);

            return redirect()->route('user.index')
                ->with('success', 'Data user berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Data user gagal diperbarui.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        // Admin tidak boleh menghapus akunnya sendiri
        if ($user->id === auth()->id()) {
            return redirect()->route('user.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        try {
            $user->delete();
            return redirect()->route('user.index')
                ->with('success', 'Data user berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('user.index')
                ->with('error', 'Data user gagal dihapus.');
        }
    }
}
