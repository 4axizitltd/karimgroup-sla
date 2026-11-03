<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_id'        => 'required|exists:clients,id',
            'application_id'   => ['required', 'exists:applications,id',
                Rule::unique('subscriptions')->where('client_id', $request->input('client_id'))],
            'quarterly_amount' => 'required|numeric|min:0',
            'note'             => 'nullable|string',
        ]);
        return response()->json(Subscription::create($data)->load('application'), 201);
    }

    public function update(Request $request, Subscription $subscription): JsonResponse
    {
        $data = $request->validate([
            'quarterly_amount' => 'sometimes|numeric|min:0',
            'is_active'        => 'sometimes|boolean',
            'note'             => 'nullable|string',
        ]);
        $subscription->update($data);
        return response()->json($subscription);
    }

    /** Client moved to a new server: allow the next activation on a different installation. */
    public function resetInstall(Subscription $subscription): JsonResponse
    {
        $subscription->forceFill(['install_id' => null])->save();
        $subscription->activationCodes()->whereNull('revoked_at')
            ->whereDate('valid_until', '>=', now()->toDateString())
            ->update(['install_id' => null]);
        return response()->json(['ok' => true]);
    }
}
