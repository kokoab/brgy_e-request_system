<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "Room 1", "Master Bedroom", "Single Room A"
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('capacity')->default(1); // How many people can stay
            $table->boolean('is_available')->default(true);
            $table->integer('order')->default(0); // For ordering rooms
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};

