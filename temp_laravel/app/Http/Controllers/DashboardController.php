<?php

namespace App\Http\Controllers;

use App\Models\InsurancePolicy;
use App\Models\PolicyEndorsement;
use App\Models\Entity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalPolicies = InsurancePolicy::count();
        $activePolicies = InsurancePolicy::where('status', 'ACTIVE')->count();
        $expiredPolicies = InsurancePolicy::where('status', 'EXPIRED')->count();
        $totalPremium = InsurancePolicy::where('status', 'ACTIVE')->sum('premium_amount');
        $totalEntities = Entity::count();

        $thirtyDaysAgo = now()->subDays(30);
        $recentEndorsements = PolicyEndorsement::where('created_at', '>=', $thirtyDaysAgo)->count();

        $stats = [
            'total_policies' => $totalPolicies,
            'active_policies' => $activePolicies,
            'expired_policies' => $expiredPolicies,
            'total_premium' => (float) $totalPremium,
            'total_entities' => $totalEntities,
            'recent_endorsements' => $recentEndorsements,
        ];

        // Get recent policies with entity information
        $recentPolicies = InsurancePolicy::with('entities')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', compact('stats', 'recentPolicies'));
    }

    public function stats()
    {
        $totalPolicies = InsurancePolicy::count();
        $activePolicies = InsurancePolicy::where('status', 'ACTIVE')->count();
        $expiredPolicies = InsurancePolicy::where('status', 'EXPIRED')->count();
        $totalPremium = InsurancePolicy::where('status', 'ACTIVE')->sum('premium_amount');
        $totalEntities = Entity::count();

        $thirtyDaysAgo = now()->subDays(30);
        $recentEndorsements = PolicyEndorsement::where('created_at', '>=', $thirtyDaysAgo)->count();

        return response()->json([
            'total_policies' => $totalPolicies,
            'active_policies' => $activePolicies,
            'expired_policies' => $expiredPolicies,
            'total_premium' => (float) $totalPremium,
            'total_entities' => $totalEntities,
            'recent_endorsements' => $recentEndorsements,
        ]);
    }
}
