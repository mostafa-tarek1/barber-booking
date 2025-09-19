<?php

namespace App\Modules\Blog\Http\Observers;

use App\Modules\Blog\Infrastructure\Persistence\Models\Hashtag\Hashtag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HashtagObserver
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
     * @param Hashtag $hashtag
     * @return void
     */
    public function created(Hashtag $hashtag)
    {

        $name = $hashtag->translate('en')->title;

        if(isset($hashtag->parent_id) && !empty($hashtag->parent_id)) {
            $main_title = $hashtag->parent->translate('en')->title;
            $name = $main_title.' '.$name;
        }
        if (!isset($name) || empty($name)) {
            $name = 'hashtag';
        }
        if(isset($hashtag->parent_id) && !empty($hashtag->parent_id)) {
            $main_title = $hashtag->parent->translate('en')->title;
            $name = $main_title.' '.$name;
        }

        $slug = makeSlugFromTitle($name, Hashtag::class);

        if (DB::table('hashtags')->where('slug', $slug)->exists()) {
            $slug = $slug . '-' . time();
        }

        $hashtag->update(['slug' => $slug]);

        $hashtag->refresh();
        // $Brand->searchable();
    }

    /**
     * Handle the Vendor "updated" event.
     *
     * @param Hashtag $hashtag
     * @return void
     */
    public function updated(Hashtag $hashtag)
    {
        //
    }

    /**
     * Handle the Vendor "deleted" event.
     *
     * @param Hashtag $hashtag
     * @return void
     */
    public function deleted(Hashtag $hashtag)
    {
        //
    }

    /**
     * Handle the Vendor "restored" event.
     *
     * @param Hashtag $hashtag
     * @return void
     */
    public function restored(Hashtag $hashtag)
    {
        //
    }

    /**
     * Handle the Vendor "force deleted" event.
     *
     * @param Hashtag $hashtag
     * @return void
     */
    public function forceDeleted(Hashtag $hashtag)
    {
        //
    }
}
