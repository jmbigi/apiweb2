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

class PagesController extends Controller
{
    public function index(Request $request)
    {
        $user_count = 0;
        $request_count = 0;
        $composer_count = 0;
        $music_score_count = 0;
        $unique_score_view_count = 0;
        $active_user_count = 0;
        $daily_score_view_count = 0;
        $daily_active_user_count = 0;
        $all_requests = ComposerRequest::latest()->limit(10)->get();
        $composers = Composer::all();
        $composer_status = ComposerStatus::all();

        $monthlyUserCounts = [];
        $monthlyRequestCounts = [];
        $monthlyComposerCounts = [];
        $monthlyMusicScoreCounts = [];
        $weeklyUniqueScoreViewCounts = [];
        $weeklyActiveUserCounts = [];
        $dailyScoreViewCounts = [];
        $dailyActiveUserCounts = [];
        $statDates = [];
        $statMonths = [];
        $statDailyDates = [];
        $statDailyMonths = [];

        $monthTranslations = [
            'Jan' => 'Ene',
            'Feb' => 'Feb',
            'Mar' => 'Mar',
            'Apr' => 'Abr',
            'May' => 'May',
            'Jun' => 'Jun',
            'Jul' => 'Jul',
            'Aug' => 'Ago',
            'Sep' => 'Sep',
            'Oct' => 'Oct',
            'Nov' => 'Nov',
            'Dec' => 'Dic',
        ];
        
        $currentDate = Carbon::now()->subWeek(); // Obtener el mes anterior
        $currentDate->subYear();

        $startDate = '';
        $numWeeks = 52;
        for ($i = 0; $i < $numWeeks; $i++) {
            $startDate = $currentDate->copy()->startOfWeek();
            $endDate = $currentDate->copy()->endOfWeek();

            $formattedStartDate = $monthTranslations[$startDate->format('M')] . $startDate->format('-y') . ' s' . $startDate->weekOfMonth; // Mes/Año sSemana
            if ($startDate->weekOfMonth == 1) {
                $formattedStartMonth =  $monthTranslations[$startDate->format('M')] . ($currentDate->month == 1 ? $startDate->format('-y') : '');
            } else {
                $formattedStartMonth = '';
            }
            $statDates[] = $formattedStartDate;
            $statMonths[] = $formattedStartMonth;

            $userCount = DB::table('users')
                ->where('email', '!=', 'superadmin@gmail.com')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('deleted_at', null)
                ->count();
            $monthlyUserCounts[] = $userCount;
            $user_count += $userCount;

            $requestCount = DB::table('composer_request')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('deleted_at', null)
                ->count();
            $monthlyRequestCounts[] = $requestCount;
            $request_count += $requestCount;
            
            $composerCount = DB::table('composers')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('deleted_at', null)
                ->count();
            $monthlyComposerCounts[] = $composerCount;
            $composer_count += $composerCount;
    
            $musicScoreCount = DB::table('music_scores')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNot('owner_id', 109)
                ->count();
            $monthlyMusicScoreCounts[] = $musicScoreCount;
            $music_score_count += $musicScoreCount;

            $uniqueScoreViewsCount = DB::table('log_display_music_scores')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select('users_id', 'music_scores_id')
                ->groupBy('users_id', 'music_scores_id') // Agrupar por ambas columnas
                ->get()
                ->count(); // Contar el número de grupos
            $weeklyUniqueScoreViewCounts[] = $uniqueScoreViewsCount;
            $unique_score_view_count += $uniqueScoreViewsCount;

            $uniqueActiveUsersCount = DB::table('log_display_music_scores')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select('users_id')
                ->groupBy('users_id')
                ->get()
                ->count(); // Contar el número de grupos
            $weeklyActiveUserCounts[] = $uniqueActiveUsersCount;
            $active_user_count += $uniqueActiveUsersCount;

            $currentDate->addWeek(); // Mover al mes anterior
        }
        // dd($startDate);

        $currentDate = Carbon::today();
        $currentDate->subMonth();
        $startDate = '';
        $numDays = 31;
        for ($i = 0; $i < $numDays; $i++) {
            $startDate = $currentDate->copy()->startOfDay();
            $endDate = $currentDate->copy()->endOfDay();
            $formattedStartDate = $startDate->day . ' ' . $monthTranslations[$startDate->format('M')];
            if ($startDate->dayOfWeek == 1) {
                $formattedStartMonth =  $startDate->day . ' ' . $monthTranslations[$startDate->format('M')];
            } else {
                $formattedStartMonth =  '';
            }
            $statDailyDates[] = $formattedStartDate;
            $statDailyMonths[] = $formattedStartMonth;

            $scoreViewsCount = DB::table('log_display_music_scores')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();
            $dailyScoreViewCounts[] = $scoreViewsCount;
            $daily_score_view_count += $scoreViewsCount;

            $uniqueActiveUsersCount = DB::table('log_display_music_scores')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select('users_id')
                ->groupBy('users_id')
                ->get()
                ->count(); // Contar el número de grupos
            $dailyActiveUserCounts[] = $uniqueActiveUsersCount;
            $daily_active_user_count += $uniqueActiveUsersCount;

            $currentDate->addDay(); // Mover al dia anterior
        }

        if ($request->ajax()) {
            $data = ComposerRequest::latest()->limit(10);
            $counter = 1;
    
            if ($request->has('search') && !is_null($request->get('search')['value'])) {
                $regex = $request->get('search')['value'];
                $data->where(function ($q) use ($regex) {
                    $q->whereHas('composer', function ($query) use ($regex) {
                        $query->where('public_name', 'like', '%' . $regex . '%');
                    });
                });
            }
    
            if ($request->has('order') && !empty($request->order[0]['column'] != 0)) {
                $regex = $request->order[0]['dir'];
                if ($request->order[0]['column'] == 1) {
                    $data->join('composers', 'composer_request.composers_id', '=', 'composers.id')
                        ->select('composer_request.*', 'composers.id as composers_id_alias', 'composers.public_name')
                        ->orderBy('composers.public_name', $regex);
                }
            }
    
            $data = $data->get();
            return Datatables::of($data)->addColumn('index', function ($row) use (&$counter) {
                return ($row->request_status_id == 3 && $row->composer_status_id == 2) ?
                    '<span>' . $row->id . '</span>' :
                    '<a href="' . route('composer_request.edit', ['id' => $row->id]) . '">' . $row->id . '</a>';
            })
            ->addColumn('name', function ($row) {
                $composer = Composer::find($row->composers_id);
                return $composer ? $composer->public_name : '';
            })
            ->addColumn('requested_date', function ($row) {
                return date('Y-m-d', strtotime($row->request_date));
            })
            ->addColumn('description', function ($row) {
                return $row->description;
            })
            ->addColumn('composer_req_status', function ($row) {
                $composer = RequestStatus::find($row->request_status_id);
                return $composer ? '<label class="label label-lg label-light-' . ($composer->name == 'Pending' ? 'warning' : ($composer->name == 'In Progress' ? 'primary' : 'danger')) . ' label-inline">' . $composer->name . '</label>' : '';
            })
            ->addColumn('composer_status', function ($row) {
                $composer = ComposerStatus::find($row->composer_status_id);
                $statusColors = [
                    'Pending' => 'warning',
                    'Active' => 'success',
                    'Rejected' => 'default',
                    'Suspended' => 'danger'
                ];
                return $composer ? '<label class="label label-lg label-light-' . ($statusColors[$composer->name] ?? 'default') . ' label-inline">' . $composer->name . '</label>' : '';
            })
            ->addColumn('action', function ($row) {
                return '<a href ="#" data-id="' . $row->id . '" class="home_delete_req mx-3 "><i class="fas fa-trash fa-lg text-danger"></i></a>';
            })
            ->rawColumns(['index', 'composer_req_status', 'composer_status', 'action'])
            ->make(true);
        } else {
            $users = Composer::oldest('id')->take(20)->get();
            return view('pages.dashboard')
                ->with('statDates', $statDates)
                ->with('statMonths', $statMonths)
                ->with('statDailyDates', $statDailyDates)
                ->with('statDailyMonths', $statDailyMonths)
                ->with('users', $users)
                ->with('monthlyUserCounts', $monthlyUserCounts)
                ->with('user_count', round($user_count / $numWeeks, 0))
                ->with('composer_count', round($composer_count / $numWeeks, 0))
                ->with('music_score_count', round($music_score_count / $numWeeks, 0))
                ->with('request_count', round($request_count / $numWeeks, 0))
                ->with('unique_score_view_count', round($unique_score_view_count / $numWeeks, 0))
                ->with('active_user_count', round($active_user_count / $numWeeks, 0))
                ->with('request_count', round($request_count / $numWeeks, 0))
                ->with('daily_score_view_count', round($daily_score_view_count / $numDays, 0))
                ->with('daily_active_user_count', round($daily_active_user_count / $numDays, 0))
                ->with('monthlyRequestCounts', $monthlyRequestCounts)
                ->with('monthlyComposerCounts', $monthlyComposerCounts)
                ->with('monthlyMusicScoreCounts', $monthlyMusicScoreCounts)
                ->with('weeklyUniqueScoreViewCounts', $weeklyUniqueScoreViewCounts)
                ->with('weeklyActiveUserCounts', $weeklyActiveUserCounts)
                ->with('dailyScoreViewCounts', $dailyScoreViewCounts)
                ->with('dailyActiveUserCounts', $dailyActiveUserCounts)
                ->with('all_requests', $all_requests)
                ->with('composers', $composers)
                ;
        }
    
        return view('admin.composer_request.index');
    }
    

