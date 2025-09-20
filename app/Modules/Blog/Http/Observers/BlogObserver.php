<?php

namespace App\Modules\Blog\Http\Observers;

use App\Modules\Blog\Infrastructure\Persistence\Models\Blog\Blog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BlogObserver
{

    /**
     * Brand Observer constructor
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the Vendor "created" event.
     *
     * @param Blog $blog
     * @return void
     */
    public function created(Blog $blog)
    {

        $name = $blog->translate('en')->title;

        if(isset($blog->parent_id) && !empty($blog->parent_id)) {
            $main_title = $blog->parent->translate('en')->title;
            $name = $main_title.' '.$name;
        }
        if (!isset($name) || empty($name)) {
            $name = 'blog';
        }
        if(isset($blog->parent_id) && !empty($blog->parent_id)) {
            $main_title = $blog->parent->translate('en')->title;
            $name = $main_title.' '.$name;
        }

        $slug = makeSlugFromTitle($name, Blog::class);

        if (DB::table('blogs')->where('slug', $slug)->exists()) {
            $slug = $slug . '-' . time();
        }

        $blog->update(['slug' => $slug]);

        $blog->refresh();
        // $Brand->searchable();
    }

    /**
     * Handle the Vendor "updated" event.
     *
     * @param Blog $blog
     * @return void
     */
    public function updated(Blog $blog)
    {
        //
    }

    /**
     * Handle the Vendor "deleted" event.
     *
     * @param Blog $blog
     * @return void
     */
    public function deleted(Blog $blog)
    {
        //
    }

    /**
     * Handle the Vendor "restored" event.
     *
     * @param Blog $blog
     * @return void
     */
    public function restored(Blog $blog)
    {
        //
    }

    /**
     * Handle the Vendor "force deleted" event.
     *
     * @param Blog $blog
     * @return void
     */
    public function forceDeleted(Blog $blog)
    {
        //
    }
}
