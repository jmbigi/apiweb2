<?php

namespace App\Http\Controllers;

use App\Http\Requests\StyleMusic\StyleMusicRequest;
use App\Models\StyleMusic;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StyleMusicController extends Controller
{
    public function getAll(Request $request){
        $style_music = new StyleMusic();
        //filtros
        if($request->name){
            $style_music = $style_music->where('name','LIKE','%'.$request->name.'%');
        }
        //return
        return response()->json([
            'status' => true,
            'message' => 'Style Music',
            'data' => $style_music->active()->get()
        ], 200);
    }

    public function show(StyleMusic $style){
        if(($style->request && $style->approved)
            || (empty($style->request) && empty($style->approved))
        ){
            return response()->json([
                'status' => true,
                'message' => 'Style Music',
                'data' => $style
            ], 200);
        }else{
            abort(403, 'Forbiden resource');
        }
    }

    public function sugest(StyleMusicRequest $request){
        $style_music = new StyleMusic();
        $style_music->fill($request->all());
        $style_music->request = Carbon::now();
        $style_music->save();
        //retornar composer
        return response()->json([
            'status' => true,
            'message' => 'Saved suggested',
            'data' => $style_music
        ], 200);
    }
}
