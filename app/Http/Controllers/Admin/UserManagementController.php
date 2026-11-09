<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->hasPermission('users.manage'), 403);

        $users = User::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = trim($request->string('q')->toString());
                $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%"));
            })
            ->with('roles')
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function updateStatus(Request $request, User $user)
    {
        abort_unless($request->user()?->hasPermission('users.manage'), 403);
        abort_if($request->user()->is($user), 422, 'وضعیت حساب خودتان از این بخش قابل تغییر نیست.');

        $data = $request->validate([
            'status' => ['required', Rule::in(['active', 'suspended', 'pending'])],
        ]);

        $before = $user->status;
        DB::transaction(function () use ($user, $data, $request, $before) {
            if ($data['status'] !== 'active') {
                $superAdminRole = Role::where('key', 'super-admin')->lockForUpdate()->first();
                if ($superAdminRole && $user->roles()->whereKey($superAdminRole->id)->exists()) {
                    $activeSuperAdmins = User::where('status', 'active')
                        ->whereHas('roles', fn ($query) => $query->where('roles.id', $superAdminRole->id))
                        ->lockForUpdate()
                        ->count();

                    if ($activeSuperAdmins <= 1) {
                        abort(422, 'امکان تعلیق آخرین مدیر کل فعال وجود ندارد.');
                    }
                }
            }

            $user->update(['status' => $data['status']]);
            \App\Models\AuditLog::create([
                'actor_user_id' => $request->user()->id,
                'action' => 'user.status.updated',
                'subject_type' => User::class,
                'subject_id' => (string) $user->id,
                'before_data' => ['status' => $before],
                'after_data' => ['status' => $data['status']],
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 2000),
                'created_at' => now(),
            ]);
        });

        return back()->with('status', 'وضعیت حساب کاربر تغییر کرد.');
    }
}
