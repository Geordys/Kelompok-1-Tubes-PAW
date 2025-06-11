<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Response;



class UserController extends Controller
{
    public function index()
    {
        // Ambil hanya kolom penting
        return response()->json(
            User::select('id', 'name', 'email', 'role')->get()
        );
    }

    public function show($id)
    {
        $user = User::find($id);
        if (!$user) return response()->json(['error' => 'Not found'], 404);
        return response()->json($user);
    }

    public function destroy($id)
{
    $user = User::find($id);

    if (!$user) {
        return response()->json(['message' => 'User tidak ditemukan'], 404);
    }

    // Jangan biarkan superadmin hapus dirinya sendiri
    if (auth()->id() === $user->id) {
        return response()->json(['message' => 'Tidak bisa menghapus diri sendiri'], 403);
    }

    $user->delete();
    return response()->json(['message' => 'User berhasil dihapus']);
}

    public function export()
    {
        $users = User::select('id', 'name', 'email', 'role')->get();
        $csv = "ID,Name,Email,Role\n";

        foreach ($users as $user) {
            $csv .= "{$user->id},\"{$user->name}\",{$user->email},{$user->role}\n";
        }

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users.csv"',

        ]);
    }

    public function createAdmin(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string',
        'email' => 'required|email|unique:users',
        'password' => 'required|string|min:6'
    ]);

    $admin = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => \Hash::make($validated['password']),
        'role' => 'admin'
    ]);

    return response()->json(['message' => 'Admin created', 'admin' => $admin]);
}

public function makeAdmin($id)
{
    $user = User::findOrFail($id);
    $user->role = 'admin';
    $user->save();

    return response()->json(['message' => 'User upgraded to admin']);
}

public function makeUser($id)
{
    $user = User::findOrFail($id);
    $user->role = 'user';
    $user->save();

    return response()->json(['message' => 'User downgraded to user']);
}

}
