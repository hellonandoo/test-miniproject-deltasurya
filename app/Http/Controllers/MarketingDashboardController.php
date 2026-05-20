<?php

namespace App\Http\Controllers;

use App\Services\DeltaSuryaApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarketingDashboardController extends Controller
{
    protected DeltaSuryaApiService $apiService;

    public function __construct(DeltaSuryaApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Dashboard data for marketing role.
     */
    public function index()
    {
        // 1. Fetch master data from API to map insurance_id to its actual name
        $apiResponse = $this->apiService->getInsurances();
        
        // Handle common API response structures (direct array or nested in 'data' key)
        $insurancesList = $apiResponse['data'] ?? $apiResponse;
        
        $insuranceMap = [];
        if (is_array($insurancesList)) {
            foreach ($insurancesList as $insurance) {
                if (isset($insurance['id']) && isset($insurance['name'])) {
                    $insuranceMap[$insurance['id']] = $insurance['name'];
                }
            }
        }

        // 2. Top Visited Insurances (By Transaction Count)
        $topVisitedQuery = DB::table('transactions')
            ->whereNotNull('insurance_id')
            ->where('insurance_id', '!=', '')
            ->select('insurance_id', DB::raw('COUNT(*) as total_transactions'))
            ->groupBy('insurance_id')
            ->orderByDesc('total_transactions')
            ->limit(5)
            ->get();

        $topVisitedInsurances = $topVisitedQuery->map(function ($item) use ($insuranceMap) {
            return [
                'insurance_id'       => $item->insurance_id,
                'insurance_name'     => $insuranceMap[$item->insurance_id] ?? 'Unknown Insurance',
                'total_transactions' => $item->total_transactions,
            ];
        });

        // 3. Top Paid Insurances (By Total Revenue / Grand Total)
        $topPaidQuery = DB::table('transactions')
            ->where('status', 'paid')
            ->whereNotNull('insurance_id')
            ->where('insurance_id', '!=', '')
            ->select('insurance_id', DB::raw('SUM(grand_total) as total_amount'))
            ->groupBy('insurance_id')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

        $topPaidInsurances = $topPaidQuery->map(function ($item) use ($insuranceMap) {
            return [
                'insurance_id'   => $item->insurance_id,
                'insurance_name' => $insuranceMap[$item->insurance_id] ?? 'Unknown Insurance',
                'total_amount'   => (float) $item->total_amount,
            ];
        });

        // 4. Total overall revenue from all paid transactions
        $totalRevenue = DB::table('transactions')
            ->where('status', 'paid')
            ->sum('grand_total');

        return view('marketing.dashboard', [
            'total_revenue'          => (float) $totalRevenue,
            'top_visited_insurances' => $topVisitedInsurances,
            'top_paid_insurances'    => $topPaidInsurances,
        ]);
    }
}
