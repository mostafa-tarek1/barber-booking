<?php

use App\Modules\Statistics\Http\Controllers\Dashboard\ContactUs\ContactUsController;
use App\Modules\Statistics\Http\Controllers\Website\Slider\SliderController;
use App\Modules\Statistics\Http\Controllers\Website\OurPartner\OurPartnerController;
use App\Modules\Statistics\Http\Controllers\Website\About\AboutController;
use App\Modules\Statistics\Http\Controllers\Website\AboutUs\AboutUsController;
use Illuminate\Support\Facades\Route;

use App\Modules\Statistics\Http\Controllers\Website\Term\TermController;
use App\Modules\Statistics\Http\Controllers\Website\Faq\FaqController;
use App\Modules\Statistics\Http\Controllers\Website\Privacy\PrivacyController;

/**
 *   Website Routes
 */
Route::prefix('api')->group(function () {

    Route::middleware('baseAuthMiddleware:user')->group(function () {

        // banners routes
    });
    // terms routes
    Route::controller(TermController::class)->group(function () {
        Route::post('fetch_terms_and_conditions', 'fetchTerms');
    });
    // terms routes
    Route::controller(PrivacyController::class)->group(function () {
        Route::post('fetch_privacy', 'fetch_privacy');
    });

    Route::controller(AboutController::class)->group(function () {
        Route::post('fetch_about_app', 'fetchAbouts');
        Route::post('fetch_contact_info', 'fetchContactInfos');
    });

    Route::controller(FaqController::class)->group(function () {
        Route::post('fetch_fq', 'fetchFaqs');
        Route::post('fetch_faq_details', 'fetchFaqDetails');
    });

    Route::controller(OurPartnerController::class)->group(function () {
        Route::post('fetch_our_partners', 'fetchOurPartners');
        Route::post('fetch_our_partner_details', 'fetchOurPartnerDetails');
    });

    Route::controller(AboutUsController::class)->group(function () {
        Route::post('fetch_about_us', 'fetchAboutUs');
    });

    Route::controller(SliderController::class)->group(function () {
        Route::post('fetch_sliders', 'fetchSliders');
    });


    Route::controller(ContactUsController::class)->group(function () {
        Route::post('create_contact_us', 'createContactUs');
    });
});