    /**
     * Demo methods below
     */

    // Datatables
    public function datatables()
    {
        $page_title = 'Datatables';
        $page_description = 'This is datatables test page';

        return view('pages.datatables', compact('page_title', 'page_description'));
    }

    // KTDatatables
    public function ktDatatables()
    {
        $page_title = 'KTDatatables';
        $page_description = 'This is KTdatatables test page';

        return view('pages.ktdatatables', compact('page_title', 'page_description'));
    }

    // Select2
    public function select2()
    {
        $page_title = 'Select 2';
        $page_description = 'This is Select2 test page';

        return view('pages.select2', compact('page_title', 'page_description'));
    }

    // jQuery-mask
    public function jQueryMask()
    {
        $page_title = 'jquery-mask';
        $page_description = 'This is jquery masks test page';

        return view('pages.jquery-mask', compact('page_title', 'page_description'));
    }

    // custom-icons
    public function customIcons()
    {
        $page_title = 'customIcons';
        $page_description = 'This is customIcons test page';

        return view('pages.icons.custom-icons', compact('page_title', 'page_description'));
    }

    // flaticon
    public function flaticon()
    {
        $page_title = 'flaticon';
        $page_description = 'This is flaticon test page';

        return view('pages.icons.flaticon', compact('page_title', 'page_description'));
    }

    // fontawesome
    public function fontawesome()
    {
        $page_title = 'fontawesome';
        $page_description = 'This is fontawesome test page';

        return view('pages.icons.fontawesome', compact('page_title', 'page_description'));
    }

    // lineawesome
    public function lineawesome()
    {
        $page_title = 'lineawesome';
        $page_description = 'This is lineawesome test page';

        return view('pages.icons.lineawesome', compact('page_title', 'page_description'));
    }

    // socicons
    public function socicons()
    {
        $page_title = 'socicons';
        $page_description = 'This is socicons test page';

        return view('pages.icons.socicons', compact('page_title', 'page_description'));
    }

    // svg
    public function svg()
    {
        $page_title = 'svg';
        $page_description = 'This is svg test page';

        return view('pages.icons.svg', compact('page_title', 'page_description'));
    }

    // Quicksearch Result
    public function quickSearch()
    {
        return view('layout.partials.extras._quick_search_result');
    }
}
