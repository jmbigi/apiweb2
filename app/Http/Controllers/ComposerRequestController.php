<?php
//Crear controlador para CRUD en tabla ComposerRequest

namespace App\Http\Controllers;

use App\Http\Requests\ComposerRequests\CreateComposerRequestRequest;
use App\Models\ComposerRequest;
use App\Models\Composer;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PharIo\Manifest\CopyrightElement;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\Role;

class ComposerRequestController extends Controller
{
    public static $COMPOSER_ROLE = "composer";

    protected static function update_composer_role(ComposerRequest $composer_request) {
        $composer_role = Role::where('name', self::$COMPOSER_ROLE)->firstOrFail();
        $comp_req_user = $composer_request->composer->user;
        if($composer_request->composer_status_id == 2  && !$comp_req_user->hasRole($composer_role)) {
            $comp_req_user->attachRole($composer_role);
        } elseif ($composer_request->composer_status_id != 2 && $comp_req_user->hasRole($composer_role)) {
            $comp_req_user->detachRole($composer_role);
        }        
    }

    public function getList(){
        $composerRequests = new ComposerRequest();
       
        //return list of composerRequests from database
        $composerRequests = ComposerRequest::with('composer','composer_status','request_status')->get();        

        return response()->json([
            'status' => true,
            'message' => 'ComposerRequest',
            'data' => $composerRequests
        ], 200);
    }

    public function get(request $request){
        //return composerRequest from database
        $composerRequest = ComposerRequest::with('composer','composer_status','request_status')->findOrFail($request->get);

        return response()->json([
            'status' => true,
            'message' => 'ComposerRequest',
            'data' => $composerRequest
        ], 200);
    }

    /* Create a funcion to create a new composerRequest */
    public function create(ComposerRequest $request,Request $req){
        $user  = User::findOrFail($req->user_id);


        $validateRequest = Validator::make($req->all(), 
        [
            // 'description' => 'required',
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

        // Checking composer role (not present) before composer request creation
        if($user->hasRole('composer')){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => ['user_id', 'The user is already a composer.']
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
        //     $telephone = $req->telephone;
        //     $composer_telephone = Composer::whereHas('composerRequest', function ($query) use ($telephone) {
        //         $query->where('composer_status_id', 2)
        //             ->where('telephone', $telephone);
        //     })
        //     ->first();
        // }
        // // $composer_telephone = Composer::where('telephone', $req->telephone)->where('composer_status_id','!=',2)->where('composer_status_id','!=',3)->first();
        // if ($composer_telephone) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'The telephone has already been taken.',
        //     ], 401);
        // }    
        // $user_telephone = User::where('telephone', $req->telephone)->where('id','!=',$req->user_id)->first();
        // if($user_telephone){
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'The telephone has already been taken.',
        //     ], 401);
        // }


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
        
        $composerRequest = new ComposerRequest();

        $composerRequest->fill($request->all()->toArray());
        $composerRequest->composers_id = $composer->id;
        $composerRequest->description = $req->description ?? 'NA';
        $composerRequest->request_status_id = 1;
        $composerRequest->composer_status_id = 1;
        $composerRequest->request_date = Carbon::now();
        $composerRequest->updated_at = Carbon::now();
        $composerRequest->save();
        self::update_composer_role($composerRequest);
        return response()->json([
            'status' => true,
            'message' => 'Composer Request Created',
            'data' => $composerRequest
        ], 200);





        // $composerRequest = new ComposerRequest();
        // $composerRequest->fill($request->all()->toArray());
        // $composerRequest->request_status_id = 1;
        // $composerRequest->composer_status_id = 1;
        // $composerRequest->request_date = Carbon::now();
        // $composerRequest->updated_at = Carbon::now();
        // $composerRequest->save();
        // return response()->json([
        //     'status' => true,
        //     'message' => 'ComposerRequest created',
        //     'data' => $composerRequest
        // ], 200);
    }
    
    /* Create a funcion to update a composerRequest by id in $request*/
    public function update(ComposerRequest $request,Request $req){
        $user  = User::findOrFail($req->user_id);
        $id = $req->id;
        $composer_id =  ComposerRequest::findOrFail($id)->composers_id;

        $validateRequest = Validator::make($req->all(), 
        [
            // 'description' => 'required',
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


        // $composer_mail = Composer::where('notification_email', $req->notification_email)->where('id','!=',$composer_id)->where('composer_status_id','!=',2)->where('composer_status_id','!=',3)->first();
        // if($req->notification_email){
        //     $notificationEmail = $req->notification_email;
        //     $composer_mail = Composer::whereHas('composerRequest', function ($query) use ($notificationEmail) {
        //         $query->where('composer_status_id', 2)
        //             ->where('notification_email', $notificationEmail);
        //     })
        //     ->first();
        // }
        // if ($composer_mail) {
        //     // return response()->json(['message' => 'The email has already been taken.']);
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'The email has already been taken.',
        //     ], 401);
        // }    
        // $user_mail = User::where('email', $req->notification_email)->where('id','!=',$req->user_id)->first();
        // if($user_mail){
        //     return response()->json(['message' => 'The email has already been taken.']);
        // }
        // $composer_telephone = Composer::where('telephone', $req->telephone)->where('id','!=',$composer_id)->where('composer_status_id','!=',2)->where('composer_status_id','!=',3)->first();
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
        //     // return response()->json(['message' => 'The telephone has already been taken.']);
        // }    
        // $user_telephone = User::where('telephone', $req->telephone)->where('id','!=',$req->user_id)->first();
        // if($user_telephone){
        //     return response()->json(['message' => 'The telephone has already been taken.']);
        // }


        $composerRequest = ComposerRequest::findOrFail($id);
        // $composerRequest->fill($req->all());
        $composerRequest->fill($request->all()->toArray());
        $composerRequest->description = $req->description ?? 'NA';
        $composerRequest->save();

        

         
        Composer::where('id',$composerRequest->composers_id)->update([
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
        $composerRequest = ComposerRequest::findOrFail($id);
        self::update_composer_role($composerRequest);

        return response()->json([
            'status' => true,
            'message' => 'Composer Request Updated',
            'data' => $composerRequest
        ], 200);
        
        
        
        
        
        
        // $id = $request->input('id');
        // $composerRequest = ComposerRequest::findOrFail($id);
        // $composerRequest->fill($request->all());
        // $composerRequest->save();
        // return response()->json([
        //     'status' => true,
        //     'message' => 'ComposerRequest updated',
        //     'data' => $composerRequest
        // ], 200);
    }

    /* Create a funcion to delete a composerRequest by $request->id */ 
    public function delete($id){
        $composerRequest = ComposerRequest::findOrFail($id);
        $composerRequest->delete();       
        return response()->json([
            'status' => true,
            'message' => 'Composer Request Deleted',
            'data' => $composerRequest
        ], 200);
         
        
        // return response()->json(null, 204);
    }   
    public function update_status(Request $request,$id){
        $composerRequest = ComposerRequest::findOrFail($id);
        $composerRequest->fill($request->all());
        $composerRequest->save();
        self::update_composer_role($composerRequest);
        return response()->json([
            'status' => true,
            'message' => 'Composer Status Updated',
            'data' => $composerRequest
        ], 200);
    } 
};