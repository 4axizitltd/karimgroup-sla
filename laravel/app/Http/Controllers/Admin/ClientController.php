<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Client::withCount('subscriptions')->orderBy('name')->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $client = Client::create($this->validated($request));
        return response()->json($client, 201);
    }

    public function show(Client $client): JsonResponse
    {
        $client->load(['subscriptions.application', 'subscriptions.payments' => fn ($q) => $q->latest('period_from'),
            'subscriptions.activationCodes' => fn ($q) => $q->latest()]);
        $client->subscriptions->each(fn ($s) => $s->setAttribute('covered_until', $s->coveredUntil()));
        return response()->json($client);
    }

    public function update(Request $request, Client $client): JsonResponse
    {
        $client->update($this->validated($request, $client));
        return response()->json($client);
    }

    private function validated(Request $request, ?Client $client = null): array
    {
        $data = $request->validate([
            'code'           => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('clients', 'code')->ignore($client)],
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'email'          => 'nullable|email',
            'address'        => 'nullable|string',
            'is_active'      => 'boolean',
        ]);
        $data['code'] = strtoupper($data['code']);
        return $data;
    }
}
