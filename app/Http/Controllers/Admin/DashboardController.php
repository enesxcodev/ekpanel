<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Dashboard\LoadDashboardWidgetsRequest;
use App\Http\Requests\Admin\Dashboard\UpdateDashboardPreferenceRequest;
use App\Models\Forms\FormSubmission;
use App\Services\Admin\Dashboard\DashboardService;
use App\Services\Analytics\VisitAnalytics;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function widgetData(LoadDashboardWidgetsRequest $request, DashboardService $dashboard): JsonResponse
    {
        return response()->json(['widgets' => $dashboard->data($request->user('admin'), $request->validated('widget_ids'))])
            ->header('Cache-Control', 'private, no-store');
    }

    public function updatePreferences(UpdateDashboardPreferenceRequest $request, DashboardService $dashboard): JsonResponse
    {
        return response()->json($dashboard->save(
            $request->user('admin'),
            $request->validated('widget_order'),
            (int) $request->validated('revision'),
        ))->header('Cache-Control', 'private, no-store');
    }

    public function index(Request $request, VisitAnalytics $analytics): View
    {
        return $this->dashboard($request, $analytics, false);
    }

    public function pages(Request $request, VisitAnalytics $analytics): View
    {
        return $this->dashboard($request, $analytics, true);
    }

    private function dashboard(Request $request, VisitAnalytics $analytics, bool $fullPageList): View
    {
        $currentYear = (int) now()->format('Y');
        $filters = $request->validate([
            'year' => ['nullable', 'integer', 'min:'.($currentYear - 1), 'max:'.$currentYear],
            'device' => ['nullable', 'in:all,computer,phone,other'],
        ]);
        $year = (int) ($filters['year'] ?? $currentYear);
        $device = $filters['device'] ?? 'all';
        $data = $analytics->dashboardData($year, $device);
        $now = CarbonImmutable::now();
        $thisMonthStart = $now->startOfMonth();
        $lastMonthStart = $thisMonthStart->subMonth();
        $visitsThisMonth = $analytics->siteTotal($thisMonthStart, $now);
        $visitsLastMonth = $analytics->siteTotal($lastMonthStart, $thisMonthStart->subSecond());
        $canViewSubmissions = $request->user('admin')->can('forms.submissions.view');
        $submissionsThisMonth = $canViewSubmissions ? FormSubmission::query()
            ->where('created_at', '>=', $thisMonthStart)
            ->where('created_at', '<=', $now)
            ->count() : 0;
        $submissionsLastMonth = $canViewSubmissions ? FormSubmission::query()
            ->where('created_at', '>=', $lastMonthStart)
            ->where('created_at', '<', $thisMonthStart)
            ->count() : 0;

        return view('admin.dashboard', [
            'widgetDashboard' => $fullPageList ? null : app(DashboardService::class)->state($request->user('admin')),
            'canViewSubmissions' => $canViewSubmissions,
            'currentYear' => $currentYear,
            'year' => $year,
            'device' => $device,
            'fullPageList' => $fullPageList,
            'data' => $data,
            'visitsThisMonth' => $visitsThisMonth,
            'visitsLastMonth' => $visitsLastMonth,
            'submissionsThisMonth' => $submissionsThisMonth,
            'submissionsLastMonth' => $submissionsLastMonth,
        ]);
    }
}
