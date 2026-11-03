<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApplicationController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Application::orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json(Application::create($this->validated($request)), 201);
    }

    public function update(Request $request, Application $application): JsonResponse
    {
        $application->update($this->validated($request, $application));
        return response()->json($application);
    }

    private function validated(Request $request, ?Application $app = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('applications', 'code')->ignore($app)],
            'name' => 'required|string|max:255',
        ]);
        $data['code'] = strtoupper($data['code']);
        return $data;
    }
}
