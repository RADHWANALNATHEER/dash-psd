<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // مهمة دفعية (Batch) تُنتج عدة تصاميم من قالب واحد بمقاسات/متغيرات مختلفة (مرحلة 2)
        Schema::create('design_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // قائمة المتغيرات المطلوبة: [{ values: {...}, sizes: [{width,height},...] }, ...]
            $table->json('payload');

            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'partially_failed'])->default('pending');
            $table->unsignedInteger('total_count')->default(0);
            $table->unsignedInteger('completed_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);

            $table->timestamps();
        });

        Schema::table('designs', function (Blueprint $table) {
            $table->foreignId('design_job_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('designs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('design_job_id');
        });

        Schema::dropIfExists('design_jobs');
    }
};
