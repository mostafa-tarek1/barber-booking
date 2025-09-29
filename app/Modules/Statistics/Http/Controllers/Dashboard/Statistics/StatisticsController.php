<?php

namespace App\Modules\Statistics\Http\Controllers\Dashboard\Statistics;

use App\Http\Controllers\Controller;
use App\Modules\Statistics\Application\UseCases\Statistics\StatisticsUseCase;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function __construct(
        private StatisticsUseCase $statisticsUseCase
    ) {
    }

    /**
     * جلب الإحصائيات الرئيسية للداشبورد
     */
    public function getDashboardStatistics(Request $request)
    {
        return $this->statisticsUseCase->getDashboardStatistics()->response();
    }

    /**
     * جلب النشاط اليومي
     */
    public function getDailyActivity(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        return $this->statisticsUseCase->getDailyActivity($date)->response();
    }

    /**
     * جلب النشاط الشهري
     */
    public function getMonthlyActivity(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        return $this->statisticsUseCase->getMonthlyActivity($month)->response();
    }

    /**
     * جلب أفضل الخدمات
     */
    public function getTopServices(Request $request)
    {
        $limit = $request->get('limit', 5);
        return $this->statisticsUseCase->getTopServices($limit)->response();
    }

    /**
     * جلب أفضل الحلاقين
     */
    public function getTopBarbers(Request $request)
    {
        $limit = $request->get('limit', 5);
        return $this->statisticsUseCase->getTopBarbers($limit)->response();
    }
}
