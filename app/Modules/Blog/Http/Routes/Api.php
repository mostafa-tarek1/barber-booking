<?php

use App\Modules\Blog\Http\Controllers\Api\Blog\BlogController;
use App\Modules\Blog\Http\Controllers\Api\Category\CategoryController;
use App\Modules\Blog\Http\Controllers\Api\Hashtag\HashtagController;
use Illuminate\Support\Facades\Route;


Route::prefix('api')->group(function () {
        // Blog routes
        Route::post('fetch_blogs', [BlogController::class, 'fetchBlogs']);
        Route::post('fetch_blog_details', [BlogController::class, 'fetchBlogDetails']);

        // Hashtag routes
        Route::post('fetch_hashtags', [HashtagController::class, 'fetchHashtags']);
        Route::post('fetch_hashtag_details', [HashtagController::class, 'fetchHashtagDetails']);

        // Category routes
        Route::post('fetch_categories', [CategoryController::class, 'fetchCategories']);
        Route::post('fetch_category_details', [CategoryController::class, 'fetchCategoryDetails']);
});
