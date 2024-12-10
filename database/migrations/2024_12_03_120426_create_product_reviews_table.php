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
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id()->comment('기본키');
            $table->foreignId('product_id')
                //->constrained()->onDelete('cascade')
                ->comment('참조 상품 ID');
            $table->foreignId('user_id')
                //->constrained()->onDelete('cascade')
                ->comment('후기 작성 사용자 ID');
            $table->unsignedTinyInteger('rating')->comment('평점 (1~5)');
            $table->text('review')->nullable()->comment('후기 내용'); // 후기 텍스트
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};