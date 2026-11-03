<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivationCode;
use App\Models\SlaPayment;
use App\Models\Subscription;
use App\Services\LicenseSigner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = SlaPayment::with('subscription.client:id,code,name', 'subscription.application:id,code,name', 'activationCode')
            ->latest('paid_at');
        if ($request->filled('subscription_id')) {
            $q->where('subscription_id', $request->integer('subscription_id'));
        }
        return response()->json($q->paginate(50));
    }

    /** Suggested next period for a subscription (starts the day after current coverage). */
    public function suggest(Subscription $subscription): JsonResponse
    {
        $until = $subscription->coveredUntil();
        $from = $until && $until >= now()->toDateString()
            ? Carbon::parse($until)->addDay()
            : now()->startOfMonth();
        $to = $from->copy()->addMonthsNoOverflow(config('sla.period_months'))->subDay();

        return response()->json([
            'period_from' => $from->toDateString(),
            'period_to'   => $to->toDateString(),
            'amount'      => $subscription->quarterly_amount,
        ]);
    }

    /** Save payment and generate the activation code in one step. */
    public function store(Request $request, LicenseSigner $signer): JsonResponse
    {
        $data = $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
            'period_from'     => 'required|date',
            'period_to'       => 'required|date|after_or_equal:period_from',
            'amount'          => 'nullable|numeric|min:0',
            'paid_at'         => 'required|date',
            'method'          => 'nullable|string|max:50',
            'reference'       => 'nullable|string|max:255',
            'note'            => 'nullable|string',
        ]);
        $data['amount'] = $data['amount'] ?? 0;   // amount is optional
        $userId = $request->user()->id;

        [$payment, $code] = DB::transaction(function () use ($data, $userId) {
            $sub = Subscription::with('client')->findOrFail($data['subscription_id']);
            $payment = SlaPayment::create($data + ['created_by' => $userId]);
            $code = ActivationCode::create([
                'subscription_id' => $sub->id,
                'sla_payment_id'  => $payment->id,
                'code'            => ActivationCode::generateCode($sub->client->code),
                'license_id'      => ActivationCode::generateLicenseId(),
                'valid_from'      => $data['period_from'],
                'valid_until'     => $data['period_to'],
                'created_by'      => $userId,
            ]);
            return [$payment, $code];
        });

        return response()->json([
            'payment'     => $payment,
            'code'        => $code,
            // Works without internet: client can paste this instead of the short code
            'offline_key' => $signer->sign($code, '*'),
        ], 201);
    }
}
