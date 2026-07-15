<?php

namespace App\Http\Controllers;

use App\Http\Requests\MusicScore\CreateScoreRequest;
use App\Http\Requests\MusicScore\EditScoreRequest;
use App\Models\FamilyInstruments;
use App\Models\FilesS3;
use App\Models\FkMusicScoreInstrument;
use App\Models\FkMusicScoreStyle;
use App\Models\Instrument;
use App\Models\MusicScore;
use App\Models\StyleMusic;
use App\Models\LogDisplayMusicScore;
use App\Models\LogViewMusicScoreDetail;
use App\Models\LogDisplayPersonalScore;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Validator;

use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\PostTooLargeException;

use App\Models\Composer;
use App\Models\User;
use App\Models\LinkInfo;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
// use Spatie\PdfToImage\Pdf;
// use Spatie\PdfToText\Pdf;

use mikehaertl\pdftk\Pdf;
use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;

use DB;

use function PHPUnit\Framework\isEmpty;

use Laravel\Sanctum\PersonalAccessToken;

//TODO: hay que crear todos los requests pertinentes

class MusicScoreController extends Controller
{
    protected $pathMusicScores = 'musicScores/pdf';
    protected $pathCoverMusicScores = 'musicScores/cover';

    protected $maxKilobitesPDF = 100 * 1000;
    protected $maxKilobitesImage = 100 * 1000;

    //Crea la función getlist para devolver la lista de partituras
    public function getList(Request $request)
    {
        $scores = new MusicScore();


        //return list of musicScores from database
        $scores = MusicScore::with('composers', 'instruments', 'style_musics')->get();
        $scores->transform(function ($score) {
            if (is_null($score->date)) {
                $score->date = "";
            }
            if (is_null($score->description)) {
                $score->description = "";
            }
            if (is_null($score->created_at)) {
                $score->created_at = "";
            }
            if (is_null($score->updated_at)) {
                $score->updated_at = "";
            }

            return $score;
        });


        return response()->json([
            'status' => true,
            'message' => 'MusicScores Retrived',
            'data' => $scores
        ], 200);
    }

    public function getListFiltered(Request $request)
    {

        // $filter = $request->route('filter');
        // $id = $request->route('id');

        // switch($filter){
        //     case 1:
        //         //Get musicScores classified by Instrument filtered by StyleMusic
        //         dd('case1');
        //         $styleMusic = Instrument::with(['music_scores' => function ($query) use ($id) {
        //             $query->whereHas('style_musics', function ($query) use ($id) {
        //                 $query->where('style_musics.id', $id);
        //             })->select('music_scores.id', 'music_scores.name')->take(21);
        //         }])->select('instruments.id', 'instruments.name')->get();
        //         break;
        //     case 2:
        //         dd('case2');
        //         //Get musicScores classified by Instruments filtered by FamilyInstruments
        //         $styleMusic = Instrument::where('family_instruments_id', $id)
        //             ->with(['music_scores' => function ($query) {
        //             $query->select('music_scores.id', 'music_scores.name')->take(21);
        //         }])
        //         ->select('instruments.id', 'instruments.name')
        //         ->get();

        //         break;
        //     case 3:
        //         dd('case3');
        //         //Get musicScores classified by StyleMusic filtered by Instruments
        //         $styleMusic = StyleMusic::with(['music_scores' => function ($query) use ($id) {
        //             $query->whereHas('instruments', function ($query) use ($id) {
        //                 $query->where('instruments.id', $id);
        //             })->select('music_scores.id', 'music_scores.name')->take(21);
        //         }])->select('style_musics.id', 'style_musics.name')->get();
        //         break;
        //     default:
        //     dd('case4');
        //         //Get musicScores classified by StyleMusic
        //         $styleMusic = StyleMusic::with(['music_scores' => function ($query) {
        //             $query->select('music_scores.id', 'music_scores.name', 'music_scores.description', 'music_scores.owner_id')->take(21);
        //         }])->select('style_musics.id', 'style_musics.name')->get();
        //         break;
        // }

        // return response()->json([
        //     'status' => true,
        //     'message' => 'StyleMusic',
        //     'data' => $styleMusic
        // ], 200);

        //  dd($request->name);
        $query = MusicScore::query();

        // Apply filters as needed
        // if ($filter) {
        //     $query->where('name', 'like', '%' . $filter . '%');
        // }
        if ($request->name) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // If you have additional filter criteria like music styles, instruments, and year,
        // you can add them here using request parameters.
        // /list-filtered?music_styles=1,2&instruments=3,4&year=2023
        if ($request->has('music_styles')) {
            $musicStyles = explode(',', $request->input('music_styles'));
            $query->whereIn('id', function ($subquery) use ($musicStyles) {
                $subquery->select('music_scores_id')
                    ->from('fk_music_score_style_music')
                    ->whereIn('style_musics_id', $musicStyles);
            });
        }

        if ($request->has('instruments')) {
            $instruments = explode(',', $request->input('instruments'));
            // $query->whereIn('instrument_id', $instruments);
            $query->whereIn('id', function ($subquery) use ($instruments) {
                $subquery->select('music_scores_id')
                    ->from('fk_music_score_instrument')
                    ->whereIn('instruments_id', $instruments);
            });
        }

        if ($request->has('family_instruments')) {
            $instruments = explode(',', $request->input('family_instruments'));

            $query->whereIn('id', function ($subquery) use ($instruments) {
                $subquery->select('music_scores_id')
                    ->from('fk_music_score_instrument')
                    ->whereIn('instruments_id', function ($subquery) use ($instruments) {
                        $subquery->select('id')
                            ->from('instruments')
                            ->whereIn('family_instruments_id', $instruments);
                    });
            });
        }

        if ($request->has('year')) {
            $year = $request->input('year');
            $query->whereYear('date', $year);
        }
        if ($request->has('composer')) {
            $composer_ids = Composer::where('public_name', 'like', '%' . $request->composer . '%')->pluck('users_id')->toArray();
            $composer_ids = array_unique($composer_ids);
            $query->whereIn('owner_id', $composer_ids);
        }

        // Execute the query
        $musicScores = $query->active()->get();
        $musicScores->transform(function ($score) {
            if (is_null($score->date)) {
                $score->date = "";
            }
            if (is_null($score->description)) {
                $score->description = "";
            }
            if (is_null($score->created_at)) {
                $score->created_at = "";
            }
            if (is_null($score->updated_at)) {
                $score->updated_at = "";
            }

            return $score;
        });

        if (!empty($musicScores->toArray())) {
            // Return the filtered results
            return response()->json([
                'status' => true,
                'message' => 'MusicScore Retrived',
                'data' => $musicScores
            ], 200);
        } else {

            return response()->json([
                'status' => true,
                'message' => 'MusicScore not found',
            ], 200);
        }
    }

