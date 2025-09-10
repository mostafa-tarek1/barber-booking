<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Base\Http\Controllers\Base\BaseController;
use App\Modules\Blog\Http\Controllers\Blog\Api\BlogController;
use App\Modules\Blog\Http\Controllers\Hashtag\Api\HashtagController;
use App\Modules\Blog\Http\Controllers\Category\Api\CategoryController;

Route::prefix('website')->group(function () {

    Route::controller(BlogController::class)->group(function () {
        Route::post('fetch_blogs', 'fetchBlogs');
        Route::post('fetch_latest_blogs', 'fetchLatestBlogs');
        Route::post('fetch_blog_details', 'fetchBlogDetails');
    });

    Route::controller(HashtagController::class)->group(function () {
        Route::post('fetch_hashtags', 'fetchHashtags');
        Route::post('fetch_latest_hashtags', 'fetchLatestHashtags');
    });

    Route::controller(CategoryController::class)->group(function () {
        Route::post('fetch_categories', 'fetchCategories');
        Route::post('fetch_latest_categories', 'fetchLatestCategories');
    });
});
