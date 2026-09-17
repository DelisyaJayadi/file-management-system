<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Mengunggah file baru ke folder tertentu.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:10240', // Maksimal 10MB per file
            'folder_id' => 'nullable|exists:folders,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $mimeType = $uploadedFile->getClientMimeType();
        $size = $uploadedFile->getSize();

        // Simpan file ke disk public (storage/app/public/files)
        $path = $uploadedFile->store('files', 'public');

        $user = $request->user();

        // Simpan informasi file ke database
        $file = File::create([
            'name' => $originalName,
            'title' => $request->title,
            'path' => $path,
            'mime_type' => $mimeType,
            'size' => $size,
            'folder_id' => $request->folder_id,
            'department_id' => $request->department_id ?? $user->department_id,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'File berhasil diunggah',
            'data' => $file
        ], 201);
    }

    /**
     * Memperbarui informasi file (Edit File).
     */
    public function update(Request $request, $id)
    {
        $file = File::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $file->update([
            'title' => $request->title,
            'department_id' => $request->department_id ?? $file->department_id,
        ]);

        return response()->json([
            'message' => 'Informasi file berhasil diperbarui',
            'data' => $file
        ], 200);
    }

    /**
     * Mengunduh file berdasarkan ID.
     */
    public function download($id)
    {
        $file = File::findOrFail($id);

        $fullPath = storage_path('app/public/' . $file->path);

        if (!file_exists($fullPath)) {
            return response()->json(['message' => 'File fisik tidak ditemukan di server'], 404);
        }

        return response()->download($fullPath, $file->name);
    }

    /**
     * Menghapus file.
     */
    public function destroy($id)
    {
        $file = File::findOrFail($id);

        // Hapus file fisik dari storage
        if (Storage::disk('public')->exists($file->path)) {
            Storage::disk('public')->delete($file->path);
        }

        // Hapus record dari database
        $file->delete();

        return response()->json([
            'message' => 'File berhasil dihapus'
        ], 200);
    }
}