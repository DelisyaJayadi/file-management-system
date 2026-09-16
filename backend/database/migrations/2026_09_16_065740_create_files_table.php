<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('name');             // Nama file unik di storage
            $table->string('original_name');    // Nama asli file
            $table->string('path');             // Lokasi penyimpanan file
            $table->bigInteger('size');         // Ukuran file (bytes)
            $table->string('mime_type');        // Tipe file
            
            // Relasi ke Folder & User
            $table->foreignId('folder_id')->constrained('folders')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('files');
    }
};
