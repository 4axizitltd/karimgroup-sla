<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivationLog;
use App\Models\SlaPayment;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $today = now()->toDateString();
        $warn = now()->addDays(config('sla.warn_days'))->toDateString();

        $rows = Subscription::with('client:id,code,name,phone', 'application:id,code,name')
            ->where('is_active', true)->get()
            ->map(function (Subscription $s) use ($today, $warn) {
                $until = $s->coveredUntil();
                $state = ! $until || $until < $today ? 'expired' : ($until <= $warn ? 'expiring' : 'active');
                $lastSeen = $s->activationCodes()->max('last_seen_at');
                return [
                    'id'               => $s->id,
                    'client'           => $s->client,
                    'application'      => $s->application,
                    'quarterly_amount' => $s->quarterly_amount,
                    'covered_until'    => $until,
                    'state'            => $state,
                    'install_id'       => $s->install_id,
                    'last_seen_at'     => $lastSeen,
                ];
            })->sortBy('covered_until')->values();

        return response()->json([
            'counts' => [
                'active'   => $rows->where('state', 'active')->count(),
                'expiring' => $rows->where('state', 'expiring')->count(),
                'expired'  => $rows->where('state', 'expired')->count(),
            ],
            'collected_this_year' => (float) SlaPayment::whereYear('paid_at', now()->year)->sum('amount'),
            'subscriptions'       => $rows,
            'recent_logs'         => ActivationLog::latest('created_at')->limit(20)->get(),
        ]);
    }
}