    //Crea la función get para devolver una partitura
    public function get(Request $request)
    {
        //return musicScore from database
        $exist_data = MusicScore::where('id', $request->get)->first();
        if (empty($exist_data)) {
            return response()->json([
                'status' => false,
                'message' => 'Music score not found',
            ], 200);
        }
        $score = MusicScore::with('composers', 'instruments', 'style_musics', 'files', 'linksInfo')->findOrFail($request->get);
        $score->date = Carbon::parse($score->date)->format('j F Y');
        $score->formatDate = Carbon::parse($score->date)->format('d-m-Y');
        if (!$score->date || $score->date == null) {
            $score->date = "";
        }
        if (!$score->description || $score->description == null) {
            $score->description = "";
        }
        if (!$score->created_at || $score->created_at == null) {
            $score->created_at = "";
        }
        if (!$score->updated_at || $score->updated_at == null) {
            $score->updated_at = "";
        }
        $score = $score->toArray();
        collect($score['links_info'])->each(function ($item) {
            if (empty($item['url'])) {
                $item['url'] = "";
            }
            if (empty($item['social_network'])) {
                $item['social_network'] = "";
            }
        });

        $this->saveDetailLog($request, $request->get);

        return response()->json([
            'status' => true,
            'message' => 'MusicScore Retrived',
            'data' => [$score]
        ], 200);
    }


    public function showMonetizationScore(Request $request, MusicScore $score)
    {
        //middleware permiso estará ya en la ruta

        //hay que comprobar el owned antes de mostrar la información
    }

    public function showInfoScore(Request $request, MusicScore $score)
    {
        //TODO: si en el request esta el parametro de devolver como "owner" se comprueba que 
    }

    //investigando: https://www.itsolutionstuff.com/post/how-to-add-password-protection-for-pdf-file-in-laravelexample.html


