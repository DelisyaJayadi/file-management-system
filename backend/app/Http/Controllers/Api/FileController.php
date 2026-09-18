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
            'file' => 'required|file|max:10240', // sesuaikan ukuran maksimal
            'department_id' => 'required|exists:departments,id',
            'folder_id' => 'nullable|exists:folders,id',
            'original_name' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            $path = $file->store('files', 'public');

            $originalName = $file->getClientOriginalName();
            $fileName = basename($path);

            // Simpan ke database dengan menyertakan original_name
            $fileItem = File::create([
                'title' => $request->title,
                'name' => $fileName,
                'original_name' => $originalName,
                'path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'department_id' => $request->department_id,
                'folder_id' => $request->folder_id,
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'message' => 'File berhasil diunggah',
                'data' => $fileItem
            ], 201);
        }

        return response()->json(['message' => 'File tidak ditemukan'], 400);
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
            'original_name' => 'nullable|string|max:255',
        ]);

        $file->update([
            'title' => $request->title,
            'department_id' => $request->department_id ?? $file->department_id,
            'original_name' => $request->original_name ?? $file->original_name,
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

    /**
     * Menampilkan daftar semua file (atau difilter).
     */
    public function index(Request $request)
    {
        $query = File::with(['user', 'department', 'folder']);

        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->has('folder_id')) {
            $query->where('folder_id', $request->folder_id);
        }

        $files = $query->get();

        return response()->json([
            'message' => 'Berhasil mengambil daftar file',
            'data' => $files
        ], 200);
    }
}
