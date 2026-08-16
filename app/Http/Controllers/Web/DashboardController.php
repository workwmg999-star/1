<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Folder;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load('company.plan');
        $company = $user->company;

        $totalDocuments = Document::count();
        $totalFolders = Folder::count();
        $totalUsers = $company->users()->count();

        $recentDocuments = Document::with(['user', 'folder'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($doc) {
                return [
                    'id'             => $doc->id,
                    'title'          => $doc->title,
                    'file_type'      => $doc->file_type,
                    'size_formatted' => $doc->size_formatted,
                    'created_at'     => $doc->created_at->toISOString(),
                    'folder'         => $doc->folder ? ['name' => $doc->folder->name] : null,
                ];
            })
            ->toArray();

        $folders = Folder::withCount('documents')
            ->orderBy('name')
            ->get()
            ->toArray();

        $dash = [
            'stats' => [
                'total_documents' => $totalDocuments,
                'total_folders'   => $totalFolders,
                'total_users'     => $totalUsers,
            ],
            'storage' => [
                'used_bytes'    => $company->storage_used_bytes,
                'used_gb'       => $company->storage_used_gb,
                'limit_gb'      => $company->storage_limit_gb,
                'usage_percent' => $company->storage_usage_percent,
            ],
            'plan' => [
                'name'          => $company->plan->name ?? 'Free',
                'max_users'     => $company->plan->max_users === -1 ? 'unlimited' : ($company->plan->max_users ?? 2),
                'max_documents' => $company->plan->max_documents === -1 ? 'unlimited' : ($company->plan->max_documents ?? 500),
            ],
            'recent_documents' => $recentDocuments,
        ];

        return view('dashboard', [
            'dash'    => $dash,
            'folders' => $folders,
            'user'    => $user->toArray(),
        ]);
    }

    public function profile()
    {
        $user = Auth::user()->load('company.plan');
        $company = $user->company;

        return view('profile', [
            'user'    => $user->toArray(),
            'company' => [
                'name'       => $company->name,
                'email'      => $company->email,
                'phone'      => $company->phone,
                'address'    => $company->address,
                'country'    => $company->country,
                'created_at' => $company->created_at->toISOString(),
                'plan'       => ['name' => $company->plan->name ?? 'Free'],
                'storage'    => ['used_gb' => $company->storage_used_gb],
            ],
        ]);
    }

    public function subscriptions(Request $request)
    {
        $user = Auth::user()->load('company.plan');
        $company = $user->company;

        if ($request->isMethod('post') && $request->filled('_upgrade_plan')) {
            $planId = $request->input('_upgrade_plan');
            $newPlan = SubscriptionPlan::findOrFail($planId);
            $company->update(['plan_id' => $newPlan->id]);
            return back()->with('success', "Upgraded to {$newPlan->name} plan successfully!");
        }

        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->toArray();

        $current = [
            'plan' => [
                'id'   => $company->plan->id ?? 0,
                'name' => $company->plan->name ?? 'Free',
            ],
            'usage' => [
                'storage_used_gb'  => $company->storage_used_gb,
                'storage_limit_gb' => $company->storage_limit_gb,
                'storage_percent'  => $company->storage_usage_percent,
                'total_documents'  => Document::count(),
                'max_documents'    => $company->plan->max_documents === -1 ? 'Unlimited' : ($company->plan->max_documents ?? 500),
                'total_users'      => $company->users()->count(),
                'max_users'        => $company->plan->max_users === -1 ? 'Unlimited' : ($company->plan->max_users ?? 2),
            ],
        ];

        return view('subscriptions', [
            'plans'   => $plans,
            'current' => $current,
            'user'    => $user->toArray(),
        ]);
    }
}
