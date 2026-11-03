<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivationCode;
use App\Models\ActivationLog;
use App\Models\Subscription;
use App\Services\LicenseSigner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Called by the client software (karimgroup). See tools/README.md for the contract.
 * Always answers HTTP 200 with {"ok": ...} so the old PHP 5.6 client can read the message.
 */
class LicenseController extends Controller
{
    public function __construct(private LicenseSigner $signer) {}

    /** POST /api/activate  code, client_code, app_code, install_id, hostname */
    public function activate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code'        => 'required|string|max:40',
            'client_code' => 'required|string|max:20',
            'app_code'    => 'required|string|max:20',
            'install_id'  => 'required|string|max:64',
            'hostname'    => 'nullable|string|max:255',
        ]);
        $codeStr = strtoupper(trim($data['code']));
        $today = now()->toDateString();

        $code = ActivationCode::with('subscription.client', 'subscription.application')
            ->where('code', $codeStr)->first();

        $fail = function (string $msg, ?int $subId = null) use ($request, $data, $codeStr) {
            $this->log($request, 'activate', $data, $codeStr, $subId, false, $msg);
            return response()->json(['ok' => false, 'message' => $msg]);
        };

        if (! $code
            || strtoupper($code->subscription->client->code) !== strtoupper($data['client_code'])
            || strtoupper($code->subscription->application->code) !== strtoupper($data['app_code'])) {
            return $fail('Invalid activation code.');
        }
        $sub = $code->subscription;
        if (! $sub->is_active || ! $sub->client->is_active) {
            return $fail('This account is disabled. Please contact 4axiz.', $sub->id);
        }
        if ($code->revoked_at) {
            return $fail('This activation code has been cancelled. Please contact 4axiz.', $sub->id);
        }
        if ($code->install_id && $code->install_id !== $data['install_id']) {
            return $fail('This code is already used on another installation. Please contact 4axiz.', $sub->id);
        }
        if ($code->valid_until->toDateString() < $today) {
            return $fail('This code has already expired (' . $code->valid_until->format('d M Y') . ').', $sub->id);
        }
        if ($code->valid_from->toDateString() > $today) {
            return $fail('This code is valid from ' . $code->valid_from->format('d M Y') . '. Please enter it on or after that date.', $sub->id);
        }

        DB::transaction(function () use ($code, $sub, $data) {
            $code->forceFill([
                'install_id'   => $data['install_id'],
                'hostname'     => $data['hostname'] ?? null,
                'activated_at' => $code->activated_at ?? now(),
                'last_seen_at' => now(),
            ])->save();
            $sub->forceFill(['install_id' => $data['install_id']])->save();
        });

        $this->log($request, 'activate', $data, $codeStr, $sub->id, true, 'Activated until ' . $code->valid_until->toDateString());

        return response()->json(['ok' => true, 'license' => $this->signer->sign($code, $data['install_id'])]);
    }

    /** POST /api/status  client_code, app_code, install_id, license_id */
    public function status(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_code' => 'required|string|max:20',
            'app_code'    => 'required|string|max:20',
            'install_id'  => 'required|string|max:64',
            'license_id'  => 'nullable|string|max:24',
        ]);
        $today = now()->toDateString();

        $sub = Subscription::with('client')->forCodes($data['client_code'], $data['app_code'])->first();
        if (! $sub) {
            $this->log($request, 'status', $data, null, null, false, 'Unknown client/app');
            return response()->json(['ok' => false, 'message' => 'Unknown client.']);
        }

        // Current license cancelled by 4axiz?
        if (! empty($data['license_id'])) {
            $current = $sub->activationCodes()->where('license_id', $data['license_id'])->first();
            if ($current && $current->revoked_at) {
                $this->log($request, 'status', $data, $current->code, $sub->id, true, 'Revoked');
                return response()->json(['ok' => false, 'revoked' => true, 'message' => 'License cancelled by 4axiz.']);
            }
        }
        if (! $sub->is_active || ! $sub->client->is_active) {
            $this->log($request, 'status', $data, null, $sub->id, true, 'Subscription disabled');
            return response()->json(['ok' => false, 'revoked' => true, 'message' => 'Account disabled by 4axiz.']);
        }

        $base = fn () => $sub->activationCodes()->whereNull('revoked_at')
            ->whereDate('valid_from', '<=', $today)->whereDate('valid_until', '>=', $today);

        // Newest code already bound to this installation
        $code = $base()->where('install_id', $data['install_id'])->orderByDesc('valid_until')->first();

        // Paid and generated but not entered yet -> renew automatically for the known installation
        if (! $code && $sub->install_id === $data['install_id']) {
            $code = $base()->whereNull('install_id')->orderByDesc('valid_until')->first();
            if ($code) {
                $code->forceFill(['install_id' => $data['install_id'], 'activated_at' => now()])->save();
            }
        }

        if (! $code) {
            $this->log($request, 'status', $data, null, $sub->id, true, 'No current license');
            return response()->json(['ok' => true]);
        }

        $code->forceFill(['last_seen_at' => now()])->save();
        $this->log($request, 'status', $data, $code->code, $sub->id, true, 'OK until ' . $code->valid_until->toDateString());

        return response()->json(['ok' => true, 'license' => $this->signer->sign($code, $data['install_id'])]);
    }

    private function log(Request $r, string $action, array $data, ?string $code, ?int $subId, bool $ok, string $msg): void
    {
        ActivationLog::create([
            'subscription_id' => $subId,
            'action'          => $action,
            'client_code'     => $data['client_code'] ?? null,
            'app_code'        => $data['app_code'] ?? null,
            'code'            => $code,
            'install_id'      => $data['install_id'] ?? null,
            'ip'              => $r->ip(),
            'success'         => $ok,
            'message'         => mb_substr($msg, 0, 255),
            'created_at'      => now(),
        ]);
    }
}
