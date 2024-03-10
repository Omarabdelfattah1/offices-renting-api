<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constraind('users');
            $table->string('title');
            $table->text('description');
            $table->decimal('lat',11,8);
            $table->decimal('lng',11,8);
            $table->text('address_line1');
            $table->text('address_line2')->nullable();
            $table->tinyInteger('approval_status')->default(1);
            $table->boolean('hidden')->default(false);
            $table->float('price_per_day');
            $table->float('monthly_discount')->default(0);
            $table->unsignedBigInteger('featured_image_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('office_tag',function (Blueprint $table) {
            $table->foreignId('tag_id')->constraind('tags');
            $table->foreignId('office_id')->constraind('offices');
            $table->unique(['tag_id','office_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_tag');
        Schema::dropIfExists('offices');
    }
};
