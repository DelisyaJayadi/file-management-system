<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            
            // Self-referencing untuk folder bersarang (parent-child)
            $table->foreignId('parent_id')->nullable()->constrained('folders')->onDelete('cascade');
            
            // Relasi ke Department & User
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('folders');
    }
};
