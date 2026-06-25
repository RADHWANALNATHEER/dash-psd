<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('designs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // القيم المُدخلة لكل طبقة (key => value) وقت إنتاج التصميم
            $table->json('values');

            $table->enum('format', ['png', 'jpg'])->default('png');
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->string('output_path')->nullable();

            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('failure_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('designs');
    }
};
