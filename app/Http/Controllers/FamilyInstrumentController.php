<?php

namespace App\Http\Controllers;

use App\Http\Requests\Instruments\FamilyInstrumentsRequest;
use App\Models\FamilyInstruments;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FamilyInstrumentController extends Controller
{
    //
    public function getAll(Request $request){
        $family = new FamilyInstruments();
        //filtros
        if($request->name){
            $family = $family->where('name','LIKE','%'.$request->name.'%');
        }
        //retornar listado
        return response()->json([
            'status' => true,
            'message' => 'Family Instruments',
            'data' => $family->active()->get()
        ], 200);
    }

    public function show(FamilyInstruments $family){
        if(($family->request && $family->approved)
            || (empty($family->request) && empty($family->approved))
        ){
            return response()->json([
                'status' => true,
                'message' => 'Composer',
                'data' => $family
            ], 200);
        }else{
            abort(403, 'Forbiden resource');
        }
    }

    public function sugest(FamilyInstrumentsRequest $request){
        $family = new FamilyInstruments();
        $family->fill($request->all());
        $family->request = Carbon::now();
        $family->save();
        //retornar composer
        return response()->json([
            'status' => true,
            'message' => 'Saved suggested',
            'data' => $family
        ], 200);
    }
}
