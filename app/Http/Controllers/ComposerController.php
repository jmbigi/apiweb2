<?php

namespace App\Http\Controllers;

use App\Http\Requests\Composer\CreateComposerRequest;
use App\Models\Composer;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Models\ComposerRequest;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class ComposerController extends Controller
{
    //
    public function getAll(Request $request){
        $composer = new Composer();
        //filtros
        if($request->name){
            $composer = $composer->where('public_name','LIKE','%'.$request->name.'%');
        }
        //retornar listado
        return response()->json([
            'status' => true,
            'message' => 'Composer',
            'data' => $composer->active()->get()
        ], 200);
    }

    public function show(Composer $composer){
        if(($composer->request && $composer->approved)
            || (empty($composer->request) && empty($composer->approved))
        ){
            return response()->json([
                'status' => true,
                'message' => 'Composer',
                'data' => $composer
            ], 200);
        }else{
            abort(403, 'Forbiden resource');
        }
    }

    // public function sugest(CreateComposerRequest $request){
    //     $composer = new Composer();
    //     $composer->fill($request->all());
    //     $composer->request = Carbon::now();
    //     $composer->save();
    //     //retornar composer
    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Saved suggested',
    //         'data' => $composer
    //     ], 200);
    // }


    public function create(ComposerRequest $request,Request $req) {
        $user  = User::findOrFail($req->user_id);


        $validateRequest = Validator::make($req->all(), 
        [
            'name' => 'required',
            'surname' => 'required',
            'vat_number' => 'required|unique:composers',
            'public_name' => 'required',   
            'street' => 'required',   
            'city' => 'required',   
            'postal_code' => 'required',   
            'country' => 'required',   
            'notification_email' => 'email',
            'telephone' => 'required', 
        ]);

        if($validateRequest->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateRequest->errors()
            ], 401);
        }
        // if($req->notification_email){
        //     $notificationEmail = $req->notification_email;
        //     $composer_mail = Composer::whereHas('composerRequest', function ($query) use ($notificationEmail) {
        //         $query->where('composer_status_id', 2)
        //             ->where('notification_email', $notificationEmail);
        //     })
        //     ->first();
        // }
        // if ($composer_mail) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'The email has already been taken.',
        //     ], 401);
        // }    
        // $user_mail = User::where('email', $req->notification_email)->where('id','!=',$req->user_id)->first();
        // if($user_mail){
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'The email has already been taken.',
        //     ], 401);
        // }
        // if($req->telephone){
        //     // $composer_telephone = Composer::where('telephone', $req->telephone)->where('composer_status_id','!=',2)->where('composer_status_id','!=',3)->first();
        //     $telephone = $req->telephone;
        //     $composer_telephone = Composer::whereHas('composerRequest', function ($query) use ($telephone) {
        //         $query->where('composer_status_id', 2)
        //             ->where('telephone', $telephone);
        //     })
        //     ->first();
        // }
        // if (isset($composer_telephone)) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'The telephone has already been taken.',
        //     ], 401);
        // }    
        // if($req->telephone){
        //     $user_telephone = User::where('telephone', $req->telephone)->where('id','!=',$req->user_id)->first();
        // }
        // if(isset($user_telephone)){
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'The telephone has already been taken.',
        //     ], 401);
        // }

        // Checking composer role before composer creation
        // TODO: Verificar si se debe realizar esta validacion
        if($user->hasRole('composer')){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => ['user_id', 'The user is already a composer.']
            ], 401);
        }

        $composer = Composer::create([
            'users_id' => $req->user_id,
            'name' => $req->name,
            'surname' => $req->surname,
            'vat_number' => $req->vat_number,
            'public_name' => $req->public_name,
            'street' => $req->street,
            'city' => $req->city,
            'postal_code' => $req->postal_code,
            'country' => $req->country,
            'notification_email' => $req->notification_email ?? $user->email ?? null,
            'telephone' => $req->telephone ?? $user->telephone ?? null,
        ]);
        return response()->json([
            'status' => true,
            'message' => 'Composer Request Created',
            'data' => $composer
        ], 200);
    }

    public function update(ComposerRequest $request,Request $req){
        $user  = User::findOrFail($req->user_id);
        $composer_id = $req->id;
       

        $validateRequest = Validator::make($req->all(), 
        [
            'name' => 'required',
            'surname' => 'required',
            'vat_number' => 'required|unique:composers,vat_number,' . $composer_id,
            'public_name' => 'required',   
            'street' => 'required',   
            'city' => 'required',   
            'postal_code' => 'required',   
            'country' => 'required',   
            'notification_email' => 'required|email',
            'telephone' => 'required', 
        ]);

        if($validateRequest->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateRequest->errors()
            ], 401);
        }

        // if($req->notification_email){
        //     $notificationEmail = $req->notification_email;
        //     $composer_mail = Composer::whereHas('composerRequest', function ($query) use ($notificationEmail) {
        //         $query->where('composer_status_id', 2)
        //             ->where('notification_email', $notificationEmail);
        //     })
        //     ->first();
        // }
        // if ($composer_mail) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'The email has already been taken.',
        //     ], 401);
        // }    
        // if($req->telephone){
        //     $telephone = $req->telephone;
        //     $composer_telephone = Composer::whereHas('composerRequest', function ($query) use ($telephone) {
        //         $query->where('composer_status_id', 2)
        //             ->where('telephone', $telephone);
        //     })
        //     ->first();
        // }
        // if ($composer_telephone) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'The telephone has already been taken.',
        //     ], 401);
        // }    
       

        $composer = Composer::where('id',$composer_id)->update([
            'users_id' => $req->user_id,
            'name' => $req->name,
            'surname' => $req->surname,
            'vat_number' => $req->vat_number,
            'public_name' => $req->public_name,
            'street' => $req->street,
            'city' => $req->city,
            'postal_code' => $req->postal_code,
            'country' => $req->country,
            'notification_email' => $req->notification_email,
            'telephone' => $req->telephone,
            
        ]);
        return response()->json([
            'status' => true,
            'message' => 'Composer Updated',
            'data' => $composer
        ], 200);

    }

    public function delete($id){
        $composer = Composer::findOrFail($id);
        $composer->delete();       
        return response()->json([
            'status' => true,
            'message' => 'Composer Deleted',
            'data' => $composer
        ], 200);
                 
    }   
}
