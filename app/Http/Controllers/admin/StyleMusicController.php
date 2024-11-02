<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\StyleMusic;
use Illuminate\Http\Request;
use DataTables;
use Carbon\Carbon;

class StyleMusicController extends Controller
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
	    	
	    	$data = StyleMusic::query();
	    	
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
		            	$instance->orderBy('style_musics.name', $request->order[0]['dir']);
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

			return '<input id="edit_style_status" data-id="' . $row->id . '" data-status="' . route('change_style_music_status') . '" name="style_status" class="toggle-class edit_style_status" type="checkbox" data-on="Active" data-off="Suspended"  data-toggle="toggle" data-offstyle="danger" ' . $checked . ' >';


            })
			
			->addColumn('action', function($row){
				$btn = '<a href ="#" data-id="'.$row->id.'" class="delete_style mx-3 "><i class="fas fa-trash fa-lg text-danger"></i></a>';                            	
                return $btn;

            })

	        ->rawColumns(['index','status','action'])
	        ->make(true);
	    }
	    else{
	      $StyleMusics = StyleMusic::oldest('id')->take(20)->get();
	      return view('admin.music_style.index')->with('StyleMusics',$StyleMusics); 
	    }
        return view('admin.music_style.index');
    }

    public function create(){
        return view('admin.music_style.create');
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
        $style = StyleMusic::create([
			'name' => $request->name,
            'request' => Carbon::now(),
            'approved'=> $approved,
		]);
        return redirect()->route('style_music.index')->with('success','MUsic style created successfully.'	);
    }
    public function edit($id) {
        $style_music  = StyleMusic::findOrFail($id);
        return view('admin.music_style.edit',compact('style_music'));
    }

    public function update(Request $request) {
        $request->validate([
		    'name' => ['required', 'string', 'max:255'],
		]);
        if ($request->style_status == 'on') {
			$approved = Carbon::now();
		}else {
			$approved = null;
		}
        $id = $request->id;
        $style = StyleMusic::where('id',$id)->update([
			'name' => $request->name,
            'approved'=> $approved,
		]);
        return redirect()->route('style_music.index')->with('success','MUsic style updated successfully.'	);
    }
    public function change_style_music_status(Request $request) {
        $style = StyleMusic::find($request->id);
        if($request->status == 0){
            $status = null;
        }else{
            $status = Carbon::now();
        }
        $style->approved = $status;
        $style->save();

        return response()->json(['success'=>'Status change successfully.']);
    }
	public function destroy(Request $request) {
    	$id = $request->id;
        $style_music = StyleMusic::findOrFail($id);
        $style_music->delete();		
       	return response()->json(['success'=>'Style Music Deleted successfully.']);
    }
}
