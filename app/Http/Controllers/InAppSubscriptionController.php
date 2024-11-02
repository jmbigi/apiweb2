<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use App\Models\SubscribedUser;
use App\Models\User;
use Carbon\Carbon;
use App\Services\SubscriptionService;

class InAppSubscriptionController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }
		
	public function syncSubscribe(Request $request)
	{
		$planType = $request->type;
	
		if ($planType === null) {
			return response()->json([
				'status' => false,
				'message' => 'The plan type cannot be null.',
				'data' => null
			], 400);
		}
	
		$subscriptionService = new SubscriptionService();
	
		if (!$subscriptionService->updateSubscription($planType)) {
			return response()->json([
				'status' => false,
				'message' => 'No subscription plan found for the provided type.',
				'data' => null
			], 404);
		}
	
		return response()->json([
			'status' => true,
			'message' => 'User subscribed to the plan.',
			'data' => $subscriptionService->getSubscriptionDetails()
		], 200);
	}
	
}
