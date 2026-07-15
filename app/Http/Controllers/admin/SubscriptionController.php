<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use App\Models\SubscribedUser;
use App\Models\User;
use App\Models\Order;
use App\Models\Webhook;
use DataTables;
use Carbon\Carbon;
use App\Notifications\PlanRenewNotify;
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
	public function index(Request $request)
	{
		$this->provider->getAccessToken();
		// $plans = $this->provider->showPlanDetails('P-1UH57627PV866313TMVPSEQA');
		// dd($plans);
		// $plan_id = 'P-1UH57627PV866313TMVPSEQA';

		// $plan = $this->provider->showPlanDetails($plan_id);
		// $plans = $this->provider->listPlans();
		// dd($plan);
		// $response = $this->provider->cancelSubscription('I-VPB1B01J9SJB', 'Not satisfied with the service');
		// $subscription = $this->provider->showSubscriptionDetails('I-RXRRKHFDEP8H');
		// dd($subscription);
		
		if ($request->ajax()) {
			// $data = User::select('*');
			// $user = auth()->user();

			$data = SubscriptionPlan::all();

			$counter = 1;
			return Datatables::eloquent(SubscriptionPlan::query())
				->filter(function ($query) use ($request) {
					if ($request->has('search') && !is_null($request->get('search')['value'])) {
						$regex = $request->get('search')['value'];
						$query->where(function ($q) use ($regex) {
							$q->where('name', 'like', '%' . $regex . '%')
								->orWhere('price', 'like', '%' . $regex . '%');
						});
					}
					if ($request->has('order') && !empty($request->has('order') && $request->order[0]['column'] != 0)) {
						if ($request->order[0]['column'] == 1) {
							$query->orderBy('name', $request->order[0]['dir']);
						}
						if ($request->order[0]['column'] == 2) {
							$query->orderBy('price', $request->order[0]['dir']);
						}
					}
				})
				->addColumn('index', function ($row) use (&$counter) {
					return '<a href="' . route('subscription.edit', ['id' => $row->id]) . '">' . $counter++ . '</a>';
				})
				->addColumn('name', function ($row) {
					return $row->name;
				})

				->addColumn('description', function ($row) {
					return $row->description;
				})
				->addColumn('price', function ($row) {
					return $row->price;
				})
				->addColumn('no_of_subscribed_user', function ($row) {
					return $row->subscribers->count();
				})
				->addColumn('start_date', function ($row) {
					return $row->start_date;
				})
				->addColumn('end_date', function ($row) {
					return $row->end_date;
				})
				->addColumn('status', function ($row) {
					$checked = $row->status ? 'checked' : '';


					// return '<input id="edit_user_status" data-id="' . $row->id . '" data-user="'.{{route('change_user_status')}}.'" name="user_status" class="toggle-class edit_user_status" type="checkbox" data-on="Active" data-off="Suspended"  data-toggle="toggle" data-offstyle="danger" ' . $checked . ' >';
					return '<input id="edit_subscription_status" data-id="' . $row->id . '" data-status="' . route('change_subscription_status') . '" name="edit_subscription_status" class="toggle-class edit_subscription_status" type="checkbox" data-on="Active" data-off="Suspended"  data-toggle="toggle" data-offstyle="danger" ' . $checked . ' >';
				})

				->addColumn('action', function ($row) {
					$btn = '<a href ="#" data-id="' . $row->id . '" class="delete_plan mx-3 "><i class="fas fa-trash fa-lg text-danger"></i></a>';
					return $btn;
				})

				->rawColumns(['index', 'status', 'action'])
				->make(true);
		} else {
			$users = SubscriptionPlan::oldest('id')->take(20)->get();
			return view('admin.subscription.index')->with('users', $users);
		}
		return view('admin.subscription.index');
	}

	public function create()
	{
		return view('admin.subscription.create');
	}

	public function store(Request $request)
	{	
		$request->validate([
			'name' => ['required', 'string', 'max:255'],
			'description' => ['required', 'string', 'max:255'],
			'price' => [
				'required',
				'numeric',
				'regex:/^\d+(\.\d{1,2})?$/',
			],
			'start_date' => [
				'required',
				'date',
				function ($attribute, $value, $fail) use ($request) {
					// Check if 'end_date' is not null
					if (!is_null($request->input('end_date'))) {
						// Validate 'start_date' is before 'end_date'
						if (strtotime($value) >= strtotime($request->input('end_date'))) {
							$fail('The start date must be before the end date.');
						}
					}
				},
			],
		]);
		$typeLabels = [
			0 => 'FREE',
			1 => 'BASIC',
			2 => 'PREMIUM',
		];
		if($request->subscription_type != 0){			
			$exist_product_id = User::where('id', 1)->pluck('product_id')->first();
			$this->provider->getAccessToken();
			$data =  json_decode('{
				"name": "Video Streaming Service",
				"description": "Video streaming service",
				"type": "SERVICE",
				"category": "SOFTWARE",
				"image_url": "https://example.com/streaming.jpg",
				"home_url": "https://example.com/home"
			}', true);

			$request_id = 'create-product-' . time();
			$product = $this->provider->createProduct($data, $request_id);
			if (!empty($exist_product_id)) {
				// User::where('id', 1)->update(['product_id' => $product['id']]);
				$product['id'] = $exist_product_id;
			} else {
				User::where('id', 1)->update(['product_id' => $product['id']]);
			}
			// $data = json_decode('{
			// 	"product_id": "' . $product['id'] . '",
			// 	"name": "'.$request->name.'",
			// 	"description": "'.$request->description.'",
			// 	"status": "ACTIVE",
			// 	"billing_cycles": [			
			// 	  {
			// 		"frequency": {
			// 		  "interval_unit": "MONTH",
			// 		  "interval_count": 1
			// 		},
			// 		"tenure_type": "REGULAR",
			// 		"sequence": 1,
			// 		"total_cycles": 12,
			// 		"pricing_scheme": {
			// 		  "fixed_price": {
			// 			"value": "'.$request->price.'",
			// 			"currency_code": "USD"
			// 		  }
			// 		}
			// 	  }
			// 	],
			// 	"payment_preferences": {
			// 	  "auto_bill_outstanding": true,
			// 	  "setup_fee": {
			// 		"value": "10",
			// 		"currency_code": "USD"
			// 	  },
			// 	  "setup_fee_failure_action": "CONTINUE",
			// 	  "payment_failure_threshold": 3
			// 	},
			// 	"taxes": {
			// 	  "percentage": "10",
			// 	  "inclusive": false
			// 	}
			//   }', true);

			$data = json_decode('{
				"product_id": "' . $product['id'] . '",
				"name": "'.$request->name.'",
				"description": "'.$request->description.'",
				"status": "ACTIVE",
				"billing_cycles": [			
				{
					"frequency": {
						"interval_unit": "MONTH",
						"interval_count": 1
					},
					"tenure_type": "REGULAR",
					"sequence": 1,
					"total_cycles": 0,
					"pricing_scheme": {
					"fixed_price": {
						"value": "'.$request->price.'",
						"currency_code": "USD"
					}
					}
				}
				],
				"payment_preferences": {
				"auto_bill_outstanding": true,
				"setup_fee": {
					"value": "0",
					"currency_code": "USD"
				},
				"setup_fee_failure_action": "CONTINUE",
				"payment_failure_threshold": 3
				},
				"taxes": {
				"percentage": "10",
				"inclusive": false
				}
			}', true);

			$plan = $this->provider->createPlan($data);
			//   dd($plan);
			
			// dd($product['id']);
			$user = SubscriptionPlan::create([
				'name' => $request->name,
				'description' => $request->description,
				'price' => $request->price,
				'start_date' => $request->start_date,
				'end_date' => $request->end_date,
				'status' => $request->plan_status,
				'plan_id' => $plan['id'],
				'type' => $request->subscription_type,
				'type_label' => $typeLabels[$request->subscription_type],

			]);
		}else{
			$user = SubscriptionPlan::create([
				'name' => $request->name,
				'description' => $request->description,
				'price' => 0,
				'start_date' => $request->start_date,
				'end_date' => $request->end_date,
				'status' => $request->plan_status,
				'plan_id' => null,
				'type' => $request->subscription_type,
				'type_label' => $typeLabels[$request->subscription_type],

			]);
		}
		return redirect()->route('subscription.index')->with('success', 'Subscription Plan created successfully.');
	}
	public function edit($id)
	{
		$subscription  = SubscriptionPlan::findOrFail($id);
		return view('admin.subscription.edit', compact('subscription'));
	}

	public function update(Request $request)
	{
		$typeLabels = [
			0 => 'FREE',
			1 => 'BASIC',
			2 => 'PREMIUM',
		];
		$this->provider->getAccessToken();
		
		$id = $request->id;
		$subscription  = SubscriptionPlan::findOrFail($id);
		$request->validate([
			'name' => ['required', 'string', 'max:255'],
			'description' => ['required', 'string', 'max:255'],
			'price' => [
				'required',
				'numeric',
				'regex:/^\d+(\.\d{1,2})?$/',
			],
			'start_date' => [
				'required',
				'date',
				function ($attribute, $value, $fail) use ($request) {
					// Check if 'end_date' is not null
					if (!is_null($request->input('end_date'))) {
						// Validate 'start_date' is before 'end_date'
						if (strtotime($value) >= strtotime($request->input('end_date'))) {
							$fail('The start date must be before the end date.');
						}
					}
				},
			],
		]);

		$plan_id = $subscription->plan_id;
		if($request->subscription_type != 0){	
			$pricing = json_decode('[
				{
					"billing_cycle_sequence": 2,
					"pricing_scheme": {
					"fixed_price": {
						"value": "'.$request->price.'",
						"currency_code": "USD"
					}
					}
				}
				]', true);
			
			$planPrice = $this->provider->updatePlanPricing($plan_id, $pricing);		  

			$data = json_decode('[
				{
				"op": "replace",
				"path": "/name",
				"value": "'.$request->name.'"
				},
				{
					"op": "replace",
					"path": "/description",
					"value": "'.$request->description.'"
				}
			]', true);
			
			$plan = $this->provider->updatePlan($plan_id, $data);
				// dd($plan);

			$user = SubscriptionPlan::where('id', $id)->update([
				'name' => $request->name,
				'description' => $request->description,
				'price' => $request->price,
				'start_date' => $request->start_date,
				'end_date' => $request->end_date,
				'status' => $request->plan_status,
				'type' => $request->subscription_type,
				'type_label' => $typeLabels[$request->subscription_type],
			]);
		}
		else{
			if(!empty($plan_id)){
				$plan = $this->provider->deactivatePlan($plan_id);
			}
			$user = SubscriptionPlan::where('id', $id)->update([
				'name' => $request->name,
				'description' => $request->description,
				'price' => 0,
				'plan_id' => null,
				'start_date' => $request->start_date,
				'end_date' => $request->end_date,
				'status' => $request->plan_status,
				'type' => $request->subscription_type,
				'type_label' => $typeLabels[$request->subscription_type],
			]);
		}
		return redirect()->route('subscription.index')->with('success', 'Subscription Plan updated successfully.');
	}


	public function destroy(Request $request)
	{
		$this->provider->getAccessToken();
		$id = $request->id;
		$subscription = SubscriptionPlan::findOrFail($id);
		if ($subscription->subscribers->count() > 0) {
			return response()->json(['error' => 'Cannot delete the subscription plan as it has subscribed users.']);
		} else {
			$subscription->delete();
			$plan = $this->provider->deactivatePlan($subscription->plan_id);
			return response()->json(['success' => 'Your record has been deleted.']);
		}
	}

	public function change_subscription_status(Request $request)
	{
		$this->provider->getAccessToken();
		$status_request = SubscriptionPlan::find($request->id);
		if ($request->status == 1) {
			$status = 1;
			$plan = $this->provider->activatePlan($status_request->plan_id);
			$msg = 'Plan activate successfully';
		} else {
			$plan = $this->provider->deactivatePlan($status_request->plan_id);
			$status = 0;
			$msg = 'Plan deactivate successfully';
		}
		$status_request->status =  $status;
		$status_request->save();

		return response()->json(['success' => $msg]);
	}

	public function subscription_payment(Request $request,$id) {
		// $requesturi = $request->requestUri;
		// $subscriptionPaymentSegmentIndex = $request->segment(2); // Assuming "subscription-payment" is the second segment

		// // Get all segments after "subscription-payment"
		// $dataAfterSubscriptionPayment = $request->segment($subscriptionPaymentSegmentIndex + 1);
	
		// $queryString = $request->getQueryString();
		$decrypt = $this->encrypt_data($id, 'd');
		$decode_data = json_decode($decrypt);
		// dd($decode_data);
		// dd($request);
		// dd($id);
		// $decryptedData = Crypt::decryptString();
		$paypal_plan = SubscriptionPlan::findOrFail($decode_data->plan_id);
		$user_id = $decode_data->user_id;
		return view('admin.subscription.payment_button',compact('paypal_plan','user_id'));
	}

	public function subscription_order(Request $request) {
		// dd($request);
		$this->provider->getAccessToken();
		$user_id = $request->get('user_id');
		$order_id = $request->get('data')['orderID'];
		$paymentSource = $request->get('data')['paymentSource'];
		$subscriptionID = $request->get('data')['subscriptionID'];
		$paypal_plan_id = $request->get('paypal_plan_id');
		$subscription_plan_id = $request->get('subscription_plan_id');
		$subscription_plan_name = $request->get('subscription_plan_name');
		$subscription_plan_price = $request->get('subscription_plan_price');
		$order = Order::create([
			'userId' => $user_id,
			'subscription_plan_id' => $subscription_plan_id,
			'orderId' => $order_id,
			'paymentSource' => $paymentSource,
			'subscriptionID' => $subscriptionID,
			'paypal_plan_id' => $paypal_plan_id,
			'paypal_plan_name' => $subscription_plan_name,
			'paypal_plan_price' => $subscription_plan_price,
			'message' => 'User Plan Created'
		]);
		$existSubscriber = SubscribedUser::where('user_id',$user_id)->first();
		$subscriptionPlan = SubscriptionPlan::find($paypal_plan_id);
		$endDate = Carbon::now()->addMonth();
		if(!empty($existSubscriber)){
			if(!empty($existSubscriber->paypal_subscription_id)){
				$response = $this->provider->cancelSubscription($existSubscriber->paypal_subscription_id, 'Subscription cancled');
			}
			// dd($response);
			$data = json_decode('[
				{
				  "op": "replace",
				  "path": "/billing_info/outstanding_balance",
				  "value": {
					"currency_code": "USD",
					"value": "'.$subscription_plan_price.'"
				  }
				}
			  ]', true);
			  
			$response = $this->provider->updateSubscription($subscriptionID, $data);
			//   dd($data);
			$existSubscriber->user_id = $user_id;
			$existSubscriber->subscription_end_date = $endDate;
			$existSubscriber->paypal_plan_id = $paypal_plan_id;
			$existSubscriber->paypal_subscription_id = $subscriptionID;
			$existSubscriber->subscription_plan_id = $subscription_plan_id;
			// $existSubscriber->plan()->associate($subscriptionPlan);
			// $existSubscriber->subscriptionPlan()->associate($subscriptionPlan);
			$existSubscriber->save();
		}else{
			$subscribedUser = new SubscribedUser();
			$subscribedUser->user_id = $user_id;
			$subscribedUser->subscription_end_date = $endDate; 
			$subscribedUser->paypal_plan_id = $paypal_plan_id;
			$subscribedUser->paypal_subscription_id = $subscriptionID;
			$subscribedUser->subscription_plan_id = $subscription_plan_id;
			// $subscribedUser->plan()->associate($subscriptionPlan);
			// $existSubscriber->subscriptionPlan()->associate($subscriptionPlan);
			$subscribedUser->save();
		}

		if(!empty($order->id)){
			return response()->json(['success' => 'Payment successfully.','price'=>$subscription_plan_price,'subscription_plan_id'=>$subscription_plan_id]);
			// return view('admin.subscription.payment_status',compact('order'));
		}else{
			// return view('admin.subscription.payment_status');
			return response()->json(['error' => 'Payment failed.']);
		}
	}

	public function subscription_status(Request $request) {		
		$price = $request->input('price');
		$subscription_plan = SubscriptionPlan::findOrFail($request->input('subscription_plan_id'));
		// dd($subscription_plan);
		return view('admin.subscription.payment_status',compact('price','subscription_plan'));
	}

	public function paypal_webhook(Request $request) {
		
		// dd(json_encode($request->all()));
		// try{
		// 	//dd($request->all());
		 	\Log::info(json_encode($request->all()));
		// 	return response()->json(['status' => '200']);
		// }catch (\Exception $e) {

		// 	return $e->getMessage();
		// }

		$webhookData = $request->all();
			// dd($webhookData['resource']['id']);
			// \Log::info(json_encode($webhookData['resource']['id']));
        $event_type = $webhookData["event_type"];
        $resource_type = 'paypal';
		$user_id = $webhookData['resource']["custom_id"] ? $webhookData['resource']["custom_id"] : '';
		$subscriptionID = $webhookData['resource']['id'];
		$paypal_plan_id =  $webhookData['resource']["plan_id"];
		$endDate = Carbon::now()->addMonth();
		$user = User::findOrFail($user_id);

		$subscription_plan = SubscriptionPlan::where('plan_id',$paypal_plan_id)->first();
        // Handle the event based on its type
		$webhook_table = Webhook::create([
			'response' => json_encode($request->all()),
			'plan_id' => $paypal_plan_id,
			'order_id' => null,
			'subscription_id' => $subscriptionID,
			'event_type' => $webhookData["event_type"],
			'status' => $webhookData['resource']['status'],
			'user_id' => $user_id,
		]);
        switch ($event_type) {
            case "BILLING.SUBSCRIPTION.RENEWED":
                $order = Order::create([
					'userId' => $user_id,
					'subscription_plan_id' => $subscription_plan->id,
					'orderId' => null,
					'paymentSource' => $resource_type,
					'subscriptionID' => $subscriptionID,
					'paypal_plan_id' => $paypal_plan_id,
					'paypal_plan_name' => $subscription_plan->name,
					'paypal_plan_price' => $subscription_plan->price,
					'message' => 'User Plan Renewd'
				]);

				$existSubscriber = SubscribedUser::where('user_id',$user_id)->first();
				if(!empty($existSubscriber)){
					$existSubscriber->user_id = $user_id;
					$existSubscriber->subscription_end_date = $endDate;
					$existSubscriber->subscription_plan_id = $subscription_plan->id;
					$existSubscriber->paypal_subscription_id = $subscriptionID;
					$existSubscriber->paypal_plan_id = $paypal_plan_id;
					// $existSubscriber->plan()->associate($subscription_plan);
					$existSubscriber->save();
				}
                break;
            case "BILLING.SUBSCRIPTION.CANCELLED":
                $this->onSubscriptionCancel($webhookData);
                break;
            case "PAYMENT.SALE.COMPLETED":
                $this->onPaymentCompleted($webhookData);
                break;
            case "BILLING.SUBSCRIPTION.PAYMENT.FAILED":
                $this->onPaymentFailed($webhookData);
                break;
            case "BILLING.SUBSCRIPTION.SUSPENDED":
                $this->onSubscriptionSuspended($webhookData);
                break;{{}}
        }
	}

	public function paypalRenewEmail(){
		$currentDate = Carbon::now();
		$threeDaysBefore = $currentDate->addDays(3);

		$subscriptions = SubscribedUser::whereDate('subscription_end_date', '=', $threeDaysBefore->toDateString())->get();
		// dd($subscriptions);
		foreach($subscriptions as $subscription){
			$user  = User::find($subscription->user_id); 
			$subscription_plan = SubscriptionPlan::where('id',$subscription->subscription_plan_id)->first();

			$user->notify(new PlanRenewNotify(['plan_name'=>$subscription_plan->name, 'description'=> $subscription_plan->description,'user_name'=> $user->name ])); 

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
