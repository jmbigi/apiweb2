<?php

namespace App\Http\Controllers\api;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Mail\sendEmail;
use App\Models\Composer;
use Illuminate\Http\Request;
use App\Models\SubscribedUser;
use App\Events\RegisteredEvent;
use App\Models\ComposerRequest;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Requests\EmailVerificationOutSessionRequest;
use App\Events\UserRegisteredEvent;
use App\Services\SubscriptionService;

use Illuminate\Support\Str;

//TODO: hace falta refactorizar a lo loco. La mitad de lo que esta en este fichero va fuera o en otras capas.

//TODO: falta solicitar nueva contraseña 
//TODO: --> se autogenera una contraseña larga y jodida --> y se manda por email

//TODO: (2) --> hacer el envio del correo para cambiar la contraseña bien
class AuthController extends Controller
{
    public static $DEFAULT_ROLE = 'musician';


    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Chequea las credenciales y retorna un token
     */
    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            $user = User::where('email', $request->email)->firstOrFail();

            // if(!$user->hasVerifiedEmail()){
            //     abort(401,'not actived user');
            // }
            if ($user->status == 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your account has been suspended'
                ], 401);
            }
            $data = auth()->user()->composer;
            if (!empty($data)) {
                $composerRequest = $data->composerRequest()
                    ->where('composer_status_id', 2)
                    ->where('request_status_id', 3)
                    ->first();

                if (empty($composerRequest)) {
                    $data = [];
                } else {
                    $data = collect($data)->toArray();
                    if (!empty($data['deleted_at'])) {
                        $data['deleted_at'] = Carbon::parse($data['deleted_at'])->format('Y-m-d H:i:s'); // Format as desired
                    } else {
                        $data['deleted_at'] = ''; // Set to empty string if it's null
                    }
                }
            }
            $subscribed_user = $user->subscriptions()->where('user_id', $user->id)->first();
            // Retrieve subscription plan once
            $subscription_plan = null;
            $subscription_plan_name = null;
            if ($subscribed_user) {
                $subscription_plan = SubscriptionPlan::find($subscribed_user->subscription_plan_id);
                $subscription_plan_name = $subscription_plan ? $subscription_plan->name : null;
            }

            $subscription_service = new SubscriptionService();
            $subscription_data = $subscription_service->getSubscriptionDetails();

            $request->user()->tokens()->delete();

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'user_id' => auth()->user()->id,
                'user_name' => auth()->user()->name,
                'token' => $user->createToken("API TOKEN")->plainTextToken,
                'pdf_password' => env('PDF_USER_PASSWORD'),
                'composer' => !empty($data) ? $data : '',
                'subscription_type' => $subscription_plan_name,
                'subscribed_user' => $subscribed_user,
                'subscription_plan' => $subscription_plan,
                'subscription_data' => $subscription_data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Destruye todos los tokens asignados al usuario
     */
    public function logoutUser(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'User Logged Out Successfully',
        ], 200);
    }

    /**
     * Genera un nuevo token y elimina los anteriores
     */
    public function refreshToken(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'User Logged In Successfully',
            'token' => $request->user()->createToken("API TOKEN")->plainTextToken
        ], 200);
    }

    /**
     * Create User
     * @param Request $request
     * @return Response json con el estatus y un pequeño mensaje informativo 
     * TODO: falta añadir el campo telefono como required en la creación
     */
    public function createUser(Request $request)
    {
        DB::beginTransaction();
        try {
            //Validated
            $validateUser = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'email_verified_at' => Carbon::now()->toTimeString()
            ]);
            $subscription_plan = SubscriptionPlan::where('name', 'Free')->orWhere('name', 'free')->first();
            $endDate = Carbon::now()->addMonth();
            SubscribedUser::create([
                'user_id' => $user->id,
                'subscription_plan_id' => $subscription_plan->id ?? null
            ]);

            $user->attachRole(Role::where('name', self::$DEFAULT_ROLE)->firstOrFail());

            // $this->sendEmailVerify($user, false);

            UserRegisteredEvent::dispatch($user);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
                'user_id' => $user->id,
                'user_name' => $user->name,
                'token' => $user->createToken("API TOKEN")->plainTextToken,
                'pdf_password' => env('PDF_USER_PASSWORD'),

            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function resendEmailVerify(Request $request)
    {
        $email = $request->email;
        if (empty($email)) {
            abort(500, 'bad data');
        }
        $user = User::where('email', $email)->firstOrFail();

        return $this->sendEmailVerify($user);
    }

    /**
     * Manda el email de verificación
     * @param User $user - usuario que va a recibir la información
     * @param Boolean $checkActivateUser - solo manda el email si el usuario no esta verificado 
     */
    protected function sendEmailVerify(User $user, $checkActivateUser = true)
    {
        //comprobamos 
        if ($checkActivateUser && $user->hasVerifiedEmail()) {
            abort(403, 'actived user not send email verify');
        }

        //lanzamos el email de verificación
        RegisteredEvent::dispatch($user);

        return response()->json([
            'status' => true,
            'message' => 'Email sended',
        ], 200);
    }

    public function activateUser(EmailVerificationOutSessionRequest $request, User $user)
    {
        //https://laracasts.com/discuss/channels/laravel/email-verification-with-api-and-laravel-sanctum
        //https://dev.to/codeanddeploy/how-to-implement-laravel-8-email-verification-492h
        //https://laravel.com/docs/10.x/verification#the-email-verification-handler
        $user = User::findOrFail($request->id);

        if ($user->hasVerifiedEmail()) {
            abort(403, 'Actived user. Not allow activate again');
        }

        if ($user->markEmailAsVerified()) {
            if ($request->header('Accept') == 'application/json') {
                return response()->json([
                    'status' => true,
                    'message' => 'User Verified',
                ], 200);
            } else {
                dd('Ahora puedes acceder a la app');
                //TODO: retornar una vista que indique que ahora pueden loguearse
            }
        }
    }

    public function askForBeComposer(Request $request)
    {
        //TODO: falta poder activar el rol desde Faristol
        //$request->user()->attachRole(Role::where('name','composer')->firstOrFail());
    }

    public function updateUser(Request $request, $id)
    {


        //OJO: si cambian el email volver a mandar validación
        //y volver a cambiar el estado de confirmación a null
        //OJO: el NIF lo cambia el personal de Faristol manualmente, no se recogerá en esta petición 
        //(se marginará a proposito del request)
        // $id = $request->id;
        DB::beginTransaction();
        try {
            //Validated
            $validateUser = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    // 'email' => 'required|email|unique:users,email',
                    'email' => 'required|email|unique:users,email,' . $id,
                    // 'password' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = User::where('id', $id)->update([
                'name' => $request->name,
                'email' => $request->email,
                // 'password' => Hash::make($request->password),
            ]);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'User updated Successfully',
                'data' => $user,

            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function getUser(Request $request, $id = null)
    {
        if ($id) {
            $user = User::select('name', 'email', 'id', 'status')
                ->with('composer', 'subscriptions')->findOrFail($id);
        } else {
            $user =  $request->user();
        }

        $data = auth()->user()->composer;
        if (!empty($data)) {
            $composerRequest = $data->composerRequest()
                ->where('composer_status_id', 2)
                ->where('request_status_id', 3)
                ->first();

            if (empty($composerRequest)) {
                $data = [];
            } else {
                $data = collect($data)->toArray();
                if (!empty($data['deleted_at'])) {
                    $data['deleted_at'] = Carbon::parse($data['deleted_at'])->format('Y-m-d H:i:s'); // Format as desired
                } else {
                    $data['deleted_at'] = ''; // Set to empty string if it's null
                }
            }
        }
        $subscribed_user = $user->subscriptions()->where('user_id', $user->id)->first();
        // Retrieve subscription plan once
        $subscription_plan = null;
        $subscription_plan_name = null;
        if ($subscribed_user) {
            $subscription_plan = SubscriptionPlan::find($subscribed_user->subscription_plan_id);
            $subscription_plan_name = $subscription_plan ? $subscription_plan->name : null;
        }

        $subscription_service = new SubscriptionService();
        $subscription_data = $subscription_service->getSubscriptionDetails();

        return response()->json([
            'status' => true,
            'message' => 'UserRequest',
            'data' => $user,
            'composer' => !empty($data) ? $data : '',
            'subscription_type' => $subscription_plan_name,
            'subscribed_user' => $subscribed_user,
            'subscription_plan' => $subscription_plan,
            'subscription_data' => $subscription_data,
        ], 200);
    }

    public function delete($id)
    {
        return self::renameUserForDeletion($id);
        /*
        $user = User::select('name', 'email', 'id', 'status')->findOrFail($id);
        $composer_id = Composer::where('users_id', $id);
        if (empty($composer_id)) {
            $user->forceDelete();
        } else {
            $user->delete();
        }
        return response()->json([
            'status' => true,
            'message' => 'User Deleted',
            'data' => $user
        ], 200);
        */
    }

    function renameUserForDeletion($id)
    {
        // Retrieve the user by ID and ensure it exists
        $user = User::select('name', 'email', 'id', 'status')->findOrFail($id);

        // Check if the user has an associated composer record
        $composer_user = $user->composer;
        if ($composer_user) {
            // Generate a unique identifier
            $orderedUuid = Str::orderedUuid();
            // Rename the VAT number to indicate deletion
            $composer_user->vat_number = 'deleted_' . $composer_user->vat_number . '_' . $orderedUuid;
            // Save changes to the composer user
            $composer_user->save();
            // Delete the composer user record
            $composer_user->delete();
        }

        // Generate a unique identifier for the user email
        $orderedUuid = Str::orderedUuid();
        // Rename the email to indicate deletion
        $user->email = 'deleted_' . $user->email . '_' . $orderedUuid;
        // Save changes to the user
        $user->save();
        // Delete the user record
        $user->delete();

        // Return a JSON response indicating success
        return response()->json([
            'status' => true,
            'message' => 'User deleted',
            'data' => $user
        ], 200);
    }

    public function requestOtp(Request $request)
    {

        $otp = rand(1000, 9999);
        Log::info("otp = " . $otp);
        $user = User::where('email', '=', $request->email)->update(['otp' => $otp]);

        if ($user) {
            // send otp in the email
            $mail_details = [
                'subject' => 'Testing Application OTP',
                'body' => 'Your OTP is : ' . $otp
            ];

            Mail::to($request->email)->send(new sendEmail($mail_details));

            return response(["status" => 200, "message" => "OTP sent successfully"]);
        } else {
            return response(["status" => 401, 'message' => 'Invalid']);
        }
    }


    public function verifyOtp(Request $request)
    {

        $user = User::where([['email', '=', $request->email], ['otp', '=', $request->otp]])->first();
        if ($user) {
            auth()->login($user, true);
            User::where('email', '=', $request->email)->update(['otp' => null]);
            $accessToken = auth()->user()->createToken('authToken')->accessToken;
            $data = [
                'name' => auth()->user()->name, // Assuming the user model has a 'name' attribute
                'email' => auth()->user()->email, // Assuming the user model has an 'email' attribute
            ];


            return response(["status" => 200, "message" => "Success", 'user' => $data]);
        } else {
            return response(["status" => 401, 'message' => 'Invalid']);
        }
    }

    public function setNewPasword(Request $request)
    {
        $validateUser = Validator::make(
            $request->all(),
            [
                'email' => 'required',
                'password' => 'required',
                'confirm_password' => 'required|same:password',
            ]
        );

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $user = User::where('email', $request->email)->update([
            'password' => Hash::make($request->password),
        ]);
        return response()->json([
            'status' => true,
            'message' => 'Password changed successfully',
            'data' => $user
        ], 200);
    }

    public function checkComposer()
    {
        $data = auth()->user()->composer;
        if (!empty($data)) {
            $composerRequest = $data->composerRequest()
                ->where('composer_status_id', 2)
                ->where('request_status_id', 3)
                ->first();

            if (empty($composerRequest)) {
                $composer = false;
            } else {
                $composer = true;
            }
            return response()->json([
                'status' => $composer,
                'data' => $composer
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'data' => false
            ], 200);
        }
    }

    public function checkSubscription()
    {
        $subscriptionDetails = $this->subscriptionService->getSubscriptionDetails();
        return response()->json([
            'status' => true,
        ] + $subscriptionDetails, 200);
    }
}
