<?php

namespace App\Http\Controllers;
use App\Models\MusicScore;
use App\Models\User;
use App\Models\ComposerRequest;
use App\Models\Composer;
use App\Models\ComposerStatus;
use App\Models\RequestStatus;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use DataTables;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SupportEmail;

class SupportController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.support');    
    }

    public function sendEmail(Request $request)
    {
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'message' => $request->message
        ];

        // Aquí puedes personalizar el destinatario y el asunto del correo electrónico según tus necesidades
        Mail::to('f.bolo@biblioscores.com')->send(new SupportEmail($data));

        return redirect()->back()->with('success', 'Your message has been sent successfully.');
    }    

}
