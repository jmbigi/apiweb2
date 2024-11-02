<?php
//Crear controlador para CRUD en tabla ComposerRequest

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ComposerRequest;
use App\Models\ComposerStatus;
use App\Models\RequestStatus;
use App\Models\Composer;
use Illuminate\Http\Request;
use DataTables;
use App\Notifications\ComposerStatusNotify;
use App\Models\Role;

class ComposerRequestController extends Controller
{
    public static $COMPOSER_ROLE = "composer";

    protected static function update_composer_role(ComposerRequest $composer_request) {
        $composer_role = Role::where('name', self::$COMPOSER_ROLE)->firstOrFail();
        $comp_req_user = $composer_request->composer->user;
        if($comp_req_user->roles->contains($composer_role)){
            $comp_req_user->detachRole($composer_role);
        }
        if($composer_request->composer_status_id == 2 && $composer_request->request_status_id == 3  && !$comp_req_user->hasRole($composer_role)) {
            $comp_req_user->attachRole($composer_role);
        } elseif ($composer_request->composer_status_id != 2 && $composer_request->request_status_id != 3 && $comp_req_user->hasRole($composer_role)) {
            $comp_req_user->detachRole($composer_role);
        }    
    }

    public function index(Request $request)
    {
	    if ($request->ajax()) {
	    	// $data = ComposerRequest::select('*');	    	
	    	$data = ComposerRequest::latest();

            if ($request->has('search') && !is_null($request->get('search')['value'])) { 
                $regex = $request->get('search')['value'];
                $data->where(function($q) use($regex) {
                    $q->whereHas('composer', function($query) use($regex) {
                        $query->where('name', 'like', '%' . $regex . '%');
                    })->whereHas('composer', function($query) use($regex) {
                        $query->where('surname', 'like', '%' . $regex . '%');
                    });
                });   
            }
            
            if ($request->has('order') && !empty($request->has('order') && $request->order[0]['column']!= 0)) {
                $regex = $request->order[0]['dir'];
                if ($request->order[0]['column'] == 1) {
                    $data->join('composers', 'composer_request.composers_id', '=', 'composers.id')
                        ->select('composer_request.*','composers.id as composers_id_alias')
                        ->orderBy('composers_id_alias', $regex);
                }
                if ($request->order[0]['column'] == 2) { 
                    $data->join('composers', 'composer_request.composers_id', '=', 'composers.id')
                        ->select('composer_request.*', 'composers.id as composers_id_alias', 'composers.name')
                        ->orderBy('composers.name', $regex);
                }		
                if ($request->order[0]['column'] == 3) { 
                    $data->join('composers', 'composer_request.composers_id', '=', 'composers.id')
                        ->select('composer_request.*', 'composers.id as composers_id_alias', 'composers.surname')
                        ->orderBy('composers.surname', $regex);
                }	
                if ($request->order[0]['column'] == 4) { 
                    $data->orderBy('request_date', $request->order[0]['dir']);
                }	
                if ($request->order[0]['column'] == 5) { 		            	
                    $data->orderBy('updated_at', $request->order[0]['dir']);
                }		            
            }

            $data = $data->get();

            $counter = 1; 
	      	return Datatables::of($data)->addColumn('composer_request_id', function ($row) use (&$counter) {
                // return '<a href="#">' . $counter++ . '</a>';
                if($row->request_status_id == 3 && $row->composer_status_id == 2){
                    return '<span>' . $row->id . '</span>';
                }else{
                    return '<a href="'. route('composer_request.edit', ['id' => $row->id]) . '">' . $row->id . '</a>';
                }

            })
            ->addColumn('user_id', function ($row) use (&$counter) {
                // return '<a href="#">' . $counter++ . '</a>';
                return $row->composers_id;

            })
	        ->addColumn('name', function($row){
                $composer = Composer::find($row->composers_id);
                return $composer ? $composer->name : '';
	        })
            ->addColumn('last_name', function($row){
                $composer = Composer::find($row->composers_id);
                return $composer ? $composer->surname : '';
	        })
	        ->addColumn('requested_date', function ($row) {
	            return date('Y-m-d', strtotime($row->request_date));
	        })
            ->addColumn('modified_date', function ($row) {
                return date('Y-m-d', strtotime($row->updated_at));
	        })           

            // ->addColumn('approve', function ($row) {
            //     return '<button id="approve_req" type="button" class="btn btn-primary approve_req">Approve</button>';
            // })
            // ->addColumn('reject', function ($row) {
            //     return '<button id="denie_req" type="button" class="btn btn-danger denie_req">Deny</button>';
            // })

            //new tag button
            ->addColumn('composer_req_status', function ($row) {
                $composer = RequestStatus::find($row->request_status_id);

                if($composer){
                    if($composer->name == 'Pending'){
                        return '<label class="label label-lg label-light-warning label-inline">'.$composer->name.'</label>';
                    }elseif($composer->name == 'In Progress') {
                        return '<label class="label label-lg label-light-primary label-inline">'.$composer->name.'</label>';
                    }elseif($composer->name == 'Completed') {
                        return '<label class="label label-lg label-light-danger label-inline">'.$composer->name.'</label>';
                    }
                }


            //     /* old code */
            //     // return $composer ? $composer->name : '';
            //     // if($row->request_status_id == 2){
            //     //     $checked = 'checked';
            //     // }
            //     // else{
            //     //     $checked = '';
            //     // }   
            //     // return '<input id="edit_request_status" data-id="' . $row->id . '" name="user_status" class="toggle-class edit_request_status" type="checkbox" data-on="Active" data-off="Suspended"  data-toggle="toggle" data-offstyle="danger" ' . $checked . ' >';
                

            // //     if($row->composer_status_id == 2){
            // //         // $checked = 'checked';
            // //         return '<a href="#" class="request_approve"  data-id="' . $row->id.'"><i class="fas fa-check-circle" style="font-size: 16px; font-weight: 900; color:black"></i></a>';

            // //     }
            // //     elseif($row->composer_status_id == 4){
            // //         // $checked = '';
            // //         return '<a href="#" class="request_approve"  data-id="' . $row->id.'"><i class="fas fa-times-circle" style="font-size: 16px; font-weight: 900; color:red"></i></a>';

            // //     }
            // //     else {
            // //         return '<a href="#" class="request_approve"  data-id="' . $row->id.'"><i class="fas fa-check-circle" style="font-size: 16px; font-weight: 900; color:black"></i></a>
            // //         <a href="#" class="request_approve"  data-id="' . $row->id.'"><i class="fas fa-times-circle" style="font-size: 16px; font-weight: 900; color:red"></i></a>';
            // //     } 
               
            // //    return '<input id="edit_composer_status" data-id="' . $row->id . '" name="user_status" class="toggle-class edit_composer_status" type="checkbox" data-on="Active" data-off="Suspended"  data-toggle="toggle" data-offstyle="danger" ' . $checked . ' >';
            // /* end old code */ 
            })
            //end tag button

            ->addColumn('composer_status', function ($row) {

            //     /* new tag code */
                $composer = ComposerStatus::find($row->composer_status_id);
                if($composer){
                    if($composer->name == 'Pending'){
                        return '<label class="label label-lg label-light-warning label-inline">'.$composer->name.'</label>';
                    }elseif($composer->name == 'Active') {
                        return '<label class="label label-lg label-light-success label-inline">'.$composer->name.'</label>';
                    }elseif($composer->name == 'Rejected') {
                        return '<label class="label label-lg label-light-default label-inline">'.$composer->name.'</label>';
                    }elseif($composer->name == 'Suspended') {
                        return '<label class="label label-lg label-light-danger label-inline">'.$composer->name.'</label>';
                    }
                }


            //     /* old code */                
            //     // return $composer ? $composer->name : '';
            // //     if($row->composer_status_id == 2){
            // //         // $checked = 'checked';
            // //         return '<a href="#" class="request_approve"  data-id="' . $row->id.'"><i class="fas fa-check-circle" style="font-size: 16px; font-weight: 900; color:black"></i></a>';

            // //     }
            // //     elseif($row->composer_status_id == 4){
            // //         // $checked = '';
            // //         return '<a href="#" class="request_approve"  data-id="' . $row->id.'"><i class="fas fa-times-circle" style="font-size: 16px; font-weight: 900; color:red"></i></a>';

            // //     }
            // //     else {
            // //         return '<a href="#" class="request_approve"  data-id="' . $row->id.'"><i class="fas fa-check-circle" style="font-size: 16px; font-weight: 900; color:black"></i></a>
            // //         <a href="#" class="request_approve"  data-id="' . $row->id.'"><i class="fas fa-times-circle" style="font-size: 16px; font-weight: 900; color:red"></i></a>';
            // //     } 
               
            // //    return '<input id="edit_composer_status" data-id="' . $row->id . '" name="user_status" class="toggle-class edit_composer_status" type="checkbox" data-on="Active" data-off="Suspended"  data-toggle="toggle" data-offstyle="danger" ' . $checked . ' >';
                
            //    /* end old code */
            })
            /* end tag */


	        // ->addColumn('composer_status_id', function ($row) {
            //     return $row->composer_status_id;
	        // })
	        // ->addColumn('last_updated', function ($row) {
            //     return date('Y-m-d', strtotime($row->updated_at));
	        // })
            
            ->addColumn('action', function($row){
				$btn = '<a href ="#" data-id="'.$row->id.'" class="delete_request mx-3 "><i class="fas fa-trash fa-lg text-danger"></i></a>';                            	
                return $btn;

            })
	        ->rawColumns(['composer_request_id','composer_req_status','composer_status','composer_status','action'])
	        ->make(true);
	    }
	    else{
	      $users = Composer::oldest('id')->take(20)->get();
	      return view('admin.composer_request.index')->with('users',$users); 
	    }
        return view('admin.composer_request.index');
    }
    
