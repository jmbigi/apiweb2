<?php

namespace App\Http\Controllers\admin;

use DataTables;
use App\Models\Composer;
use Illuminate\Http\Request;
use App\Models\ComposerStatus;
use App\Models\ComposerRequest;
use App\Http\Controllers\Controller;

class ComposerController extends Controller
{
    //
    public function index(Request $request)
    {
	    if ($request->ajax()) {
	    	$data = Composer::select('*');	    	
            $counter = 1; 
	      	return Datatables::of($data)->filter(function ($instance) use ($request) {
	        
	        	if ($request->has('search') && !is_null($request->get('search')['value'])) { 
	            	$regex = $request->get('search')['value'];
		            $instance->where(function($q) use($regex) {
		            	$q->where('public_name', 'like', '%' . $regex . '%');
		            });
	          	}
		        if ($request->has('order') && !empty($request->has('order') && $request->order[0]['column']!= 0)) {
		        	if ($request->order[0]['column'] == 1) { 
		            	$instance->orderBy('composers.public_name', $request->order[0]['dir']);
		            }		            
		        }
	        })
            ->addColumn('index', function ($row) use (&$counter) {
                // return '<a href="#">' . $counter++ . '</a>';
				return $row->id;
            })
	        ->addColumn('name', function($row){
	        	return $row->public_name;
	        })
	        
            
	        ->addColumn('register_date', function ($row) {
	            return $row->created_at;
	        })
	        ->addColumn('last_updated', function ($row) {
	            return $row->updated_at;
	        })

	        ->rawColumns(['index'])
	        ->make(true);
	    }
	    else{
	      $users = Composer::oldest('id')->take(20)->get();
	      return view('admin.composer.index')->with('users',$users); 
	    }
        return view('admin.composer.index');
    }
	public function show($id){
		$composer = Composer::find($id);
        $composer_all_status = ComposerStatus::all();
		$composer_request  = ComposerRequest::where('composers_id',$id)->first();
    	return view('admin.composer.show',compact('composer','composer_all_status','composer_request'));
    
	}
	public function update_composer_status(Request $request){
		$composer_status = ComposerRequest::where('composers_id',$request->id)->update([
			'composer_status_id' => $request->composer_status,
		]);
		return redirect()->route('composer.index')->with('success','Composer status updated successfully.'	);
	}
 }
