<?php

namespace App\Http\Controllers\admin;

use App\Models\FilesS3;
use DataTables;
use App\Models\User;
use App\Models\Composer;
use App\Models\MusicScore;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LogDisplayMusicScore;

class MusicScoreController extends Controller
{
    public function index(Request $request)
    {
	    if ($request->ajax()) {
	    	$data = MusicScore::select('*');	    	
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
		            	$instance->orderBy('music_scores.name', $request->order[0]['dir']);
		            }
                    // if ($request->order[0]['column'] == 2) { 
		            // 	$instance->orderBy('users.name', $request->order[0]['dir']);
		            // }		            
		        }
	        })
            ->addColumn('index', function ($row) use (&$counter) {
                // return '<a href="#">' . $counter++ . '</a>';
				return $row->id;
            })
	        ->addColumn('name', function($row){                
	        	return $row->name;
	        })
	        
            
	        ->addColumn('composer', function ($row) {
                // $composer_name = User::findOrFail($row->owner_id)->name; 
                $composer_name = Composer::active()->where('users_id',$row->owner_id)->first();
	            return ($composer_name ?? collect()->isNotEmpty()) ? $composer_name->public_name : '';
	        })
	        ->addColumn('publish_date', function ($row) {
	            return $row->date;
	        })
            ->addColumn('total_view', function ($row) {
                $total_view = LogDisplayMusicScore::where('music_scores_id',$row->id)->count();
	            return $total_view;
	        })
			->addColumn('status', function ($row) {
                $checked = $row->status ? 'checked' : ''; 
				return '<input id="edit_music_score_status" data-id="' . $row->id . '" data-status="' . route('change_music_score_status') . '" name="edit_music_score_status" class="toggle-class edit_music_score_status" type="checkbox" data-on="Active" data-off="Suspended"  data-toggle="toggle" data-offstyle="danger" ' . $checked . ' >';
            })
            ->addColumn('action', function($row){
				$btn = '<a href ="#" data-id="'.$row->id.'" class="delete_music_score mx-3 "><i class="fas fa-trash fa-lg text-danger"></i></a>';                            	
                return $btn;
            })

	        ->rawColumns(['index','action','status'])
	        ->make(true);
	    }
	    else{
	      $music_scores = MusicScore::oldest('id')->take(20)->get();
	      return view('admin.music_score.index')->with('music_scores',$music_scores); 
	    }
        return view('admin.music_score.index');
    }

    public function show($id){
        $music_scores = MusicScore::find($id);
		$composer = Composer::active()->where('users_id',$music_scores->owner_id)->first();
		$composer_name = $composer ?? collect()->isNotEmpty() ? $composer->public_name : '';
		$composer_id = $composer ?? collect()->isNotEmpty() ? $composer->id : '';
        $total_view = LogDisplayMusicScore::where('music_scores_id',$id)->count();
		$pdf = FilesS3::where('fileable_id',$id)->where('extension','pdf')->value('path');
		
		if(!empty($pdf)){

			$fileInfo = pathinfo($pdf);
			$filename = $fileInfo['filename'];
			$extension = isset($fileInfo['extension']) ? $fileInfo['extension'] : '' ;
			$pdf_name = $filename.'.'.$extension;
		}else{
			$pdf_name = 'NA';
		}
		if(empty($extension)){
			$pdf_name = 'NA';
		}
		
		$style_of_music = $music_scores->style_musics->pluck('name')->toArray();
		$instrument_of_music = $music_scores->instruments->pluck('name')->toArray();
		$link_of_music = $music_scores->linksInfo->value('url');
    	return view('admin.music_score.show',compact('music_scores','total_view','composer_name','pdf_name','style_of_music','instrument_of_music','link_of_music','composer_id'));
    }
	public function change_music_score_status(Request $request) {
		// dd($request->id);
		$status_request = MusicScore::find($request->id);
        if($request->status == 1){
            $status = 1;
        }
        else{
            $status = 0;
        }
        $status_request->status =  $status;
        $status_request->save();
		// if($request->ajax()){
			return response()->json(['success'=>'Status change successfully.']);
		// }
		// else{
		// 	return redirect()->route('music_score.index')->with('success','Music score status updated successfully.'	);
		// }
	}
	public function music_score_status_update(Request $request) {
		$status_request = MusicScore::find($request->id);
        if($request->music_score_status == 'on'){
			$status = 1;
        }
        else{
			$status = 0;
        }
        $status_request->status =  $status;
        $status_request->save();
		return redirect()->route('music_score.index')->with('success','Music score status updated successfully.'	);
	}

	public function destroy(Request $request) {
    	$id = $request->id;
        $music_score = MusicScore::findOrFail($id);
        $music_score->delete();        
       	return response()->json(['success'=>'Music Score Deleted successfully.']);
    }
}
