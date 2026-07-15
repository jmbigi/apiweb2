<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Instrument;
use App\Models\FamilyInstruments;
use DataTables;
use Carbon\Carbon;

class InstrumentController extends Controller
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
	    	
	    	$data = Instrument::query();
	    	
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
		            	$instance->orderBy('instruments.name', $request->order[0]['dir']);
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

			// return '<input id="edit_user_status" data-id="' . $row->id . '" data-user="'.{{route('change_user_status')}}.'" name="user_status" class="toggle-class edit_user_status" type="checkbox" data-on="Active" data-off="Suspended"  data-toggle="toggle" data-offstyle="danger" ' . $checked . ' >';
			return '<input id="edit_instrument_status" data-id="' . $row->id . '" data-status="' . route('change_instrument_status') . '" name="edit_instrument_status" class="toggle-class edit_instrument_status" type="checkbox" data-on="Active" data-off="Suspended"  data-toggle="toggle" data-offstyle="danger" ' . $checked . ' >';


            })
			
			->addColumn('action', function($row){
				$btn = '<a href ="#" data-id="'.$row->id.'" class="delete_instrument mx-3 "><i class="fas fa-trash fa-lg text-danger"></i></a>';                            	
                return $btn;

            })

	        ->rawColumns(['index','status','action'])
	        ->make(true);
	    }
	    else{
	      $users = Instrument::oldest('id')->take(20)->get();
	      return view('admin.instrument.index')->with('users',$users); 
	    }
        return view('admin.instrument.index');
    }

    public function create(){
        $family_instruments = FamilyInstruments::all();
        return view('admin.instrument.create',compact('family_instruments'));
    }

    public function store(Request $request) {
        $request->validate([
		    'name' => ['required', 'string', 'max:255'],
		    'family_instrument' => ['required']
		]);
        if ($request->instrument_status == 'on') {
			$approved = Carbon::now();
		}else {
			$approved = null;
		}
        $user = Instrument::create([
			'name' => $request->name,
			'family_instruments_id' => $request->family_instrument,
            'request' => Carbon::now(),
            'approved'=> $approved,
		]);
		return redirect()->route('instrument.index')->with('success','Instrument created successfully.'	);
    }
    public function edit($id) {
        $instrument  = Instrument::findOrFail($id);
        $family_instruments = FamilyInstruments::all();
        return view('admin.instrument.edit',compact('instrument','family_instruments'));
    }
	public function update(Request $request) {
        $request->validate([
		    'name' => ['required', 'string', 'max:255'],
		    'family_instrument' => ['required']
		]);
        if ($request->instrument_status == 'on') {
			$approved = Carbon::now();
		}else {
			$approved = null;
		}
        $id = $request->id;
		// dd($request);
        $instrument = Instrument::where('id',$id)->update([
			'name' => $request->name,
			'family_instruments_id' => $request->family_instrument,
            'approved'=> $approved,
		]);
        return redirect()->route('instrument.index')->with('success','Instrument updated successfully.'	);
    }
    
	public function destroy(Request $request) {
    	$id = $request->id;
        $instrument = Instrument::findOrFail($id);
        $instrument->delete();		
       	return response()->json(['success'=>'Instrument Deleted successfully.']);
    }

	
	public function change_instrument_status(Request $request) {
		$instrument = Instrument::find($request->id);
		if($request->status == 0){
			$status = null;
		}else{
			$status = Carbon::now();
		}
		$instrument->approved = $status;
		$instrument->save();

		return response()->json(['success'=>'Instrument status change successfully.']);
		
	}
}
