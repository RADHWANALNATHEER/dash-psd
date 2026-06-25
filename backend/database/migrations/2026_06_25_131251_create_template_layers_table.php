<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_layers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained()->cascadeOnDelete();

            // مفتاح الطبقة المستخدم كاسم الحقل في النموذج الديناميكي (title, subtitle, photo...)
            $table->string('key');
            $table->string('label');
            $table->enum('type', ['text', 'image'])->default('text');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_required')->default(false);

            // الموضع والأبعاد المشتركة بين النص والصورة
            $table->integer('x')->default(0);
            $table->integer('y')->default(0);
            $table->unsignedInteger('width')->default(100);
            $table->unsignedInteger('height')->nullable();

            // خصائص الطبقة النصية
            $table->string('font_family')->nullable();
            $table->unsignedInteger('font_size')->nullable();
            $table->unsignedInteger('font_weight')->nullable();
            $table->string('color')->nullable();
            $table->enum('align', ['right', 'left', 'center'])->default('right');
            $table->enum('direction', ['rtl', 'ltr'])->default('rtl');
            $table->decimal('line_height', 4, 2)->nullable();
            $table->decimal('letter_spacing', 4, 2)->nullable();
            $table->string('text_shadow')->nullable();
            $table->string('placeholder')->nullable();

            // خصائص الطبقة الصورية
            $table->unsignedInteger('border_radius')->nullable();
            $table->enum('object_fit', ['cover', 'contain', 'fill'])->nullable();
            $table->string('placeholder_url')->nullable();

            $table->timestamps();

            $table->unique(['template_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_layers');
    }
};
