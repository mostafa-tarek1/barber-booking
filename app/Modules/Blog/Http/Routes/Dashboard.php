<?php

use App\Modules\Blog\Http\Controllers\Dashboard\Blog\BlogController;
use App\Modules\Blog\Http\Controllers\Dashboard\Category\CategoryController;
use App\Modules\Blog\Http\Controllers\Dashboard\Hashtag\HashtagController;
use Illuminate\Support\Facades\Route;


Route::prefix('dashboard')->group(function () {
    Route::middleware('baseAuthMiddleware:employee')->group(function () {

        Route::post('fetch_categories', [CategoryController::class, 'fetchCategories']);
        Route::post('fetch_category_details', [CategoryController::class, 'fetchCategoryDetails']);
        Route::post('create_category', [CategoryController::class, 'createCategory']);
        Route::post('update_category', [CategoryController::class, 'updateCategory']);
        Route::post('delete_category', [CategoryController::class, 'deleteCategory']);

        Route::post('fetch_hashtags', [HashtagController::class, 'fetchHashtags']);
        Route::post('fetch_hashtag_details', [HashtagController::class, 'fetchHashtagDetails']);
        Route::post('create_hashtag', [HashtagController::class, 'createHashtag']);
        Route::post('update_hashtag', [HashtagController::class, 'updateHashtag']);
        Route::post('delete_hashtag', [HashtagController::class, 'deleteHashtag']);

        Route::post('fetch_blogs', [BlogController::class, 'fetchBlogs']);
        Route::post('fetch_blog_details', [BlogController::class, 'fetchBlogDetails']);
        Route::post('create_blog', [BlogController::class, 'createBlog']);
        Route::post('update_blog', [BlogController::class, 'updateBlog']);
        Route::post('delete_blog', [BlogController::class, 'deleteBlog']);

    });
});
