<?php


use Illuminate\Support\Facades\Route;
use App\Modules\Blog\Http\Controllers\Blog\Dashboard\BlogController;
use App\Modules\Blog\Http\Controllers\Hashtag\Dashboard\HashtagController;
use App\Modules\Blog\Http\Controllers\Category\Dashboard\CategoryController;


Route::prefix('api/dashboard/v1/')->middleware('baseAuthMiddleware:employee')->group(function () {

    Route::controller(BlogController::class)->group(function () {
        Route::post('fetch_blogs', 'fetchBlogs');
        Route::post('fetch_blog_details', 'fetchBlogDetails');
        Route::post('create_blog', 'createBlog');
        Route::post('update_blog', 'updateBlog');
        Route::post('delete_blog', 'deleteBlog');
    });

    Route::controller(HashtagController::class)->group(function () {
        Route::post('fetch_hashtags', 'fetchHashtags');
        Route::post('fetch_hashtag_details', 'fetchHashtagDetails');
        Route::post('create_hashtag', 'createHashtag');
        Route::post('update_hashtag', 'updateHashtag');
        Route::post('delete_hashtag', 'deleteHashtag');
    });

    Route::controller(CategoryController::class)->group(function () {
        Route::post('fetch_categories', 'fetchCategories');
        Route::post('fetch_category_details', 'fetchCategoryDetails');
        Route::post('create_category', 'createCategory');
        Route::post('update_category', 'updateCategory');
        Route::post('delete_category', 'deleteCategory');
    });
});
