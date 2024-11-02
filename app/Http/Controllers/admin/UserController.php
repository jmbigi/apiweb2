<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Composer;
use DataTables;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;

use function PHPUnit\Framework\isEmpty;
use App\Services\SubscriptionService;


class UserController extends Controller
{
	public static $DEFAULT_ROLE = 'musician';

    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
       $this->middleware('auth');
	   $this->subscriptionService = $subscriptionService;
	}

    public function index(Request $request)
    {
	    if ($request->ajax()) {
	    	// $data = User::select('*');
	    	$user = auth()->user();
	    	
	    		$data = User::where('email', '!=', 'superadmin@gmail.com');
	    	
                $counter = 1; 
	      	return Datatables::of($data)->filter(function ($instance) use ($request) {
	        
	        	if ($request->has('search') && !is_null($request->get('search')['value'])) { 
	            	$regex = $request->get('search')['value'];
		            $instance->where(function($q) use($regex) {
		            	$q->where('name', 'like', '%' . $regex . '%')
		              	->orWhere('email', 'like', '%' . $regex . '%');
		            });
	          	}
		        if ($request->has('order') && !empty($request->has('order') && $request->order[0]['column']!= 0)) {
		        	if ($request->order[0]['column'] == 1) { 
		            	$instance->orderBy('users.name', $request->order[0]['dir']);
		            }
		            if ($request->order[0]['column'] == 2) {
		                $instance->orderBy('users.email', $request->order[0]['dir']);
		            } 
					
		        }
	        })
            ->addColumn('index', function ($row) use (&$counter) {
                return '<a href="' . route('user.edit', ['id' => $row->id]) . '">' . $counter++ . '</a>';
            })
	        ->addColumn('name', function($row){
	        	return $row->name;
	        })	       
            
	        ->addColumn('register_date', function ($row) {
	            return $row->created_at;
	        })
	        ->addColumn('last_updated', function ($row) {
	            return $row->updated_at;
	        })

			->addColumn('status', function ($row) {
                $checked = $row->status ? 'checked' : ''; 
            //     return '<label class="switch mt-2">
            //     <input 
			// 	  id="user_status_check"
            //       type="checkbox" 
            //       class="user_status"
            //       name="my-checkbox" 
            //       data-switch="true"
            //       data-size="small"
            //       data-on-text="Active" 
            //       data-off-text="Suspended"
            //       data-on-color="primary"
            //       data-off-color="danger"
            //       data-id="' . $row->id . '" 
            //       ' . $checked . '>
            //     <span></span>
            // </label>';

			// return '<input id="edit_user_status" data-id="' . $row->id . '" data-user="'.{{route('change_user_status')}}.'" name="user_status" class="toggle-class edit_user_status" type="checkbox" data-on="Active" data-off="Suspended"  data-toggle="toggle" data-offstyle="danger" ' . $checked . ' >';
			return '<input id="edit_user_status" data-id="' . $row->id . '" data-status="' . route('change_user_status') . '" name="user_status" class="toggle-class edit_user_status" type="checkbox" data-on="Active" data-off="Suspended"  data-toggle="toggle" data-offstyle="danger" ' . $checked . ' >';


            })
			
			->addColumn('action', function($row){
				$btn = '<a href ="#" data-id="'.$row->id.'" class="delete_user mx-3 "><i class="fas fa-trash fa-lg text-danger"></i></a>';                            	
                return $btn;

            })

	        ->rawColumns(['index','status','action'])
	        ->make(true);
	    }
	    else{
	      $users = User::oldest('id')->take(20)->get();
	      return view('admin.users.index')->with('users',$users); 
	    }
        return view('admin.users.index');
    }

	public function create() {
		return view('admin.users.create');
	}

	public function store(Request $request) {
		if($request->telephone){
			if($request->country_code){
				$telephone = '(+'.$request->country_code.')'.$request->telephone;
			}
			else{
				$telephone = '(+34)'.$request->telephone;
			}
		}else{
			$telephone = null;
		}
		
		
		$request->validate([
		    'name' => ['required', 'string', 'max:255'],
		    'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
		    'password' => ['required', 'string', 'min:8'],
			'telephone' => ['required','numeric'],
		]);

		if ($request->has('telephone')) {	
			$existingUser = User::where('telephone', $telephone)->where('telephone','!=',null)->first();
			if ($existingUser) {
				return redirect()->back()->withErrors(['telephone' => 'The telephone number is not unique.'])->withInput();
			}
		}
		if ($request->user_status == 'on') {
			$status = 1;
		}else {
			$status = 0;
		}
		$user = User::create([
			'name' => $request->name,
			'email' => $request->email,
			'telephone' => $telephone ?? null,
			'status' => $status,
			'password' => Hash::make($request->password),
		]);
		$defaultRole = Role::where('name', self::$DEFAULT_ROLE)->first();
        // dd($defaultRole);
        $user->attachRole($defaultRole);
		return redirect()->route('user.index')->with('success','User created successfully.'	);
	}
    public function edit($id) {
    	$user  = User::findOrFail($id);
		$subscription_service = new SubscriptionService();
		$subscription_data = $subscription_service->getSubscriptionDetailsById($id);
	
    	return view('admin.users.edit',compact('user', 'subscription_data'));
    }

	public function update(Request $request) {
		$id = $request->id;
		$currentEmail = User::where('id', $id)->value('email');
		$uniqueRule = Rule::unique('users')->ignore($id);
		if($request->telephone){
			if($request->country_code){
				$telephone = '(+'.$request->country_code.')'.$request->telephone;
			}else{
				$telephone = '(+34)'.$request->telephone;
			}
		}
		else{
			$telephone = null;
		}
		
		if ($request->email !== $currentEmail) {
			$uniqueRule->where(function ($query) use ($request) {
				$query->where('email', $request->email);
			});
		}
		if ($request->has('telephone')) {	
			$existingUser = User::where('telephone', $telephone)->where('id','!=',$id)->where('telephone','!=',null)->first();
			if ($existingUser) {
				return redirect()->back()->withErrors(['telephone' => 'The telephone number is not unique.'])->withInput();
			}
		}
		$user  = User::findOrFail($id); 
		$password = $user->password;
		if($request->password != $password){ 
			$password = Hash::make($request->password);
		}
		
		if ($request->user_status == 'on') {
			$status = 1;
		}else {
			$status = 0;
		}
		$request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', $uniqueRule],
			'password' => ['required', 'string', 'min:8'],
			'telephone' => ['nullable','numeric'],
        ]);

        $user = User::where('id', $id)->update([
            'name' => $request->name,
            'email' => $request->email,
			'telephone' => $telephone ?? null,
			'status' => $status,
			'password' => $password,
        ]);
		//
		if ($request->sync_subscr_type != null && $request->sync_subscr_type >= 0) {
			$planType = $request->sync_subscr_type;
			$subscriptionService = new SubscriptionService();
			$subscriptionService->updateSubscriptionById($id, $planType);
		}
		//
		return redirect()->route('user.index')->with('success','User updated successfully.'	);
	}

    public function change_user_status(Request $request) {
        $user = User::find($request->id);
        $user->status = $request->status;
        $user->save();
  
        return response()->json(['success'=>'Status change successfully.']);
    }
	public function destroy(Request $request) {
    	$id = $request->id;
        $user = User::findOrFail($id);
		// $role = $user->roles->first()->name;
		$composer_id = Composer::where('users_id',$id)->get()->toArray();
		// dd($composer_id);

		if(empty($composer_id)){
			// dd('force');
            $user->forceDelete(); 
        }else {
			// dd('delete');
            $user->delete();
        }
       	return response()->json(['success'=>'User Deleted successfully.']);
    }
}
