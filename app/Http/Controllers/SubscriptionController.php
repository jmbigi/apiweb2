<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use App\Models\SubscribedUser;
use App\Models\User;
use Carbon\Carbon;
use PayPal\Api\WebhookEvent;
use PayPal\Auth\OAuthTokenCredential;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class SubscriptionController extends Controller
{
	protected $provider;
	public function __construct()
	{
		$this->provider = new PayPalClient;
		$this->provider->setApiCredentials(config('paypal'));
	}
	public function subscribed_user(Request $request)
	{

		$this->provider->getAccessToken();

		$existingSubscribedUser = SubscribedUser::with('subscriptionPlan')->where('user_id', $request->user_id)->where('subscription_plan_id', $request->subscription_plan_id)->first();
		$subscriptionPlan = SubscriptionPlan::find($request->subscription_plan_id);

		if ($existingSubscribedUser) {
			// dd($existingSubscribedUser);
			$end_date = $existingSubscribedUser->subscriptionPlan->end_date;
			if (!empty($end_date)) {
				if ($end_date > Carbon::now()) {
					return response()->json([
						'status' => false,
						'message' => 'User is already subscribed, and their subscription is active.',
					], 200);
				} else {
					return response()->json([
						'status' => false,
						'message' => 'This subscription plan is ended',
					], 200);
				}
			} else {
				return response()->json([
					'status' => false,
					'message' => 'User is already subscribed, but their subscription has expired or has no end date.',
				], 200);
			}
		} else {
			$plan = SubscriptionPlan::findOrFail($request->subscription_plan_id);
			$user = User::findOrFail($request->user_id);
			// $subscription = $this->provider->showSubscriptionDetails('I-R270L4AG815K');
			// dd($subscription);
			// $data = json_decode('{
			// 	"plan_id": "' . $plan->plan_id . '",
			// 	"start_time": "2023-11-01T00:00:00Z",
			// 	"shipping_amount": {
			// 	  "currency_code": "USD",
			// 	  "value": "' . $plan->price . '"
			// 	},
			// 	"subscriber": {
			// 	  "name": {
			// 		"given_name": "' . $user->name . '",
			// 		"surname": ""
			// 	  },
			// 	  "email_address": "' . $user->email . '",
			// 	  "shipping_address": {
			// 		"name": {
			// 		  "full_name": "' . $user->name . '"
			// 		},
			// 		"address": {
			// 		  "address_line_1": "2211 N First Street",
			// 		  "address_line_2": "Building 17",
			// 		  "admin_area_2": "San Jose",
			// 		  "admin_area_1": "CA",
			// 		  "postal_code": "95131",
			// 		  "country_code": "US"
			// 		}
			// 	  }
			// 	},
			// 	"application_context": {
			// 	  "brand_name": "walmart",
			// 	  "locale": "en-US",
			// 	  "shipping_preference": "SET_PROVIDED_ADDRESS",
			// 	  "user_action": "SUBSCRIBE_NOW",
			// 	  "payment_method": {
			// 		"payer_selected": "PAYPAL",
			// 		"payee_preferred": "IMMEDIATE_PAYMENT_REQUIRED"
			// 	  },
			// 	  "return_url": "https://example.com/returnUrl",
			// 	  "cancel_url": "https://example.com/cancelUrl"
			// 	}
			//   }', true);

			// $subscription = $this->provider->createSubscription($data);

			//   dd($subscription['id']);
			$subscribedUser = new SubscribedUser();
			$subscribedUser->user_id = $request->user_id;
			$subscribedUser->subscription_plan_id = $request->subscription_plan_id;
			// $subscribedUser->paypal_subscription_id = $subscription['id'];
			$subscribedUser->plan()->associate($subscriptionPlan);
			$subscribedUser->save();
		}

		return response()->json([
			'status' => true,
			'message' => 'User Subscribed To Plan',
			'data' => $subscribedUser
		], 200);
	}
	public function subscription_plans_list()
	{
		// $subscription_plans = SubscriptionPlan::where('end_date', null)->orWhere('end_date', '>', Carbon::now())->get();
		$subscription_plans = SubscriptionPlan::where(function ($query) {
			$query->where('end_date', null)
				  ->orWhere('end_date', '>', Carbon::now());
		})
		->where('status', 1)
		->orderBy('price','asc')
		->get();
		$encryptedSubscriptionPlans = $subscription_plans->map(function ($plan) {
			// Encrypt plan_id and user_id separately
			if($plan->type != 0){
				$user_id = auth()->user()->id;
				$plan_id = $plan->id;
				// $route_data = '{"user_id":'.auth()->user()->id.', plan_id:'.$plan->id.'}';
				$route_data = '{"user_id":' . $user_id . ', "plan_id":' . $plan_id . '}';		
				$encrypted_data = $this->encrypt_data($route_data);
				$plan->payment = env('APP_URL').'subscription-payment/'.$encrypted_data;
		
				return $plan;
			}else{
				$plan->plan_id = '';
				$plan->payment = '';
			}
		});
		
		// $encrypt = $this->encrypt_data($route_data);
		// $decrypt = $this->encrypt_data($encrypt, 'd');
		return response()->json([
			'status' => true,
			'message' => 'Subscription Plans',
			'data' => $subscription_plans
		], 200);
	}

	public function subscription_payment(Request $request) {
		// dd($request);
		$user_id = $request->user_id;
		$plan_id = $request->plan_id;
		$paypal_plan = SubscriptionPlan::findOrFail($plan_id);
		return view('admin.subscription.payment_button',compact('paypal_plan','user_id'));
	}

	public function subscription_status(Request $request) {
		// dd($request);		
		$price = $request->input('price');
		// return view('admin.subscription.payment_status',compact('price'));
		if(!empty($price)){
			return response()->json([
				'status' => true,
				'message' => 'Payment Success',
				'data' => $price
			], 200);
		}else{
			return response()->json([
				'status' => false,
				'message' => 'Payment Failed',
			], 200);
		}
	}
	public function encrypt_data($string, $action = 'e')
	{
		// you may change these values to your own
		$secret_key = 'password212';
		$secret_iv = 'secret121';
	
		$output = false;
		$encrypt_method = "AES-256-CBC";
	
		$key = hash('sha256', $secret_key);
		$iv = substr(hash('sha256', $secret_iv), 0, 16);
	
		if ($action == 'e') {
			$output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
		} else if ($action == 'd') {
			$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
		}
	
		return $output;
	}
}
