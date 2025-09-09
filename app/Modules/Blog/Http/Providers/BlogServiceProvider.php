<?php

namespace App\Modules\Blog\Http\Providers;

use App\Modules\Blog\Http\Observers\BlogObserver;
use App\Modules\Blog\Http\Observers\CategoryObserver;
use App\Modules\Blog\Http\Observers\HashtagObserver;
use App\Modules\Blog\Infrastructure\Persistence\Models\Blog\Blog;
use App\Modules\Blog\Infrastructure\Persistence\Models\Category\Category;
use App\Modules\Blog\Infrastructure\Persistence\Models\Hashtag\Hashtag;

use Illuminate\Support\ServiceProvider;

class BlogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
        $this->loadMigrationsFrom(app_path('Modules/Blog/Infrastructure/DataBase/Migrations'));

        $this->loadRoutesFrom(app_path('Modules/Blog/Http/Routes/Api.php'));
        $this->loadRoutesFrom(app_path('Modules/Blog/Http/Routes/Dashboard.php'));
        foreach (glob(app_path('Modules/Blog/Application/Helpers') . '/*.php') as $filename) {
            require_once $filename;
        }

        Hashtag::observe(HashtagObserver::class);
        Category::observe(CategoryObserver::class);
        Blog::observe(BlogObserver::class);
    }
}
