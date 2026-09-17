<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    /**
     * Tampilkan daftar folder (bisa difilter berdasarkan department_id atau parent_id).
     */
    public function index(Request $request)
    {
        $query = Folder::with(['department', 'user', 'children', 'files']);

        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        } else {
            // Default tampilkan root folder jika parent_id tidak dispesifikasikan secara khusus
            // atau biarkan kosong jika ingin mengambil semua tergantung kebutuhan frontend.
        }

        $folders = $query->get();

        return response()->json([
            'message' => 'Berhasil mengambil daftar folder',
            'data' => $folders
        ]);
    }

    /**
     * Buat folder baru (bisa root folder atau sub-folder).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'parent_id' => 'nullable|exists:folders,id',
        ]);

        // Masukkan ID user yang sedang login sebagai pembuat folder
        $validated['user_id'] = $request->user()->id;

        $folder = Folder::create($validated);

        return response()->json([
            'message' => 'Folder berhasil dibuat',
            'data' => $folder
        ], 201);
    }

    /**
     * Tampilkan detail folder tertentu.
     */
    public function show(Folder $folder)
    {
        $folder->load(['department', 'user', 'parent', 'children', 'files']);

        return response()->json([
            'message' => 'Detail folder berhasil diambil',
            'data' => $folder
        ]);
    }

    /**
     * Perbarui / Rename nama folder.
     */
    public function update(Request $request, Folder $folder)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'sometimes|exists:departments,id',
            'parent_id' => 'nullable|exists:folders,id',
        ]);

        $folder->update($validated);

        return response()->json([
            'message' => 'Folder berhasil diperbarui',
            'data' => $folder
        ]);
    }

    /**
     * Hapus folder (beserta sub-folder dan file di dalamnya jika menggunakan onDelete cascade).
     */
    public function destroy(Folder $folder)
    {
        $folder->delete();

        return response()->json([
            'message' => 'Folder berhasil dihapus'
        ]);
    }
}