<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PushSubscriptionController extends Controller
{
    /**
     * Store a new push subscription.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
            'p256dh_key' => 'required|string',
            'auth_token' => 'required|string',
        ]);

        try {
            // Delete existing subscription for this endpoint if it exists
            PushSubscription::where('endpoint', $validated['endpoint'])->delete();

            // Create new subscription
            $subscription = PushSubscription::create([
                'user_id' => auth()->id(),
                'endpoint' => $validated['endpoint'],
                'p256dh_key' => $validated['p256dh_key'],
                'auth_token' => $validated['auth_token'],
            ]);

            Log::info('Push subscription created', [
                'user_id' => auth()->id(),
                'endpoint' => $validated['endpoint'],
            ]);

            return response()->json([
                'message' => 'Push subscription created successfully',
                'subscription' => $subscription,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create push subscription', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to create push subscription',
            ], 500);
        }
    }

    /**
     * Delete a push subscription.
     */
    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
        ]);

        try {
            $deleted = PushSubscription::where('user_id', auth()->id())
                ->where('endpoint', $validated['endpoint'])
                ->delete();

            if ($deleted) {
                Log::info('Push subscription deleted', [
                    'user_id' => auth()->id(),
                    'endpoint' => $validated['endpoint'],
                ]);

                return response()->json([
                    'message' => 'Push subscription deleted successfully',
                ]);
            }

            return response()->json([
                'message' => 'Push subscription not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to delete push subscription', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to delete push subscription',
            ], 500);
        }
    }

    /**
     * Get user's push subscriptions.
     */
    public function index(): JsonResponse
    {
        try {
            $subscriptions = PushSubscription::where('user_id', auth()->id())->get();

            return response()->json([
                'subscriptions' => $subscriptions,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get push subscriptions', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to get push subscriptions',
            ], 500);
        }
    }
} 