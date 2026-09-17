<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    /**
     * Menampilkan daftar folder (berdasarkan parent atau root, dan department user).
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Folder::with('user', 'department');

        // Jika user bukan admin pusat, filter berdasarkan department atau folder publik/pribadi miliknya
        if ($user->role !== 'admin') {
            $query->where('department_id', $user->department_id);
        }

        // Filter parent_id (jika null berarti root folder, jika ada angka berarti sub-folder)
        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        } else {
            $query->whereNull('parent_id');
        }

        $folders = $query->get();

        return response()->json([
            'message' => 'Berhasil mengambil data folder',
            'data' => $folders
        ], 200);
    }

    /**
     * Membuat folder baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:folders,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $user = $request->user();

        $folder = Folder::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'department_id' => $request->department_id ?? $user->department_id,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Folder berhasil dibuat',
            'data' => $folder
        ], 201);
    }

    /**
     * Menampilkan detail folder beserta isi sub-folder dan file di dalamnya.
     */
    public function show($id)
    {
        // Ubah subFolders menjadi children sesuai model Anda
        $folder = Folder::with(['children', 'files', 'user', 'department'])->findOrFail($id);

        return response()->json([
            'message' => 'Detail folder ditemukan',
            'data' => $folder
        ], 200);
    }

    /**
     * Menghapus folder.
     */
    public function destroy($id)
    {
        $folder = Folder::findOrFail($id);

        // Hapus folder (pastikan cascade atau hapus child-nya jika perlu)
        $folder->delete();

        return response()->json([
            'message' => 'Folder berhasil dihapus'
        ], 200);
    }

    /**
     * Mengubah nama folder (Rename).
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $folder = Folder::findOrFail($id);
        $folder->update([
            'name' => $request->name
        ]);

        return response()->json([
            'message' => 'Nama folder berhasil diperbarui',
            'data' => $folder
        ], 200);
    }
}
