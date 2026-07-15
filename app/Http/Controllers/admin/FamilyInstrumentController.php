<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DataTables;
use Carbon\Carbon;
use App\Models\FamilyInstruments;

class FamilyInstrumentController extends Controller
{
    public function __construct()
    {
       $this->middleware('auth');
    }
    public function index(Request $request)
    {
	    if ($request->ajax()) {
	    	// $data = User::select('*');
	    	// $user = auth()->user();
	    	
	    	$data = FamilyInstruments::query();
	    	
            $counter = 1; 
	      	return Datatables::of($data)->filter(function ($instance) use ($request) {
	        
	        	if ($request->has('search') && !is_null($request->get('search')['value'])) { 
	            	$regex = $request->get('search')['value'];
		            $instance->where(function($q) use($regex) {
		            	$q->where('name', 'like', '%' . $regex . '%');
		            });
	          	}
		        if ($request->has('order') && !empty($request->has('order') && $request->order[0]['column']!= 0)) {
		        	if ($request->order[0]['column'] == 1) { 
		            	$instance->orderBy('family_instruments.name', $request->order[0]['dir']);
		            }
					
		        }
	        })
            ->addColumn('index', function ($row) use (&$counter) {
                return '<a href="">' . $counter++ . '</a>';
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
                $checked = $row->approved ? 'checked' : ''; 
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

			return '<input id="edit_family_instrument_status" data-id="' . $row->id . '" data-status="' . route('change_family_instrument_status') . '" name="family_instrument_status" class="toggle-class edit_family_instrument_status" type="checkbox" data-on="Active" data-off="Suspended"  data-toggle="toggle" data-offstyle="danger" ' . $checked . ' >';


            })
			
			->addColumn('action', function($row){
				$btn = '<a href ="#" data-id="'.$row->id.'" class="delete_family_instrument mx-3 "><i class="fas fa-trash fa-lg text-danger"></i></a>';                            	
                return $btn;

            })

	        ->rawColumns(['index','status','action'])
	        ->make(true);
	    }
	    else{
	      $family_instrument = FamilyInstruments::oldest('id')->take(20)->get();
	      return view('admin.family_instrument.index')->with('family_instrument',$family_instrument); 
	    }
        return view('admin.family_instrument.index');
    }

    public function create(){
        return view('admin.family_instrument.create');
    }

    public function store(Request $request) {
        $request->validate([
		    'name' => ['required', 'string', 'max:255'],
		]);
        if ($request->style_status == 'on') {
			$approved = Carbon::now();
		}else {
			$approved = null;
		}
        $style = FamilyInstruments::create([
			'name' => $request->name,
            'request' => Carbon::now(),
            'approved'=> $approved,
		]);
        return redirect()->route('family_instruments.index')->with('success','Family Instrument created successfully.'	);
    }
    public function edit($id) {
        $family_instrument  = FamilyInstruments::findOrFail($id);
        return view('admin.family_instrument.edit',compact('family_instrument'));
    }

    public function update(Request $request) {
        $request->validate([
		    'name' => ['required', 'string', 'max:255'],
		]);
        if ($request->family_instrument == 'on') {
			$approved = Carbon::now();
		}else {
			$approved = null;
		}
        $id = $request->id;
        $family_instrument = FamilyInstruments::where('id',$id)->update([
			'name' => $request->name,
            'approved'=> $approved,
		]);
        return redirect()->route('family_instruments.index')->with('success','Family Instrument updated successfully.'	);
    }
    public function change_family_instrument_status(Request $request) {
        $family_instrument = FamilyInstruments::find($request->id);
        if($request->status == 0){
            $status = null;
        }else{
            $status = Carbon::now();
        }
        $family_instrument->approved = $status;
        $family_instrument->save();

        return response()->json(['success'=>'Family instrument status change successfully.']);
    }
	public function destroy(Request $request) {
    	$id = $request->id;
        $family_instrument = FamilyInstruments::findOrFail($id);
        $family_instrument->delete();		
       	return response()->json(['success'=>'Family Instrument Deleted successfully.']);
    }
}
