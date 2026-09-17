<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Menampilkan daftar seluruh departemen.
     */
    public function index()
    {
        $departments = Department::withCount(['users', 'folders', 'files'])->get();

        return response()->json([
            'message' => 'Berhasil mengambil daftar departemen',
            'data' => $departments
        ], 200);
    }

    /**
     * Membuat departemen baru (Khusus Administrator).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
        ]);

        $department = Department::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => 'Departemen berhasil dibuat',
            'data' => $department
        ], 201);
    }

    /**
     * Menampilkan detail departemen.
     */
    public function show($id)
    {
        $department = Department::with(['users', 'folders', 'files'])->findOrFail($id);

        return response()->json([
            'message' => 'Detail departemen ditemukan',
            'data' => $department
        ], 200);
    }

    /**
     * Memperbarui departemen.
     */
    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
        ]);

        $department->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => 'Departemen berhasil diperbarui',
            'data' => $department
        ], 200);
    }

    /**
     * Menghapus departemen.
     */
    public function destroy($id)
    {
        $department = Department::findOrFail($id);
        $department->delete();

        return response()->json([
            'message' => 'Departemen berhasil dihapus'
        ], 200);
    }
}
