<?php

namespace App\Http\Controllers;

use App\Http\Requests\Instruments\InstrumentRequest;
use App\Models\FamilyInstruments;
use App\Models\Instrument;
use App\Models\StyleMusic;
use Carbon\Carbon;
use Illuminate\Http\Request;
use NunoMaduro\Collision\Adapters\Laravel\Inspector;
use Illuminate\Support\Facades\Validator;

//TODO: el with hacía arriba (hacía family) funciona solo en los 5 primeros registros
// luego retorna null
class InstrumentController extends Controller
{
    //
    public function getAll(Request $request){
        $instruments = new Instrument();
        //filtros
        if($request->name){
            $instruments = $instruments->where('name','LIKE','%'.$request->name.'%');
        }
        if($request->family_instruments_id){
            $instruments = $instruments->where('family_instruments_id',$request->family_instruments_id);
        }
        //return
        return response()->json([
            'status' => true,
            'message' => 'Instrument',
            'data' => $instruments->active()->with('family')->get()
        ], 200);
    }

    public function show(Instrument $instrument){
        if(($instrument->request && $instrument->approved)
            || (empty($instrument->request) && empty($instrument->approved))
        ){
            return response()->json([
                'status' => true,
                'message' => 'Instrument',
                'data' => $instrument->load('family')
            ], 200);
        }else{
            abort(403, 'Forbiden resource');
        }
    }

    public function sugest(InstrumentRequest $request){
        $instrument = new Instrument();
        $instrument->fill($request->all());
        $instrument->request = Carbon::now();
        $instrument->save();
        //retornar composer
        return response()->json([
            'status' => true,
            'message' => 'Saved suggested',
            'data' => $instrument
        ], 200);
    }

    public function create(Request $request) {
        $validateRequest = Validator::make($request->all(), [
            'instrument_name' => 'unique:instruments,name',
            'style_name' => 'unique:style_musics,name',
        ]);

        if($validateRequest->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateRequest->errors()
            ], 401);
        }
        if(empty($request->instrument_name) && empty($request->style_name)){            
            $validateRequest = Validator::make($request->all(), [
                'instrument_name' => 'required',
                'style_name' => 'required',
            ]);     
            if($validateRequest->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateRequest->errors()
                ], 401);
            }     
        }
        $instrument_success = false;
        if($request->instrument_name) {
            $family_instrument_id = FamilyInstruments::where('name','Not categorized')->value('id');
            Instrument::firstOrCreate([
                'name' => $request->instrument_name,
                'family_instruments_id' => $family_instrument_id,
                'request' => Carbon::now(),
            ]);
            $instrument_success = 'Instrument saved successfully';
        }
        $style_success = false;
        if($request->style_name){
            StyleMusic::firstOrCreate([
                'name' => $request->style_name,
                'request' => Carbon::now(),
            ]);
            $style_success = 'Music Style saved successfully';
        }
        if($instrument_success && $style_success){
            return response()->json([
                'status' => true,
                'message' => 'instrument and music style saved successfully'
                    ], 200); 
        }
        else if(!empty($instrument_success)){
            return response()->json([
                'status' => true,
                'message' => 'Instrument saved successfully'
                    ], 200); 
            
        }
        else if(!empty($style_success)){
            return response()->json([
                'status' => true,
                'message' => 'Music style saved successfully'
                    ], 200); 
            
        }
    }
}
