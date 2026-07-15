<?php

namespace App\Http\Controllers\admin;

use App\Models\SubscribedUser;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DataTables;

class SubscribedUserController extends Controller
{
    public function index(Request $request)
    {
	    if ($request->ajax()) {
	    	// $data = User::select('*');
	    	$user = auth()->user();
	    	
	    		$data = SubscribedUser::all();
	    	
                $counter = 1; 
                return Datatables::eloquent(SubscribedUser::query()
                // ->join('users', 'users.id', '=', 'subscribed_user.user_id')
                // ->join('subscription_plan', 'subscription_plan.id', '=', 'subscribed_user.subscription_plan_id')
                // ->select('subscribed_user.*', 'users.name as user_name','subscription_plan.name as plan_name')
                ) // Specify the columns you want to select
                ->filter(function ($instance) use ($request) {
                    if ($request->has('search') && !is_null($request->get('search')['value'])) { 
                        $regex = $request->get('search')['value'];
                        $instance->where(function ($query) use ($regex) {
                            $query->whereHas('subscriptionPlan', function ($subQuery) use ($regex) {
                                $subQuery->where('user_id', 'like', '%' . $regex . '%');
                            })->orWhereHas('user', function ($subQuery) use ($regex) {
                                $subQuery->where('subscription_plan_id', 'like', '%' . $regex . '%');
                            });
                        });
                    }
                    if ($request->has('order') && !empty($request->has('order') && $request->order[0]['column'] != 0)) {
                        if ($request->order[0]['column'] == 1) {
                            $instance->orderBy('user_id', $request->order[0]['dir']);
                        }
                        if ($request->order[0]['column'] == 2) {
                            $instance->orderBy('subscription_plan_id', $request->order[0]['dir']);
                        }
                    }
                })
            ->addColumn('index', function ($row) use (&$counter) {
                return $row->id;
            })
	        ->addColumn('username', function($row){
                return $row->user_id;
	        })	       
            ->addColumn('planname', function ($row) {
                return $row->subscription_plan_id;
	        })
	        ->addColumn('register_date', function ($row) {
	            return $row->created_at->format('Y-m-d');
	        })
            ->addColumn('plan_end_date', function ($row) {
	            return $row->subscription_end_date;
	        })

	        // ->rawColumns(['index'])
	        ->make(true);
	    }
	    else{
	      $data = SubscribedUser::oldest('id')->take(20)->get();
	      return view('admin.subscribed_user.index')->with('users',$data); 
	    }
        return view('admin.subscribed_user.index');
    }
}
