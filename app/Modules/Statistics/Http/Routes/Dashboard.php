<?php

use App\Modules\Statistics\Http\Controllers\Dashboard\AboutUs\AboutUsController;
use App\Modules\Statistics\Http\Controllers\Dashboard\AboutUs\AboutUsStatisticsController;
use App\Modules\Statistics\Http\Controllers\Dashboard\ContactUs\ContactUsController;
use App\Modules\Statistics\Http\Controllers\Dashboard\OurPartner\OurPartnerController;
use App\Modules\Statistics\Http\Controllers\Dashboard\Privacy\PrivacyController;
use App\Modules\Statistics\Http\Controllers\Dashboard\Term\TermController;
use Illuminate\Support\Facades\Route;
use App\Modules\Statistics\Http\Controllers\Dashboard\Faq\FaqController;
use App\Modules\Statistics\Http\Controllers\Dashboard\About\AboutController;
use App\Modules\Statistics\Http\Controllers\Dashboard\Setting\SettingController;
use App\Modules\Statistics\Http\Controllers\Dashboard\Slider\SliderController;
use App\Modules\Statistics\Http\Controllers\Dashboard\Statistics\StatisticsController;

// Route::prefix('dashboard')->middleware('baseAuthMiddleware:employee')->group(function () {
//     // Banner Routes
//     Route::controller(BannerController::class)->group(function () {
//         Route::post('fetch_banners', [BannerController::class, 'fetchBanners'])->name('fetch_banners');
//         Route::post('fetch_banner_details', [BannerController::class, 'fetchBannerDetails']);
//         Route::post('create_banner', [BannerController::class, 'createBanner']);
//         Route::post('update_banner', [BannerController::class, 'updateBanner']);
//         Route::post('delete_banner', [BannerController::class, 'deleteBanner']);
//     });
//     // Faq Routes
//     Route::controller(FaqController::class)->group(function () {
//         Route::post('fetch_faqs', [FaqController::class, 'fetchFaqs'])->name('fetch_faqs');
//         Route::post('fetch_faq_details', [FaqController::class, 'fetchFaqDetails']);
//         Route::post('create_faq', [FaqController::class, 'createFaq']);
//         Route::post('update_faq', [FaqController::class, 'updateFaq']);
//         Route::post('delete_faq', [FaqController::class, 'deleteFaq']);
//     });
// });


Route::prefix('dashboard')->group(function () {
    Route::middleware(['baseAuthMiddleware:employee', 'employeeRole:admin,super_admin'])->group(function () {
        //        Faq
        Route::post('fetch_faqs', [FaqController::class, 'fetchFaqs'])->name('fetch_faqs');
        Route::post('fetch_faq_details', [FaqController::class, 'fetchFaqDetails']);
        Route::post('create_faq', [FaqController::class, 'createFaq']);
        Route::post('update_faq', [FaqController::class, 'updateFaq']);
        Route::post('delete_faq', [FaqController::class, 'deleteFaq']);
        //        Settings
        Route::post('create-or-update-setting', [SettingController::class, 'createSettings']);
        Route::post('fetch-settings', [SettingController::class, 'fetchSettings']);
        // privacy routes
        Route::post('create-or-update_privacy', [PrivacyController::class, 'store_privacy']);
        Route::post('fetch_privacy', [PrivacyController::class, 'fetch_privacy']);
        // term routes
        Route::post('create-or-update_terms', [TermController::class, 'createTerm']);
        Route::post('fetch_terms', [TermController::class, 'fetchTerms']);
        // OurPartner
        Route::post('fetch_our_partners', [OurPartnerController::class, 'fetchOurPartners'])->name('fetch_our_partners');
        Route::post('fetch_our_partner_details', [OurPartnerController::class, 'fetchOurPartnerDetails']);
        Route::post('create_our_partner', [OurPartnerController::class, 'createOurPartner']);
        Route::post('update_our_partner', [OurPartnerController::class, 'updateOurPartner']);
        Route::post('delete_our_partner', [OurPartnerController::class, 'deleteOurPartner']);
        // About Us
        Route::post('create-or-update-about_us', [AboutUsController::class, 'createOrUpdate']);
        Route::post('fetch-about_us', [AboutUsController::class, 'fetchAboutUs']);
        Route::post('delete_about_us_images', [AboutUsController::class, 'deleteAboutUsImage']);
        // Slider
        Route::post('fetch_sliders', [SliderController::class, 'fetchSliders'])->name('fetch_sliders');
        Route::post('fetch_slider_details', [SliderController::class, 'fetchSliderDetails']);
        Route::post('create_slider', [SliderController::class, 'createSlider']);
        Route::post('update_slider', [SliderController::class, 'updateSlider']);
        Route::post('delete_slider', [SliderController::class, 'deleteSlider']);

        // Statistics
        Route::controller(StatisticsController::class)->group(function () {
            Route::post('fetch_dashboard_statistics', 'getDashboardStatistics')->name('fetch_dashboard_statistics');
            Route::post('fetch_statistics', 'getDashboardStatistics')->name('fetch_statistics');
            Route::post('fetch_daily_activity', 'getDailyActivity')->name('fetch_daily_activity');
            Route::post('fetch_monthly_activity', 'getMonthlyActivity')->name('fetch_monthly_activity');
            Route::post('fetch_top_services', 'getTopServices')->name('fetch_top_services');
            Route::post('fetch_top_barbers', 'getTopBarbers')->name('fetch_top_barbers');
        });
    });

    Route::controller(ContactUsController::class)
        ->middleware(['baseAuthMiddleware:employee', 'employeeRole:admin,super_admin'])
        ->group(function () {
            Route::post('fetch_contact_us', 'fetchContactUs');
            Route::post('fetch_contact_us_details', 'fetchContactUsDetails');
            Route::post('delete_contact_us', 'deleteContactUs');
        });
});
