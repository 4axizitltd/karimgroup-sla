<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivationCode;
use App\Models\ActivationLog;
use App\Services\LicenseSigner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivationCodeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = ActivationCode::with('subscription.client:id,code,name', 'subscription.application:id,code,name')->latest();
        if ($request->filled('subscription_id')) {
            $q->where('subscription_id', $request->integer('subscription_id'));
        }
        return response()->json($q->paginate(50));
    }

    public function revoke(Request $request, ActivationCode $activationCode): JsonResponse
    {
        $data = $request->validate(['reason' => 'nullable|string|max:255']);
        $activationCode->forceFill(['revoked_at' => now(), 'revoke_reason' => $data['reason'] ?? null])->save();
        return response()->json($activationCode);
    }

    /** Offline key for a code (when the client server has no internet). */
    public function offlineKey(ActivationCode $activationCode, LicenseSigner $signer): JsonResponse
    {
        abort_if($activationCode->revoked_at, 422, 'Code is revoked.');
        return response()->json(['offline_key' => $signer->sign($activationCode, $activationCode->install_id ?: '*')]);
    }

    public function logs(Request $request): JsonResponse
    {
        $q = ActivationLog::latest('created_at');
        if ($request->filled('subscription_id')) {
            $q->where('subscription_id', $request->integer('subscription_id'));
        }
        return response()->json($q->paginate(100));
    }
}
