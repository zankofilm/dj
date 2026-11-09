<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = request()->user();
        abort_unless($user && (
            $user->hasPermission('admin.dashboard.view') ||
            $user->hasPermission('admin.access') ||
            $user->hasPermission('sellers.review')
        ), 403);

        $stats = [
            'users' => User::count(),
            'sellers' => Seller::count(),
            'pending_sellers' => Seller::whereIn('status', ['pending', 'submitted', 'under_review'])->count(),
            'products' => Product::count(),
            'pending_products' => Product::whereIn('status', ['pending_review', 'submitted'])->count(),
            'orders' => Order::count(),
            'recent_revenue_minor' => Order::where('status', 'paid')
                ->where('created_at', '>=', now()->subDays(30))
                ->sum('total_minor'),
        ];

        $recentOrders = Order::query()->latest('created_at')->limit(8)->get();
        $recentSellers = Seller::query()->latest('created_at')->limit(6)->get();
        $recentProducts = Product::query()->with('seller')->latest('created_at')->limit(6)->get();
        $recentAudit = class_exists(AuditLog::class)
            ? AuditLog::with('actor')->latest('created_at')->limit(8)->get()
            : collect();

        return view('admin.dashboard', compact(
            'stats', 'recentOrders', 'recentSellers', 'recentProducts', 'recentAudit'
        ));
    }
}