    //investigando: https://wasabi-support.zendesk.com/hc/en-us/articles/360035684991-How-do-I-use-Laravel-with-Wasabi-
    public function create(Request $request)
    {
        \Log::info('Create');
        \Log::info('==================================================');
        \Log::info(json_encode($request->all()));
        \Log::info('==================================================');
        // $request_instruments = explode(',', trim($request->instrument_id, '[]'));
        // $request_instruments = array_map('trim', $request_instruments);
        // $request->merge(['instrument_id' => $request_instruments]);

        $request->merge(['instrument_id' => json_decode($request->instrument_id, true)]);
        $request->merge(['composer_id' => json_decode($request->composer_id, true)]);




        // $request_style_id = explode(',', trim($request->style_id, '[]'));
        // $request_instruments = array_map('trim', $request_style_id);
        // $request->merge(['style_id' => $request_style_id]);
        $request->merge(['style_id' => json_decode($request->style_id, true)]);

        if ($request->new_instrument) {
            // $request_newInstruments = explode(',', trim($request->new_instrument, '[]'));
            // $request_instruments = array_map('trim', $request_newInstruments);
            // $request->merge(['new_instrument' => $request_newInstruments]);
            $request->merge(['new_instrument' => json_decode($request->new_instrument, true)]);
        }
        if ($request->new_style) {
            // $request_newStyle = explode(',', trim($request->new_style, '[]'));
            // $request_instruments = array_map('trim', $request_newStyle);
            // $request->merge(['new_style' => $request_newStyle]);
            $request->merge(['new_style' => json_decode($request->new_style, true)]);
        }

        $validateRequest = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|unique:music_scores',
                'pdf' => [
                    'required',
                    File::types(['pdf'])
                        ->max($this->maxKilobitesPDF),
                ],
                'cover' => [

                    'nullable',
                    File::types(['png', 'jpg', 'jpeg'])
                        ->max($this->maxKilobitesImage),
                ],

                // 'owner_id' => 'required|integer',
                'composer_id' => [
                    'nullable',
                    function ($attribute, $value, $fail) {
                        is_array(json_decode($value)) || is_integer(json_decode($value)) ?: $fail("Must be array or integer");
                    },
                    'exists:composers,id'
                ],
                'instrument_id' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        is_array($value) || is_integer($value) ?: $fail("Must be array or integer");
                    },
                    // 'exists:instruments,name'
                ],
                'style_id' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        is_array($value) || is_integer($value) ?: $fail("Must be array or integer");
                    },
                    // 'exists:style_musics,id'
                ],
                'links' => 'url',
                'new_instrument' => [
                    function ($attribute, $value, $fail) {
                        is_array($value) ?: $fail("Must be array or integer");
                    },
                    Rule::unique('instruments', 'name'),
                ],
                'new_style' => [
                    function ($attribute, $value, $fail) {
                        is_array($value) ?: $fail("Must be array or integer");
                    },
                    Rule::unique('style_musics', 'name'),
                ],
            ],
            [
                'cover.max' => 'The cover image must be less than 100 mb.',
                'pdf.max' => 'The pdf must be less than 100 mb.',
            ]
        );

        if ($validateRequest->fails()) {
            \Log::error('Create');
            // Loguear los errores detallados del validador
            \Log::error('Validation errors: ', $validateRequest->errors()->toArray());
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateRequest->errors()
            ], 401);
        }

        $score = new MusicScore();
        $score->fill($request->all());
        $score->owner()->associate($request->user());
        $score->save();

        $req_instruments = is_array($request->instrument_id) ? $request->instrument_id : [$request->instrument_id];

        $instrumentIds = [];
        // $existingInstrumentNames = Instrument::whereIn('name', $request->instrument_id)->pluck('name')->toArray();
        $existingInstrumentNames = Instrument::where(function ($query) use ($req_instruments) {
            foreach ($req_instruments as $instrumentName) {
                $query->orWhere('name', 'LIKE', $instrumentName);
            }
        })->pluck('name')->toArray();
        $existingInstrumentNames = array_merge($existingInstrumentNames, $req_instruments);

        $existingInstrumentIds = Instrument::whereIn('name', $req_instruments)->pluck('id')->toArray();
        foreach ($req_instruments as $instrumentName) {
            if (!in_array($instrumentName, $existingInstrumentNames, true)) {

                $family_instrument = FamilyInstruments::where('name', 'Not categorized')->first();
                $instrument = Instrument::create([
                    'name' => $instrumentName,
                    'family_instruments_id' => $family_instrument->id,
                    'request' => Carbon::now(),
                ]);
                $newInstrumentIds[] = $instrument->id;
            }
        }
        // dd( $newInstrumentIds);
        if (isset($newInstrumentIds)) {
            $instrumentIds = array_merge($existingInstrumentIds, $newInstrumentIds);
        } else {
            $instrumentIds = $existingInstrumentIds;
        }
        if ($request->new_instrument) {
            $family_instrument = FamilyInstruments::where('name', 'Not categorized')->first();
            foreach ($request->new_instrument as $new_instrument) {
                $newInstrument = Instrument::firstOrCreate([
                    'name' => $new_instrument,
                    'family_instruments_id' => $family_instrument->id,
                    'request' => Carbon::now(),
                ]);
            }
        }

        // collect($request->instrument_id)->each(function($item) use(&$instrumentIds){
        //     if($item){
        //         dd($existingInstrumentIds);
        //         $family_instrument = FamilyInstruments::where('name','Not categorized')->first();                
        //         if (!in_array($item, $existingInstrumentIds)) {
        //             $instrument = Instrument::create([
        //                 'name' => $item,
        //                  'family_instruments_id' => $family_instrument->id,
        //             ]);
        //             $newInstrumentIds[] = $instrument->id;
        //         }
        //         $instrumentIds[] = $instrument->id;
        //         // $instrumentIds[] = $item;
        //     }else{
        //         $family_instrument = FamilyInstruments::where('name','Not categorized')->first();

        //         $newInstrument = Instrument::Create([
        //             'name' => $item,
        //             'family_instruments_id' => $family_instrument->id,
        //         ]);
        //         $instrumentIds[] = $newInstrument->id;                
        //     }
        // });



        // $mergedInstruments = array_merge($request->instrument_id, $instrumentIds);    
        //$mergedInstruments = array_filter($mergedInstruments);
        $score->instruments()->sync($instrumentIds);

        $req_styles = is_array($request->style_id) ? $request->style_id : [$request->style_id];

        $StyleIds = [];
        $existingStyleNames = StyleMusic::whereIn('name', $req_styles)->pluck('name')->toArray();
        $existingStyleIds = StyleMusic::whereIn('name', $req_styles)->pluck('id')->toArray();
        foreach ($req_styles as $styleName) {
            if (!in_array($styleName, $existingStyleNames)) {

                $style = StyleMusic::create([
                    'name' => $styleName,
                    'request' => Carbon::now(),
                ]);
                $newStyleIds[] = $style->id;
            }
        }
        // dd( $newInstrumentIds);
        if (isset($newStyleIds)) {
            $StyleIds = array_merge($existingStyleIds, $newStyleIds);
        } else {
            $StyleIds = $existingStyleIds;
        }
        if ($request->new_style) {
            foreach ($request->new_style as $new_style) {
                $newStyle = StyleMusic::firstOrCreate([
                    'name' => $new_style,
                    'request' => Carbon::now(),
                ]);
            }
        }
        $score->style_musics()->sync($StyleIds);

        // if($request->new_style){
        //     $newStyleIds = [];
        //     foreach($request->new_style as $new_style){
        //         $newStyle = StyleMusic::Create([
        //           'name' => $new_style,
        //         ]);
        //         $newStyleIds[] = $newStyle->id;
        //     }
        //     $mergedStyleIds = array_merge($request->style_id, $newStyleIds);    
        //     $mergedStyleIds = array_filter($mergedStyleIds);
        //     $score->style_musics()->sync($mergedStyleIds);
        // }else{
        //     $score->style_musics()->sync($request->style_id); 
        // }


        if ($request->composer_id) {
            $score->composers()->sync($request->composer_id);
        }



        $linkdata = ['url' => $request->links, 'social_network' => $request->social_network];
        $score->linksInfo()->create($linkdata);

        //Subir ficheros al S3 (sin contraseñas)



        $uploadedPdfFile = $request->file('pdf');
        $realNamePDFFile = date('U') . "_" . str_replace(' ', '_', $request->file('pdf')->getClientOriginalName());

        $disk = 'public';
        Storage::disk($disk)->put('music_score/' . $realNamePDFFile, file_get_contents($uploadedPdfFile));
        $pdfPassword = env('PDF_ADMIN_PASSWORD');
        $userPassword = env('PDF_USER_PASSWORD');
        $filePath = storage_path('app/public/music_score/' . $realNamePDFFile);
        // $pdf = new Pdf($filePath);  

        // $result = $pdf->allow('AllFeatures')
        // ->setPassword($pdfPassword)
        // ->setUserPassword($userPassword)
        // ->passwordEncryption(128)
        // ->saveAs($filePath);

        $protected_file = file_get_contents($filePath);
        Storage::disk('Wasabi')->put($this->pathMusicScores . '/' . $realNamePDFFile, $protected_file);
        $filedata = ['path' => $this->pathMusicScores . '/' . $realNamePDFFile, 'storagePlace' => 'Wasabisys', 'extension' => $uploadedPdfFile->getClientOriginalExtension()];
        $score->files()->create($filedata);
        unlink($filePath);

        if ($request->file('cover')) {
            $uploadCover = $request->file('cover');
            $realNameCoverFile = date('U') . "_" . str_replace(' ', '_', $request->file('cover')->getClientOriginalName());
            $pathCover = $request->cover->storeAs(
                $this->pathCoverMusicScores,
                $realNameCoverFile,
                'Wasabi'
            );
            $coverdata = ['path' => $this->pathCoverMusicScores . '/' . $realNameCoverFile, 'storagePlace' => 'Wasabisys', 'extension' => $uploadCover->getClientOriginalExtension()];
            $score->files()->create($coverdata);
        }
        if (!$score->id_card || !$score->owner->id_card) {
            $score->id_card = "";
            $score->owner->id_card = "";
        }
        if (!$score->telephone || !$score->owner->telephone) {
            $score->telephone = "";
            $score->owner->telephone = "";
        }
        if (!$score->composer_request || !$score->owner->composer_request) {
            $score->composer_request = "";
            $score->owner->composer_request = "";
        }
        if (!$score->composer_aproved || !$score->owner->composer_aproved) {
            $score->composer_aproved = "";
            $score->owner->composer_aproved = "";
        }
        if (!$score->deleted_at || !$score->owner->deleted_at) {
            $score->deleted_at = "";
            $score->owner->deleted_at = "";
        }
        if (!$score->owner->otp) {
            $score->owner->otp = "";
        }
        return response()->json([
            'status' => true,
            'message' => 'Music Score Created',
            'data' => $score
        ], 200);

        //start transaction
        //almacenar las url's del S3 junto con los valores del request en musicScores

        //almacenar las referencias del musicScore

        //retonrar información y commit

    }


    public function editScore(EditScoreRequest $request, MusicScore $score)
    {
        //el middleware esta en la ruta

        //se comprueba el owned para que no editen desde otros perfiles

        //se almacenan los nuevos cambios

        //se comprueba que no venga fichero 
        //(si viene fichero eliminamos el viejo del S3 y volvemos a subir)
    }

    /**
     * V2 -- ahora ni caso
     */
    public function notesScore(Request $request, MusicScore $score) {}

    public function getAllPDF($id)
    {
        $files = FilesS3::where('fileable_id', $id)->get();
        if (empty($files->toArray())) {
            return response()->json([
                'status' => true,
                'message' => 'PDF files not found',
            ], 200);
        }
        $modifiedFiles = $files->map(function ($file) {
            $file['path'] = env('WAS_ENDPOINT') + env('WAS_BUCKET') + '/' . $file['path'];
            return $file;
        });
        return response()->json([
            'status' => true,
            'message' => 'PDF Files Retrived',
            'data' => $modifiedFiles
        ], 200);
    }

    protected function saveDisplayLog($scoreId)
    {
        $user_id = auth()->user()->id;
        $log = new LogDisplayMusicScore();
        $log->user()->associate($user_id);
        $log->music_score()->associate($scoreId);
        $log->save();
    }

    public function checkToken(Request $request)
    {
        // Obtener el token del encabezado 'Authorization'
        $token = $request->bearerToken();

        if ($token) {
            try {
                // Intentar verificar el token
                $token = PersonalAccessToken::findToken($token);

                if ($token && $token->tokenable) {
                    // El token es válido y tiene un usuario asociado
                    return $token->tokenable;
                }
            } catch (\Exception $e) {
                // Ocurre un error al intentar decodificar el token, no es válido
                return null;
            }
        }

        // Si no hay token válido, el usuario es invitado
        return null;
    }

    protected function saveDetailLog($request, $scoreId)
    {
        try {
            // Obtener el usuario asociado al token
            $user = $this->checkToken($request);

            // Si se obtuvo un usuario válido y tiene un ID, asociarlo al log
            if ($user && isset($user->id)) {
                $log = new LogViewMusicScoreDetail();
                $log->user()->associate($user->id);
            } else {
                // Si no se obtuvo un usuario, no asociar el campo user
                $log = new LogViewMusicScoreDetail();
            }

            // Asociar la puntuación de la música
            $log->musicScore()->associate($scoreId);
            $log->save();
        } catch (\Exception $e) {
            \Log::error("Error al guardar el log de detalles: " . $e->getMessage());
            // Aquí podrías manejar más específicamente el error según sea necesario
        }
    }

    protected function saveDisplayPersonalLog(Request $request)
    {
        try {
            $userId = $request->input('userId');
            $filename = $request->input('filename');

            // Validar que filename esté presente
            if (empty($filename)) {
                \Log::warning('saveDisplayPersonalLog: filename is required');
                return false;
            }

            // Si userId es 0, lo convertimos a null
            if ($userId === 0 || $userId === '0') {
                $userId = null;
            }

            $log = new LogDisplayPersonalScore();
            $log->user_id = $userId;
            $log->filename = $filename;
            $log->save();

            return true;
        } catch (\Exception $e) {
            \Log::error("Error al guardar el log personal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Endpoint público para registrar visualización de archivos personales
     */
    public function logPersonalFileView(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'userId' => 'nullable|integer|min:0',
            'filename' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        $success = $this->saveDisplayPersonalLog($request);

        if ($success) {
            return response()->json([
                'status' => true,
                'message' => 'Personal file view logged successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Failed to log personal file view',
            ], 500);
        }
    }

    public function getPdfContent(Request $request)
    {
        //
        $scoreId = $request->scoreId;
        self::saveDisplayLog($scoreId);

        $password = $request->password;
        if ($password == env('PDF_USER_PASSWORD')) {
            $credentials = [
                'key'    => env('WAS_ACCESS_KEY_ID'),
                'secret' => env('WAS_SECRET_ACCESS_KEY'),
            ];

            $s3Config = [
                'version'     => 'latest',
                'region'      => env('WAS_DEFAULT_REGION'), // Change to your desired AWS region
                'endpoint'    => env('WAS_ENDPOINT'), // Replace with your manual endpoint URL
                'credentials' => $credentials,
            ];

            $filedata = FilesS3::findOrFail($request->id);
            // Initialize the S3 client
            $s3 = new S3Client($s3Config);

            $bucketName = env('WAS_BUCKET'); // Replace with your S3 bucket name
            $objectKey = $filedata->path; // Replace with the object (file) key

            try {
                // Get the file from S3
                $result = $s3->getObject([
                    'Bucket' => $bucketName,
                    'Key'    => $objectKey,
                ]);

                // The file contents are in $result['Body']
                $fileContents = $result['Body']->getContents();
                $base64EncodedContent = base64_encode($fileContents);

                // Do something with the file contents
                return response()->json([
                    'status' => true,
                    'message' => 'PDF Content Retrived',
                    'data' => $base64EncodedContent
                ], 200);
            } catch (\Exception $e) {
                // TODO: Confirmar si el formato de la respuesta es correcto.
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage()
                ], 403);
                // dd($e->getMessage());
                // Handle any errors that occur during the request
                // echo 'Error: ' . $e->getMessage();
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Please enter valid password.'
            ], 403);
        }
    }

    public function composerMusic(Request $request)
    {
        $score = MusicScore::where('owner_id', $request->input('id'));

        if ($request->has('name')) {
            $score->where('name', 'like', '%' . $request->input('name') . '%');
        }

        // Order by name or date
        if ($request->has('order')) {
            $order = $request->input('order');
            $type = $request->input('type');
            if ($order === 'name') {
                $score->orderBy('name', $type);
            } elseif ($order === 'date') {
                $score->orderBy('date', $type);
            }
        }
        $musicScores = $score->active()->get();
        $musicScores->transform(function ($item) {
            $item->date = Carbon::parse($item->date)->format('j F Y');
            if (is_null($item->date)) {
                $item->date = ''; // Set an empty string
            }
            if (is_null($item->description)) {
                $item->description = ''; // Set an empty string
            }
            if (is_null($item->created_at)) {
                $item->created_at = ''; // Set an empty string
            }
            if (is_null($item->updated_at)) {
                $item->updated_at = ''; // Set an empty string
            }
            return $item;
        });
        if (empty($musicScores->toArray())) {
            return response()->json([
                'status' => true,
                'message' => 'Composer`s music scores not found',
            ], 200);
        }
        return response()->json([
            'status' => true,
            'message' => 'Composer`s Music Scores Retrived',
            'data' => $musicScores
        ], 200);
    }


    public function update(Request $request)
    {
        \Log::info(json_encode($request->all()));
        //$request->merge(['instrument_id' => json_decode($request->instrument_id, true)]);
        //$request->merge(['composer_id' => json_decode($request->composer_id, true)]);

        $request->merge(['instrument_id' => json_decode($request->instrument_id)]);
        $request->merge(['style_id' => json_decode($request->style_id)]);


        $request->merge(['composer_id' => json_decode($request->composer_id, true)]);

        if ($request->new_instrument) {
            $request->merge(['new_instrument' => json_decode($request->new_instrument, true)]);
        }
        if ($request->new_style) {
            $request->merge(['new_style' => json_decode($request->new_style, true)]);
        }

        $validateRequest = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|unique:music_scores,name,' . $request->id,
                'pdf' => [
                    'nullable',
                    File::types(['pdf'])
                        ->max($this->maxKilobitesPDF),
                ],
                'cover' => [

                    'nullable',
                    File::types(['png', 'jpg', 'jpeg'])
                        ->max($this->maxKilobitesImage),
                ],
                'composer_id' => [
                    'nullable',
                    function ($attribute, $value, $fail) {
                        is_array(json_decode($value)) || is_integer(json_decode($value)) ?: $fail("Must be array or integer");
                    },
                    'exists:composers,id'
                ],
                'instrument_id' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        is_array($value) || is_integer($value) ?: $fail("Must be array or integer");
                    },
                ],
                'style_id' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        is_array($value) || is_integer($value) ?: $fail("Must be array or integer");
                    },
                ],
                'links' => 'url',
                'new_instrument' => [
                    function ($attribute, $value, $fail) {
                        is_array($value) ?: $fail("Must be array or integer");
                    },
                    Rule::unique('instruments', 'name'),
                ],
                'new_style' => [
                    function ($attribute, $value, $fail) {
                        is_array($value) ?: $fail("Must be array or integer");
                    },
                    Rule::unique('style_musics', 'name'),
                ],
            ],
            [
                'cover.max' => 'The cover image must be less than 100 mb.',
                'pdf.max' => 'The pdf must be less than 100 mb.',
            ]
        );

        if ($validateRequest->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateRequest->errors()
            ], 401);
        }

        $score = MusicScore::find($request->id);
        $score->fill($request->all());
        $score->owner()->associate($request->user());
        $score->save();

        $instrumentIds = [];
        $existingInstrumentNames = Instrument::where(function ($query) use ($request) {
            foreach ($request->instrument_id as $instrumentName) {
                $query->orWhere('name', 'LIKE', $instrumentName);
            }
        })->pluck('name')->toArray();
        // $existingInstrumentNames = array_merge($existingInstrumentNames, $request->instrument_id);

        $existingInstrumentIds = Instrument::whereIn('name', $request->instrument_id)->pluck('id')->toArray();
        foreach ($request->instrument_id as $instrumentName) {
            if (!in_array($instrumentName, $existingInstrumentNames, true)) {
                $family_instrument = FamilyInstruments::where('name', 'Not categorized')->first();
                $instrument = Instrument::create([
                    'name' => $instrumentName,
                    'family_instruments_id' => $family_instrument->id,
                    'request' => Carbon::now(),
                ]);
                $newInstrumentIds[] = $instrument->id;
            }
        }
        // dd($newInstrumentIds);
        if (isset($newInstrumentIds)) {
            $instrumentIds = array_merge($existingInstrumentIds, $newInstrumentIds);
        } else {
            $instrumentIds = $existingInstrumentIds;
        }
        if ($request->new_instrument) {
            $family_instrument = FamilyInstruments::where('name', 'Not categorized')->first();
            foreach ($request->new_instrument as $new_instrument) {
                $newInstrument = Instrument::firstOrCreate([
                    'name' => $new_instrument,
                    'family_instruments_id' => $family_instrument->id,
                    'request' => Carbon::now(),
                ]);
            }
        }

        $score->instruments()->sync($instrumentIds);

        $StyleIds = [];
        $existingStyleNames = StyleMusic::whereIn('name', $request->style_id)->pluck('name')->toArray();
        $existingStyleIds = StyleMusic::whereIn('name', $request->style_id)->pluck('id')->toArray();
        foreach ($request->style_id as $styleName) {
            if (!in_array($styleName, $existingStyleNames)) {

                $style = StyleMusic::create([
                    'name' => $styleName,
                    'request' => Carbon::now(),
                ]);
                $newStyleIds[] = $style->id;
            }
        }
        if (isset($newStyleIds)) {
            $StyleIds = array_merge($existingStyleIds, $newStyleIds);
        } else {
            $StyleIds = $existingStyleIds;
        }
        if ($request->new_style) {
            foreach ($request->new_style as $new_style) {
                $newStyle = StyleMusic::firstOrCreate([
                    'name' => $new_style,
                    'request' => Carbon::now(),
                ]);
            }
        }
        $score->style_musics()->sync($StyleIds);

        if ($request->composer_id) {
            $score->composers()->sync($request->composer_id);
        }

        $linkInfo = $score->linksInfo;

        if ($linkInfo) {
            // Update the record's attributes
            foreach ($linkInfo as $link) {
                $link->update([
                    'url' => $request->links,
                    'social_network' => $request->social_network,
                ]);
            }

            // Save the changes
            // $linkInfo->save();
        } else {
            $score->linksInfo()->create([
                'url' => $request->links,
                'social_network' => $request->social_network,
            ]);
        }

        $credentials = [
            'key'    => env('WAS_ACCESS_KEY_ID'),
            'secret' => env('WAS_SECRET_ACCESS_KEY'),
        ];

        $s3Config = [
            'version'     => 'latest',
            'region'      => 'eu-west-2', // Change to your desired AWS region
            'endpoint'    =>  env('WAS_ENDPOINT'), // Replace with your manual endpoint URL
            'credentials' => $credentials,
        ];
        $s3 = new S3Client($s3Config);

        if (!empty($request->file('pdf'))) {
            $filedata = FilesS3::where('fileable_id', $request->id)->where('extension', 'pdf')->first();
            // Initialize the S3 client
            $newpdf = $this->pathMusicScores . '/' . $request->file('pdf')->getClientOriginalName();


            $bucketName = env('WAS_BUCKET');
            $objectKey = $filedata->path;
            if ($objectKey != $newpdf) {
                $uploadedPdfFile = $request->file('pdf');
                $realNamePDFFile = date('U') . "_" . str_replace(' ', '_', $request->file('pdf')->getClientOriginalName());

                $disk = 'public';
                Storage::disk($disk)->put('music_score/' . $realNamePDFFile, file_get_contents($uploadedPdfFile));
                $filePath = storage_path('app/public/music_score/' . $realNamePDFFile);

                $protected_file = file_get_contents($filePath);
                Storage::disk('Wasabi')->put($this->pathMusicScores . '/' . $realNamePDFFile, $protected_file);
                // $filedata = ['path' => $this->pathMusicScores.'/'.$realNamePDFFile, 'storagePlace' => 'Wasabisys', 'extension' => $uploadedPdfFile->getClientOriginalExtension()];
                // $score->files()->create($filedata);
                $file = $score->files->first(); // Assuming there is only one associated file, adjust if needed

                if ($file) {
                    // Update the file's attributes
                    $file->update([
                        'path' => $this->pathMusicScores . '/' . $realNamePDFFile,
                        'storagePlace' => 'Wasabisys',
                        'extension' => $uploadedPdfFile->getClientOriginalExtension(),
                    ]);
                    // Save the changes
                    $file->save();
                    $s3->deleteObject([
                        'Bucket' => $bucketName,
                        'Key'    => $objectKey,
                    ]);
                }
                unlink($filePath);
            }
        }

        if (!empty($request->file('cover'))) {
            $fileExtensions = ['png', 'jpg', 'jpeg'];
            $filedata = FilesS3::where('fileable_id', $request->id)
                ->whereIn('extension', $fileExtensions)
                ->first();
            $newcover = $this->pathCoverMusicScores . '/' . $request->file('cover')->getClientOriginalName();


            $bucketName = env('WAS_BUCKET');
            $objectKey = $filedata->path;
            if ($objectKey != $newcover) {
                $uploadedCoverFile = $request->file('cover');
                $realNameCoverFile = date('U') . "_" . str_replace(' ', '_', $request->file('cover')->getClientOriginalName());

                $pathCover = $request->cover->storeAs(
                    $this->pathCoverMusicScores,
                    $realNameCoverFile,
                    'Wasabi'
                );

                $filedata->update([
                    'path' => $this->pathCoverMusicScores . '/' . $realNameCoverFile,
                    'storagePlace' => 'Wasabisys',
                    'extension' => $uploadedCoverFile->getClientOriginalExtension(),
                ]);
                // Save the changes 1696510755_1.jpg 1696510754_test.pdf
                $filedata->save();
                $s3->deleteObject([
                    'Bucket' => $bucketName,
                    'Key'    => $objectKey,
                ]);
            }
        }

        if (!$score->id_card || !$score->owner->id_card) {
            $score->id_card = "";
            $score->owner->id_card = "";
        }
        if (!$score->telephone || !$score->owner->telephone) {
            $score->telephone = "";
            $score->owner->telephone = "";
        }
        if (!$score->composer_request || !$score->owner->composer_request) {
            $score->composer_request = "";
            $score->owner->composer_request = "";
        }
        if (!$score->composer_aproved || !$score->owner->composer_aproved) {
            $score->composer_aproved = "";
            $score->owner->composer_aproved = "";
        }
        if (!$score->deleted_at || !$score->owner->deleted_at) {
            $score->deleted_at = "";
            $score->owner->deleted_at = "";
        }
        if (!$score->owner->otp) {
            $score->owner->otp = "";
        }
        return response()->json([
            'status' => true,
            'message' => 'Music Score Updated',
            'data' => $score
        ], 200);
    }

    public function delete(Request $request)
    {
        //
        $score = MusicScore::findOrFail($request->id);
        $score->delete();
        //
        return response()->json([
            'status' => true,
            'message' => 'Music Score Deleted',
            'data' => $score
        ], 200);
    }

    public function getStatistics($id)
    {
        $exist_data = MusicScore::where('id', $id)->first();
        if (empty($exist_data)) {
            return response()->json([
                'status' => false,
                'message' => 'Music score not found',
            ], 200);
        }
        $music_score = MusicScore::findORFail($id);
        if (empty($music_score->toArray())) {
            return response()->json([
                'status' => true,
                'message' => 'Music score not found',
            ], 200);
        }
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $name = $music_score->name;
        $date = $music_score->date;
        $total_count = LogDisplayMusicScore::where('music_scores_id', $id)->count();
        $monthly_count = LogDisplayMusicScore::where('music_scores_id', $id)->whereMonth('created_at', $currentMonth)->count();

        $startDate = Carbon::now()->subMonths(11)->day(1)->hour(00)->minute(00)->second(01); // Calculate the start date 12 months ago
        $endDate = Carbon::now()->day(1)->hour(00)->minute(00)->second(01);

        $countsByMonth = LogDisplayMusicScore::where('music_scores_id', $id)
            ->selectRaw('YEAR(log_display_music_scores.created_at) as year, MONTH(log_display_music_scores.created_at) as month, COUNT(*) as count')
            ->where('log_display_music_scores.created_at', '>=', $startDate)
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();
        $dateWiseCount = [];
        while ($startDate->lessThanOrEqualTo($endDate)) {

            $countData = collect($countsByMonth)->where('year', $startDate->year)->where('month', $startDate->month)->first();
            if (!empty($countData) && collect($countData)->count() > 0) {
                $dateWiseCount[] = array($countData['count'], sprintf('%02d-%d', $startDate->month, $startDate->year));
            } else {
                $dateWiseCount[] = array(0, sprintf('%02d-%d', $startDate->month, $startDate->year));
            }
            $startDate->addMonth();
        }
        $statistics = [];
        foreach ($dateWiseCount as $countData) {
            $statistics[] = [
                'count' => $countData[0],
                'date' => $countData[1],
            ];
        }
        $data = [
            'name' => $name,
            'date' => $date ? $date : "",
            'total_count' => $total_count,
            'monthly_count' => $monthly_count,
            'statistics' => $statistics,
        ];
        return response()->json([
            'status' => true,
            // 'data' => [
            //     'name' => $name,
            //     'date' => $date ? $date : "",
            //     'total_count' => $total_count,
            //     'monthly_count' => $monthly_count,
            //     'statistics' => $statistics,
            // ],
            'data' => [$data],
        ], 200);
    }


    // public function composerMusictemp(Request $request){
    //     $score = MusicScore::where('owner_id',$request->input('id'));
    //     $perPage = $request->input('limit', 10); // Number of records per page (default: 10)
    //     $page = $request->input('page', 1); // Current page number (default: 1)
    //     if ($request->has('name')) {
    //         $score->where('name', 'like', '%' . $request->input('name') . '%');
    //     }

    //     // Order by name or date
    //     if ($request->has('order')) {
    //         $order = $request->input('order');
    //         $type = $request->input('type');
    //         if ($order === 'name') {
    //             $score->orderBy('name',$type);
    //         } elseif ($order === 'date') {
    //             $score->orderBy('date',$type);
    //         }
    //     }
    //     $musicScores = $score->paginate($perPage, ['*'], 'page', $page);

    //     $musicScores->getCollection()->transform(function ($item) {
    //         $item->date = Carbon::parse($item->date)->format('j F Y');
    //         if (is_null($item->date)) {
    //             $item->date = ''; // Set an empty string
    //         }
    //         if (is_null($item->description)) {
    //             $item->description = ''; // Set an empty string
    //         }
    //         if (is_null($item->created_at)) {
    //             $item->created_at = ''; // Set an empty string
    //         }
    //         if (is_null($item->updated_at)) {
    //             $item->updated_at = ''; // Set an empty string
    //         }
    //         return $item;
    //     });

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Composer`s Music Scores Retrived',
    //         'data' => $musicScores
    //     ], 200);
    // }


    public function tempallmusic(Request $request)
    {
        // dd($request->type);
        // if($request->has('type')){
        // $music = [];
        // $datas = FkMusicScoreStyle::with('styleName', 'allMusicData')->get();

        // foreach ($datas as $data) {
        //     $styleName = optional($data->styleName)->name;
        //     $styleId = optional($data->styleName)->id;

        //     if (!isset($music[$styleName])) {
        //         $music[$styleName] = [];
        //         $music[$styleName][] = ['music_style_id'=> $styleId];
        //     }
        //     // $value = ['name'=>optional($data->allMusicData),
        //     //         'description'=>
        //     //         ];
        //     $music[$styleName][] = optional($data->allMusicData)->toArray();
        //     foreach($music[$styleName] as &$score ){
        //         if(empty($score['description'])){
        //             $score['description'] = '';
        //         }
        //     }
        // } 
        // // dd($music);
        // return response()->json([
        //     'status' => true,
        //     'message' => 'Music Scores Retrived',
        //     'data' => $music,
        // ], 200);


        // $music = [];
        // $datas = FkMusicScoreStyle::with('styleName', 'allMusicData')->get();

        // foreach ($datas as $data) {
        //     $styleName = optional($data->styleName)->name;
        //     $styleId = optional($data->styleName)->id;
        //     if (!isset($music['music_score'])) {
        //         $music['music_score'] = $styleId;
        //     }
        //     if (!isset($music[$styleName])) {
        //         $music[$styleName] = [];
        //     }
        //     // $value = ['name'=>optional($data->allMusicData),
        //     //         'description'=>
        //     //         ];
        //     $music[$styleName][] = optional($data->allMusicData)->toArray();
        //     foreach($music[$styleName] as &$score ){
        //         if(empty($score['description'])){
        //             $score['description'] = '';
        //         }
        //     }
        // } 
        // // dd($music);
        // return response()->json([
        //     'status' => true,
        //     'message' => 'Music Scores Retrived',
        //     'data' => $music,
        // ], 200);
        if (!$request->music_style_id) {


            $music = [];
            $datas = FkMusicScoreStyle::with('styleName', 'allMusicData')->get();

            foreach ($datas as $data) {
                $styleName = optional($data->styleName)->name;
                $styleId = optional($data->styleName)->id;

                if (!isset($music[$styleName])) {
                    $music[$styleName] = [
                        'music_style_name' => $styleName,
                        'music_style_id' => $styleId,
                        'music_scores' => [],
                    ];
                }

                if (!empty($data->allMusicData)) {
                    if ($data->allMusicData->description == null) {
                        $data->allMusicData->description = '';
                    }
                    $music[$styleName]['music_scores'][] = optional($data->allMusicData)->toArray();
                    // $music_scores['description'] = $music_scores['description'] ?? '';
                    // $music[$styleName]['music_scores'][] = $musicScore;
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Music Scores Retrived',
                'data' => array_values($music), // Convert associative array to indexed array
            ], 200);
        } else {
            $music = [];
            $datas = FkMusicScoreStyle::where('style_musics_id', $request->music_style_id)->with('styleName', 'allMusicData')->get();

            foreach ($datas as $data) {
                $styleName = optional($data->styleName)->name;
                $styleId = optional($data->styleName)->id;

                if (!isset($music[$styleName])) {
                    $music[$styleName] = [
                        'music_style_name' => $styleName,
                        'music_style_id' => $styleId,
                        'music_scores' => [],
                    ];
                }

                if (!empty($data->allMusicData)) {
                    if ($data->allMusicData->description == null) {
                        $data->allMusicData->description = '';
                    }
                    $music[$styleName]['music_scores'][] = optional($data->allMusicData)->toArray();
                    // $music_scores['description'] = $music_scores['description'] ?? '';
                    // $music[$styleName]['music_scores'][] = $musicScore;
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Music Scores Retrived',
                'data' => array_values($music), // Convert associative array to indexed array
            ], 200);
        }
    }

    public function allmusic(Request $request)
    {
        // dd($request->type);
        $music = [];
        if ($request->has('type') && $request->type == 'musicStyle') {
            // $datas = FkMusicScoreStyle::with('styleName', 'allMusicData')->get();

            // // foreach ($datas as $data) {
            // //     $styleName = optional($data->styleName)->name;

            // //     if (!isset($music['name'])) {                       
            // //         $music['name'] = $styleName;
            // //     }
            // //     // $music['name'] = optional($data->allMusicData)->toArray();
            // //     $music['music_score'] = optional($data->allMusicData)->toArray();
            // //     foreach($music['name'] as &$score ){
            // //         if(empty($score['description'])){
            // //             $score['description'] = '';
            // //         }
            // //     }
            // // } 

            // $music = [];

            // foreach ($datas as $data) {
            //     $styleName = optional($data->styleName)->name;

            //     if (!isset($music[$styleName])) {                       
            //         $music[$styleName] = [
            //             'name' => $styleName,
            //             'music_score' => [],
            //         ];
            //     }

            //     $music[$styleName]['music_score'][] = $data->allMusicData;
            // }
            $music = [];
            if (!$request->music_style_id) {
                $datas = FkMusicScoreStyle::with('styleName', 'allMusicData')->get();
            } else {
                $datas = FkMusicScoreStyle::where('style_musics_id', $request->music_style_id)->with('styleName', 'allMusicData')->get();
            }
            foreach ($datas as $data) {
                $styleName = optional($data->styleName)->name;
                $styleId = optional($data->styleName)->id;

                if (!isset($music[$styleName])) {
                    $music[$styleName] = [
                        'music_style_name' => $styleName,
                        'music_style_id' => $styleId,
                        'music_scores' => [],
                    ];
                }

                if (!empty($data->allMusicData)) {
                    if ($data->allMusicData->description == null) {
                        $data->allMusicData->description = '';
                    }
                    $valid = true;
                    if ($data->allMusicData->status == 0) {
                        $valid = false;
                    }
                    if ($valid) {
                        $music[$styleName]['music_scores'][] = optional($data->allMusicData)->toArray();
                        // $music_scores['description'] = $music_scores['description'] ?? '';
                        // $music[$styleName]['music_scores'][] = $musicScore;    
                    }
                }
            }
            $filteredMusic = array_filter($music, function ($entry) {
                return !empty($entry['music_scores']);
            });

            if (!$filteredMusic) {
                return response()->json([
                    'status' => true,
                    'message' => 'Music scores not found ',
                ], 200);
            }

            return response()->json([
                'status' => true,
                'message' => 'Music Scores Retrived',
                'data' => array_values($filteredMusic), // Convert associative array to indexed array
            ], 200);
            // if(!$music){
            //     return response()->json([
            //         'status' => true,
            //         'message' => 'Music scores not found ',
            //     ], 200);
            // }
            // return response()->json([
            //     'status' => true,
            //     'message' => 'Music Scores Retrived',
            //     'data' => array_values($music), // Convert associative array to indexed array
            // ], 200);


        } elseif ($request->has('type') && $request->type == 'instrument') {
            //     $datas = FkMusicScoreInstrument::with('instrumentName', 'allMusicData')->get();

            //     foreach ($datas as $data) {
            //         $instrumentName = optional($data->instrumentName)->name;

            //         if (!isset($music[$instrumentName])) {
            //             $music[$instrumentName] = [];
            //         }
            //         $music[$instrumentName][] = optional($data->allMusicData)->toArray();
            //         foreach($music[$instrumentName] as &$score ){      
            //             if(empty($score['description'])){
            //                 $score['description'] = '';
            //             }
            //         }
            //     } 
            // }
            // // elseif($request->has('type') && $request->type == 'familyInstrument'){
            // //     $datas = FkMusicScoreFamilyInstrument::with('familyInstrumentName', 'allMusicData')->get();

            // //     foreach ($datas as $data) {
            // //         $instrumentName = optional($data->instrumentName)->name;

            // //         if (!isset($music[$instrumentName])) {
            // //             $music[$instrumentName] = [];
            // //         }
            // //         $music[$instrumentName][] = optional($data->allMusicData)->toArray();
            // //         foreach($music[$instrumentName] as &$score ){      
            // //             if(empty($score['description'])){
            // //                 $score['description'] = '';
            // //             }
            // //         }
            // //     } 
            // // }
            //     // dd($music);
            // return response()->json([
            //     'status' => true,
            //     'message' => 'Music Scores Retrived',
            //     'data' => [$music],
            // ], 200);


            $music = [];
            if (!$request->music_instrument_id) {
                $datas = FkMusicScoreInstrument::with('instrumentName', 'allMusicData')->get();
            } else {
                $datas = FkMusicScoreInstrument::where('instruments_id', $request->music_instrument_id)->with('instrumentName', 'allMusicData')->get();
            }
            foreach ($datas as $data) {
                $instrumentName = optional($data->instrumentName)->name;
                $instrumentId = optional($data->instrumentName)->id;

                if (!isset($music[$instrumentName])) {
                    $music[$instrumentName] = [
                        'music_instrument_name' => $instrumentName,
                        'music_instrument_id' => $instrumentId,
                        'music_scores' => [],
                    ];
                }

                if (!empty($data->allMusicData)) {
                    if ($data->allMusicData->description == null) {
                        $data->allMusicData->description = '';
                    }
                    if ($data->allMusicData->date == null) {
                        $data->allMusicData->date = '';
                    }
                    $valid = true;
                    if ($data->allMusicData->status == 0) {
                        $valid = false;
                    }
                    if ($valid) {
                        $music[$instrumentName]['music_scores'][] = optional($data->allMusicData)->toArray();
                        // $music_scores['description'] = $music_scores['description'] ?? '';
                        // $music[$styleName]['music_scores'][] = $musicScore;
                    }
                }
            }
            $filteredMusic = array_filter($music, function ($entry) {
                return !empty($entry['music_scores']);
            });

            if (!$filteredMusic) {
                return response()->json([
                    'status' => true,
                    'message' => 'Music scores not found ',
                ], 200);
            }

            return response()->json([
                'status' => true,
                'message' => 'Music Scores Retrived',
                'data' => array_values($filteredMusic), // Convert associative array to indexed array
            ], 200);
            // if(!$music){
            //     return response()->json([
            //         'status' => true,
            //         'message' => 'Music scores not found ',
            //     ], 200);
            // }
            // return response()->json([
            //     'status' => true,
            //     'message' => 'Music Scores Retrived',
            //     'data' => array_values($music), // Convert associative array to indexed array
            // ], 200);


        }
    }
    public function favMusicScore(Request $request)
    {
        $exist_data = MusicScore::where('id', $request->music_score_id)->first();
        if (empty($exist_data)) {
            return response()->json([
                'status' => false,
                'message' => 'Music score not found',
            ], 200);
        }
        if ($request->music_score_id) {
            $user = auth()->user();
            $musicScoreId = $request->music_score_id;
            if ($user->favMusic()->where('music_scores_id', $musicScoreId)->exists()) {
                // $user->favMusic()->detach($musicScoreId);
                return response()->json([
                    'status' => false,
                    'message' => 'Music score already added to favourite',
                ], 200);
            } else {
                $user->favMusic()->attach($musicScoreId, ['created_at' => now(), 'updated_at' => now()]);
                return response()->json([
                    'status' => true,
                    'message' => 'Music score added to favourite',
                ], 200);
            }
        }
    }

    public function removeFavMusicScore(Request $request)
    {
        $exist_data = MusicScore::where('id', $request->music_score_id)->first();
        if (empty($exist_data)) {
            return response()->json([
                'status' => false,
                'message' => 'Music score not found',
            ], 200);
        }
        if ($request->music_score_id) {
            $user = auth()->user();
            $musicScoreId = $request->music_score_id;
            if ($user->favMusic()->where('music_scores_id', $musicScoreId)->exists()) {
                $user->favMusic()->detach($musicScoreId);
                return response()->json([
                    'status' => true,
                    'message' => 'Music score removed from favourite',
                ], 200);
            }
        }
    }
    public function usersFavMusicScore()
    {
        $user = auth()->user();
        $favMusicScores = $user->favMusic;
        foreach ($favMusicScores as $favMusicScore) {
            if ($favMusicScore->description == null) {
                $favMusicScore->description = "";
            }
            if ($favMusicScore->date == null) {
                $favMusicScore->date = "";
            }
        }
        return response()->json([
            'status' => true,
            'data' => $favMusicScores,
        ], 200);
    }
}