    public function edit($id) {
    	$composer_request  = ComposerRequest::findOrFail($id);
        $composer = Composer::find($composer_request->composers_id);
        $composer_all_status = ComposerStatus::all();
        $request_all_status = RequestStatus::all();
        
    	return view('admin.composer_request.edit',compact('composer_request','composer','composer_all_status','request_all_status'));
    }    

    public function update(Request $request) {
        $id = $request->id;
        $composer_request  = ComposerRequest::findOrFail($id);
        
        // TODO: Solo para Development
        $notify = $request->notity;
        //
        // $notify = true;

        $request->validate([
            // 'public_name' => ['required', 'string', 'max:255'],
            'composer_status' => ['required'],
			'request_status' => ['required'],
            'reject_comment' => [
                'required_if:composer_status,3',
                'string',
                'nullable',
            ],
        ],
        [
            'reject_comment.required_if' => 'Please enter reason for reject composer request', 
        ]);
        $composer_id = ComposerRequest::find($id)->composers_id;
        $composer = Composer::find($composer_id); 
        if($composer_request->composer_status_id != 3){
            if($request->composer_status == 3){
                $comment = $request->reject_comment;
                if($request->request_status == 3){
                    if($notify) {
                        $composer->notify(new ComposerStatusNotify(['denied_reason'=>html_entity_decode($comment), 'composer_name'=> $composer->name,'user_name'=> auth()->user()->name ])); 
                    }
                }
                
            }else{
                $comment = null;
                if($request->composer_status == 2 && $request->request_status == 3){
                    if($notify) {
                        $composer->notify(new ComposerStatusNotify(['composer_name'=> $composer->name,'user_name'=> auth()->user()->name ])); 
                    }
                }
            }
        }else{
            if($request->composer_status == 3){                
                $comment = $request->reject_comment;
            }else{
                $comment = null;
                if($request->composer_status == 2 && $request->request_status == 3){
                    if($notify) {
                        $composer->notify(new ComposerStatusNotify(['composer_name'=> $composer->name,'user_name'=> auth()->user()->name ])); 
                    }
                }
            }
        }

        ComposerRequest::where('id', $id)->update([
             
            'composer_status_id' => $request->composer_status,
			'request_status_id' => $request->request_status,
			'comment' => $comment,
        ]);
        $composer_request  = ComposerRequest::findOrFail($id);
        self::update_composer_role($composer_request);
		return redirect()->route('composer_request.index')->with('success','Composer request updated successfully.'	);
    } 

    public function change_composer_status(Request $request) {
        $composer_request = ComposerRequest::find($request->id);
        if($request->status == 1){
            $status = 2;
        }
        else{
            $status = 4;
        }
        $composer_request->composer_status_id =  $status;
        $composer_request->save();
        self::update_composer_role($composer_request);
        return response()->json(['success'=>'Status change successfully.']);
    }

    public function destroy(Request $request) {
    	$id = $request->id;
        $composer_request = ComposerRequest::findOrFail($id);
        $composer = Composer::findOrFail($composer_request->composers_id);
        $composer->delete();
       	$composer_request->delete();
       	return response()->json(['success'=>'User Deleted successfully.']);
    }

    public function store_denied_reason(){

    }
}