<?php

namespace App\Http\Controllers;

use App\Models\InsurancePolicy;
use App\Models\PolicyEndorsement;
use App\Models\Entity;
use App\Models\Employee;
use App\Models\Student;
use App\Models\Vessel;
use App\Models\Vehicle;
use App\Models\Company;
use App\Models\Course;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportsController extends Controller
{
    /**
     * Display the reports page
     */
    public function index()
    {
        $stats = $this->getStatistics();
        return view('reports.index', compact('stats'));
    }

    /**
     * Get live statistics for the dashboard
     */
    public function getStatistics()
    {
        // Policy Statistics
        $totalPolicies = InsurancePolicy::count();
        $activePolicies = InsurancePolicy::where('status', 'ACTIVE')->count();
        $expiredPolicies = InsurancePolicy::where('status', 'EXPIRED')->count();
        
        // Premium Statistics
        $totalPremium = InsurancePolicy::sum('premium_amount');
        $averagePremium = InsurancePolicy::avg('premium_amount') ?: 0;
        $monthlyRevenue = InsurancePolicy::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('premium_amount');

        // Entity Statistics
        $totalEntities = Entity::count();
        $totalEmployees = Employee::count();
        $totalStudents = Student::count();
        $totalVessels = Vessel::count();
        $totalVehicles = Vehicle::count();

        // Endorsement Statistics
        $totalEndorsements = PolicyEndorsement::count();
        $recentEndorsements = PolicyEndorsement::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // Company Statistics
        $totalCompanies = Company::count();
        $activeCompanies = Company::where('status', 'ACTIVE')->count();

        // Premium by Insurance Type
        $premiumByType = InsurancePolicy::select('insurance_type')
            ->selectRaw('SUM(premium_amount) as total_premium')
            ->groupBy('insurance_type')
            ->get();

        // Policy Status Distribution
        $policyStatusDistribution = InsurancePolicy::select('status')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('status')
            ->get();

        // Monthly Premium Trends (Last 6 months)
        $monthlyTrends = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $premium = InsurancePolicy::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->sum('premium_amount');
            
            $monthlyTrends[] = [
                'month' => $month->format('M Y'),
                'premium' => $premium
            ];
        }

        // Entity Type Distribution
        $entityDistribution = [
            'employees' => $totalEmployees,
            'students' => $totalStudents,
            'vessels' => $totalVessels,
            'vehicles' => $totalVehicles,
        ];

        return [
            'policies' => [
                'total' => $totalPolicies,
                'active' => $activePolicies,
                'expired' => $expiredPolicies,
            ],
            'premium' => [
                'total' => $totalPremium,
                'average' => $averagePremium,
                'monthly' => $monthlyRevenue,
            ],
            'entities' => [
                'total' => $totalEntities,
                'employees' => $totalEmployees,
                'students' => $totalStudents,
                'vessels' => $totalVessels,
                'vehicles' => $totalVehicles,
            ],
            'endorsements' => [
                'total' => $totalEndorsements,
                'recent' => $recentEndorsements,
            ],
            'companies' => [
                'total' => $totalCompanies,
                'active' => $activeCompanies,
            ],
            'charts' => [
                'premiumByType' => $premiumByType,
                'policyStatusDistribution' => $policyStatusDistribution,
                'monthlyTrends' => $monthlyTrends,
                'entityDistribution' => $entityDistribution,
            ]
        ];
    }

    /**
     * Export reports to various formats
     */
    public function export(Request $request, $format)
    {
        $stats = $this->getStatistics();
        
        switch ($format) {
            case 'pdf':
                return $this->exportToPdf($stats);
            case 'excel':
                return $this->exportToExcel($stats);
            case 'csv':
                return $this->exportToCsv($stats);
            default:
                return response()->json(['error' => 'Invalid export format'], 400);
        }
    }

    private function exportToPdf($stats)
    {
        // For now, return a simple JSON response
        // In a real implementation, you would use a PDF library like DomPDF
        return response()->json([
            'message' => 'PDF export functionality would be implemented here',
            'stats' => $stats
        ]);
    }

    private function exportToExcel($stats)
    {
        // For now, return a simple JSON response
        // In a real implementation, you would use a library like PhpSpreadsheet
        return response()->json([
            'message' => 'Excel export functionality would be implemented here',
            'stats' => $stats
        ]);
    }

    private function exportToCsv($stats)
    {
        // For now, return a simple JSON response
        // In a real implementation, you would generate CSV content
        return response()->json([
            'message' => 'CSV export functionality would be implemented here',
            'stats' => $stats
        ]);
    }

    /**
     * Get specific report data for charts
     */
    public function getChartData($type)
    {
        $stats = $this->getStatistics();

        switch ($type) {
            case 'policy-status':
                return response()->json($stats['charts']['policyStatusDistribution']);
            
            case 'premium-by-type':
                return response()->json($stats['charts']['premiumByType']);
            
            case 'monthly-trends':
                return response()->json($stats['charts']['monthlyTrends']);
            
            case 'entity-distribution':
                return response()->json($stats['charts']['entityDistribution']);
            
            default:
                return response()->json(['error' => 'Invalid chart type'], 400);
        }
    }
}