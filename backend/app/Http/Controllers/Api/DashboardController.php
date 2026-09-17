<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\File;
use App\Models\Folder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Menampilkan statistik ringkasan dan 10 file terbaru.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $totalFolders = Folder::count();
        $totalFiles = File::count();
        $totalDepartments = Department::count();

        // 10 file terbaru beserta relasinya
        $latestFiles = File::with(['user', 'department', 'folder'])
            ->latest()
            ->take(10)
            ->get();

        return response()->json([
            'message' => 'Data statistik dashboard berhasil dimuat',
            'data' => [
                'total_folders' => $totalFolders,
                'total_files' => $totalFiles,
                'total_departments' => $totalDepartments,
                'latest_files' => $latestFiles,
            ]
        ], 200);
    }
}