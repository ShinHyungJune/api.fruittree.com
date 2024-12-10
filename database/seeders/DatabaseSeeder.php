<?php

namespace Database\Seeders;

use App\Models\MdProductPackage;
use App\Models\Post\Board;
use App\Models\Post\BoardCategory;
use App\Models\Post\Post;
use App\Models\Product;
use App\Models\ProductInquiry;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        /*DB::statement("SET foreign_key_checks=0");
        User::truncate();
        Product::truncate();
        ProductReview::truncate();
        BoardCategory::truncate();
        Board::truncate();
        Post::truncate();
        MdProductPackage::truncate();
        ProductInquiry::truncate();
        DB::table("media")->truncate();
        DB::statement("SET foreign_key_checks=1");*/

        $this->call([
            UserSeeder::class,
            ProductSeeder::class,
            ProductReviewSeeder::class,
            BoardSeeder::class,
            BoardCategorySeeder::class,
            PostSeeder::class,
            MdProductPackageSeeder::class,
            ProductInquirySeeder::class,
        ]);
    }
}