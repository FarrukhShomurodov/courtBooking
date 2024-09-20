<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\BotUser;
use App\Models\Court;
use App\Models\SportType;
use App\Models\Stadium;
use App\Models\User;
use App\Repositories\StatisticsRepositories;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StatisticsController extends Controller
{
    protected StatisticsRepositories $statisticsRepository;

    public function __construct(StatisticsRepositories $statisticsRepository)
    {
        $this->statisticsRepository = $statisticsRepository;
    }

    public function stadiums(Request $request): View|\Illuminate\Foundation\Application|Factory|Application
    {
        if (Auth::user()->roles()->first()->name == 'owner stadium') {
            $stadiums = Auth::user()->stadiumOwner()->get();
        } else {
            $stadiums = Stadium::all();
        }

        if ($request->get('owner-id')) {
            $owner = User::query()->find($request->get('owner-id'));

            if ($owner && $owner->hasRole('owner stadium')) {
                $stadiums = $owner->stadiumOwner()->get();
            }
        }

        if ($request->get('sport-type-id') && $request->get('sport-type-id') !== 'all') {
            $stadiums = $stadiums->filter(function ($stadium) use ($request) {
                return $stadium->sportTypes->contains('id', $request->get('sport-type-id'));
            });
        }

        $dateFrom = null;
        $dateTo = null;

        if ($request->get('date_from')) {
            $dateFrom = $request->get('date_from');
        }

        if ($request->get('date_to')) {
            $dateTo = $request->get('date_to');
        }


        $statistics = [];

        foreach ($stadiums as $stadium) {
            $statistics[] = [
                'stadium' => $stadium,
                'statistic' => $this->statisticsRepository->stadiumStatistics($stadium, $dateFrom, $dateTo),
            ];
        }
        $sportTypes = SportType::all();
        $ownerStadium = User::query()->whereDoesntHave('roles', function ($query) {
            $query->where('name', 'user')
                ->orWhere('name', 'trainer');
        })->get();


        $totalStatistics = [
            'total_book_count' => 0,
            'bot_book_count' => 0,
            'manual_book_count' => 0,
            'total_revenue' => 0,
            'bot_revenue' => 0,
            'manual_revenue' => 0,
            'unbooked_hours' => 0,
        ];

        foreach ($statistics as $statistic) {
            $totalStatistics['total_book_count'] += $statistic['statistic']['total_book_count'];
            $totalStatistics['bot_book_count'] += $statistic['statistic']['bot_book_count'];
            $totalStatistics['manual_book_count'] += $statistic['statistic']['manual_book_count'];
            $totalStatistics['total_revenue'] += $statistic['statistic']['total_revenue'];
            $totalStatistics['bot_revenue'] += $statistic['statistic']['bot_revenue'];
            $totalStatistics['manual_revenue'] += $statistic['statistic']['manual_revenue'];
            $totalStatistics['unbooked_hours'] += $statistic['statistic']['unbooked_hours'];
        }
        return view('admin.statistics.stadiums', compact('statistics', 'sportTypes', 'ownerStadium', 'totalStatistics'));
    }

    public function courts(Request $request): View|\Illuminate\Foundation\Application|Factory|Application
    {
        if (Auth::user()->roles()->first()->name == 'owner stadium') {
            $courts = Auth::user()->stadiumOwner()->first()->courts()->get();
        } else {
            $courts = Court::all();
        }

        if ($request->get('owner-id')) {
            $owner = User::query()->find($request->get('owner-id'));

            if ($owner && $owner->hasRole('owner stadium')) {
                $courts = $owner->stadiumOwner()->first()->courts()->get();
            }
        }

        if ($request->get('sport-type-id') && $request->get('sport-type-id') !== 'all') {
            $courts = $courts->filter(function ($court) use ($request) {
                return $court->sport_type_id == $request->get('sport-type-id');
            });
        }


        $dateFrom = null;
        $dateTo = null;

        if ($request->get('date_from')) {
            $dateFrom = $request->get('date_from');
        }

        if ($request->get('date_to')) {
            $dateTo = $request->get('date_to');
        }

        $statistics = [];

        foreach ($courts as $court) {
            $statistics[] = [
                'court' => $court,
                'statistic' => $this->statisticsRepository->courtStatistics($court, $dateFrom, $dateTo),
            ];
        }

        $totalStatistics = [
            'total_book_count' => 0,
            'bot_book_count' => 0,
            'manual_book_count' => 0,
            'total_revenue' => 0,
            'bot_revenue' => 0,
            'manual_revenue' => 0,
            'unbooked_hours' => 0,
        ];

        foreach ($statistics as $statistic) {
            $totalStatistics['total_book_count'] += $statistic['statistic']['total_book_count'];
            $totalStatistics['bot_book_count'] += $statistic['statistic']['bot_book_count'];
            $totalStatistics['manual_book_count'] += $statistic['statistic']['manual_book_count'];
            $totalStatistics['total_revenue'] += $statistic['statistic']['total_revenue'];
            $totalStatistics['bot_revenue'] += $statistic['statistic']['bot_revenue'];
            $totalStatistics['manual_revenue'] += $statistic['statistic']['manual_revenue'];
            $totalStatistics['unbooked_hours'] += $statistic['statistic']['unbooked_hours'];
        }

        $sportTypes = SportType::all();
        $ownerStadium = User::query()->whereDoesntHave('roles', function ($query) {
            $query->where('name', 'user')
                ->orWhere('name', 'trainer');
        })->get();
        return view('admin.statistics.courts', compact('statistics', 'sportTypes', 'ownerStadium', 'totalStatistics'));
    }

    public function sportType(Request $request): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $sportTypeId = $request->input('sport-type-id');
        $stadiumId = $request->input('stadium-id') ?? 'all';

        if (Auth::user()->roles()->first()->name == 'owner stadium') {
            $sportTypes = Auth::user()->stadiumOwner()->first()->sportTypes()->get();
            $stadiumId = Auth::user()->stadiumOwner()->first()->id;
            $allSportTypes = Auth::user()->stadiumOwner()->first()->sportTypes()->get();
        } else {
            $sportTypes = SportType::all();
            $allSportTypes = SportType::all();
        }

        if ($stadiumId && $stadiumId !== 'all') {
            $sportTypes = Stadium::query()->find($stadiumId)->sportTypes()->get();
        }

        if ($sportTypeId && $sportTypeId !== 'all') {
            $sportTypes = SportType::with('courts.bookings')->where('id', $sportTypeId)->get();
        }

        $dateFrom = null;
        $dateTo = null;

        if ($request->get('date_from')) {
            $dateFrom = $request->get('date_from');
        }

        if ($request->get('date_to')) {
            $dateTo = $request->get('date_to');
        }
        $statistics = [];


        foreach ($sportTypes as $sportType) {
            $statistics[] = [
                'spotType' => $sportType,
                'statistic' => $this->statisticsRepository->sportTypeStatistics($sportType, $stadiumId, $dateFrom, $dateTo),
            ];
        }

        $totalStatistics = [
            'total_bookings' => 0,
            'total_revenue' => 0,
            'manual_revenue' => 0,
            'bot_revenue' => 0,
            'unbooked_hours' => 0,
        ];

        foreach ($statistics as $statistic) {
            $totalStatistics['total_bookings'] += $statistic['statistic']['total_bookings'];
            $totalStatistics['total_revenue'] += $statistic['statistic']['total_revenue'];
            $totalStatistics['manual_revenue'] += $statistic['statistic']['manual_revenue'];
            $totalStatistics['bot_revenue'] += $statistic['statistic']['bot_revenue'];
            $totalStatistics['unbooked_hours'] += $statistic['statistic']['unbooked_hours'];
        }

        $stadiums = Stadium::all();
        return view('admin.statistics.sport-types', compact('statistics', 'allSportTypes', 'stadiums', 'totalStatistics'));
    }

    public function exportStatistics(): StreamedResponse
    {
        $role = Auth::user()->roles()->first()->name;

        if ($role == 'admin') {
            $adminStatistics = $this->statisticsRepository->adminStatistics();
            $statistics = [
                __('dashboard.Пользователи сайта') => $adminStatistics['user_count'],
                __('dashboard.Пользователи бота') => $adminStatistics['bot_user_count'],
                __('dashboard.Пользователи сайта') => $adminStatistics['total_user_count'],
                __('dashboard.Стадионы') => $adminStatistics['stadium_count'],
                __('dashboard.Корты') => $adminStatistics['court_count'],
                __('dashboard.Брони') => $adminStatistics['booking_count'],
                __('dashboard.Типы спорта') => $adminStatistics['sport_type_count'],
                __('dashboard.Дата наибольшего бронирования') => $adminStatistics['most_booking_date'],
                __('dashboard.Cамый загруженный временной интервал') => is_string($adminStatistics['most_booked_time_slot']) ? $adminStatistics['most_booked_time_slot'] : $adminStatistics['most_booked_time_slot']->start_time . ' - ' . $adminStatistics['most_booked_time_slot']->end_time,
            ];
        } elseif ($role == 'owner stadium') {
            $statistics = $this->statisticsRepository->stadiumOwnerStatistics();
        } else {
            abort(403, 'Access denied');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = [
            __('dashboard.metric'),
            __('dashboard.value'),
        ];

        $columnLetters = range('A', 'B');
        foreach ($columnLetters as $index => $letter) {
            $sheet->setCellValue($letter . '1', $headers[$index]);
            $sheet->getStyle($letter . '1')->getFont()->setBold(true); // Make header bold
        }

        // Add data to cells
        $row = 2;
        foreach ($statistics as $key => $value) {
            $sheet->setCellValue('A' . $row, $key);
            $sheet->setCellValue('B' . $row, is_array($value) ? json_encode($value) : $value);
            $row++;
        }


        // Auto resize columns
        foreach ($columnLetters as $letter) {
            $sheet->getColumnDimension($letter)->setAutoSize(true);
        }
        // Create writer and set the output stream
        $writer = new Xlsx($spreadsheet);

        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="statistics.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    public function exportStadiumsStatistics(): StreamedResponse
    {
        $role = Auth::user()->roles()->first()->name;

        if ($role == 'owner stadium') {
            $stadiums = Auth::user()->stadiumOwner()->get();
        } else {
            $stadiums = Stadium::all();
        }

        $statistics = [];

        foreach ($stadiums as $stadium) {
            $statistics[] = [
                'stadium' => $stadium->name,
                'bot_book_count' => $this->statisticsRepository->stadiumStatistics($stadium)['bot_book_count'],
                'manual_book_count' => $this->statisticsRepository->stadiumStatistics($stadium)['manual_book_count'],
                'total_book_count' => $this->statisticsRepository->stadiumStatistics($stadium)['total_book_count'],
                'bot_revenue' => $this->statisticsRepository->stadiumStatistics($stadium)['bot_revenue'],
                'manual_revenue' => $this->statisticsRepository->stadiumStatistics($stadium)['manual_revenue'],
                'total_revenue' => $this->statisticsRepository->stadiumStatistics($stadium)['total_revenue'],
                'unbooked_hours' => $this->statisticsRepository->stadiumStatistics($stadium)['unbooked_hours'],
            ];
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = [
            __('stadium.stadium'),
            __('stadium.bot_hours'),
            __('stadium.manual_hours'),
            __('stadium.total_hours'),
            __('stadium.bot_revenue'),
            __('stadium.manual_revenue'),
            __('stadium.total_revenue'),
            __('stadium.unbooked_hours'),
        ];

        $columnLetters = range('A', 'H');
        foreach ($columnLetters as $index => $letter) {
            $sheet->setCellValue($letter . '1', $headers[$index]);
            $sheet->getStyle($letter . '1')->getFont()->setBold(true); // Make header bold
        }

        // Add data to cells
        $row = 2;
        foreach ($statistics as $data) {
            $sheet->setCellValue('A' . $row, $data['stadium']);
            $sheet->setCellValue('B' . $row, $data['bot_book_count']);
            $sheet->setCellValue('C' . $row, $data['manual_book_count']);
            $sheet->setCellValue('D' . $row, $data['total_book_count']);
            $sheet->setCellValue('E' . $row, $data['bot_revenue']);
            $sheet->setCellValue('F' . $row, $data['manual_revenue']);
            $sheet->setCellValue('G' . $row, $data['total_revenue']);
            $sheet->setCellValue('H' . $row, $data['unbooked_hours']);
            $row++;
        }

        // Auto resize columns
        foreach ($columnLetters as $letter) {
            $sheet->getColumnDimension($letter)->setAutoSize(true);
        }

        // Create writer and set the output stream
        $writer = new Xlsx($spreadsheet);

        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="stadium_statistics.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    public function exportCourtsStatistics(): StreamedResponse
    {
        $role = Auth::user()->roles()->first()->name;

        if (Auth::user()->roles()->first()->name == 'owner stadium') {
            $courts = Auth::user()->stadiumOwner()->first()->courts()->get();
        } else {
            $courts = Court::all();
        }

        $statistics = [];

        foreach ($courts as $court) {
            $statistics[] = [
                'court' => $court->name,
                'bot_book_count' => $this->statisticsRepository->courtStatistics($court)['bot_book_count'],
                'manual_book_count' => $this->statisticsRepository->courtStatistics($court)['manual_book_count'],
                'total_book_count' => $this->statisticsRepository->courtStatistics($court)['total_book_count'],
                'bot_revenue' => $this->statisticsRepository->courtStatistics($court)['bot_revenue'],
                'manual_revenue' => $this->statisticsRepository->courtStatistics($court)['manual_revenue'],
                'total_revenue' => $this->statisticsRepository->courtStatistics($court)['total_revenue'],
                'unbooked_hours' => $this->statisticsRepository->courtStatistics($court)['unbooked_hours'],
            ];
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers

        $headers = [
            __('court.court'),
            __('court.bot_hours'),
            __('court.manual_hours'),
            __('court.total_hours'),
            __('court.bot_revenue'),
            __('court.manual_revenue'),
            __('court.total_revenue'),
            __('stadium.unbooked_hours'),
        ];

        $columnLetters = range('A', 'H');
        foreach ($columnLetters as $index => $letter) {
            $sheet->setCellValue($letter . '1', $headers[$index]);
            $sheet->getStyle($letter . '1')->getFont()->setBold(true); // Make header bold
        }

        // Add data to cells
        $row = 2;
        foreach ($statistics as $data) {
            $sheet->setCellValue('A' . $row, $data['court']);
            $sheet->setCellValue('B' . $row, $data['bot_book_count']);
            $sheet->setCellValue('C' . $row, $data['manual_book_count']);
            $sheet->setCellValue('D' . $row, $data['total_book_count']);
            $sheet->setCellValue('E' . $row, $data['bot_revenue']);
            $sheet->setCellValue('F' . $row, $data['manual_revenue']);
            $sheet->setCellValue('G' . $row, $data['total_revenue']);
            $sheet->setCellValue('H' . $row, $data['unbooked_hours']);
            $row++;
        }

        // Auto resize columns
        foreach ($columnLetters as $letter) {
            $sheet->getColumnDimension($letter)->setAutoSize(true);
        }

        // Create writer and set the output stream
        $writer = new Xlsx($spreadsheet);

        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="court_statistics.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    public function exportSportTypeStatistics(Request $request): StreamedResponse
    {
        $sportTypeId = $request->input('sport-type-id');
        $stadiumId = $request->input('stadium-id') ?? 'all';

        if (Auth::user()->roles()->first()->name == 'owner stadium') {
            $sportTypes = Auth::user()->stadiumOwner()->first()->sportTypes()->get();
            $stadiumId = Auth::user()->stadiumOwner()->first()->id;
            $allSportTypes = Auth::user()->stadiumOwner()->first()->sportTypes()->get();
        } else {
            $sportTypes = SportType::all();
            $allSportTypes = SportType::all();
        }

        if ($stadiumId && $stadiumId !== 'all') {
            $sportTypes = Stadium::query()->find($stadiumId)->sportTypes()->get();
        }

        if ($sportTypeId && $sportTypeId !== 'all') {
            $sportTypes = SportType::with('courts.bookings')->where('id', $sportTypeId)->get();
        }

        $dateFrom = null;
        $dateTo = null;

        if ($request->get('date_from')) {
            $dateFrom = $request->get('date_from');
        }

        if ($request->get('date_to')) {
            $dateTo = $request->get('date_to');
        }
        $statistics = [];

        foreach ($sportTypes as $sportType) {
            $mostBookedTimeSlot = $this->statisticsRepository->sportTypeStatistics($sportType,  $stadiumId, $dateFrom, $dateTo)['most_booked_time_slot'];
            $statistics[] = [
                'sport_type' => $sportType->name,
                'total_bookings' => $this->statisticsRepository->sportTypeStatistics($sportType,  $stadiumId, $dateFrom, $dateTo)['total_bookings'],
                'total_revenue' => $this->statisticsRepository->sportTypeStatistics($sportType,  $stadiumId, $dateFrom, $dateTo)['total_revenue'],
                'manual_revenue' => $this->statisticsRepository->sportTypeStatistics($sportType,  $stadiumId, $dateFrom, $dateTo)['manual_revenue'],
                'bot_revenue' => $this->statisticsRepository->sportTypeStatistics($sportType,  $stadiumId, $dateFrom, $dateTo)['bot_revenue'],
                'most_booked_date' => $this->statisticsRepository->sportTypeStatistics($sportType,  $stadiumId, $dateFrom, $dateTo)['most_booked_date'] ?? '-',
                'most_booked_time_slot' => $mostBookedTimeSlot ? $mostBookedTimeSlot['start_time'] . ' - ' . $mostBookedTimeSlot['end_time'] : '-',
                'unbooked_hours' => $this->statisticsRepository->sportTypeStatistics($sportType,  $stadiumId, $dateFrom, $dateTo)['unbooked_hours'],
            ];
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = [
            __('sportType.name'),
            __('book.all_book'),
            __('court.total_revenue'),
            __('court.manual_revenue'),
            __('court.bot_revenue'),
            __('dashboard.Дата наибольшего бронирования'),
            __('dashboard.Cамый загруженный временной интервал'),
            __('stadium.unbooked_hours'),
        ];

        $columnLetters = range('A', 'H');
        foreach ($columnLetters as $index => $letter) {
            $sheet->setCellValue($letter . '1', $headers[$index]);
            $sheet->getStyle($letter . '1')->getFont()->setBold(true); // Make header bold
        }

        // Add data to cells
        $row = 2;
        foreach ($statistics as $data) {
            $sheet->setCellValue('A' . $row, $data['sport_type']);
            $sheet->setCellValue('B' . $row, $data['total_bookings']);
            $sheet->setCellValue('C' . $row, $data['total_revenue']);
            $sheet->setCellValue('D' . $row, $data['manual_revenue']);
            $sheet->setCellValue('E' . $row, $data['bot_revenue']);
            $sheet->setCellValue('F' . $row, $data['most_booked_date']);
            $sheet->setCellValue('G' . $row, $data['most_booked_time_slot']);
            $sheet->setCellValue('H' . $row, $data['unbooked_hours']);
            $row++;
        }

        // Auto resize columns
        foreach ($columnLetters as $letter) {
            $sheet->getColumnDimension($letter)->setAutoSize(true);
        }


        // Create writer and set the output stream
        $writer = new Xlsx($spreadsheet);

        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="sport_type_statistics.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    public function exportBotUsers(): StreamedResponse
    {
        $botUsers = BotUser::all();

        $statistics = [];

        foreach ($botUsers as $user) {
            $statistics[] = [
                'id' => $user->id ?? '-',
                'chat_id' => $user->chat_id ?? '-',
                'first_name' => $user->first_name ?? '-',
                'second_name' => $user->second_name ?? '-',
                'uname' => $user->uname ?? '-',
                'typed_name' => $user->typed_name ?? '-',
                'phone' => $user->phone ?? '-',
                'sms_code' => $user->sms_code ?? '-',
                'step' => $user->step ?? '-',
                __('user.bot_user_created_at') => $user->created_at ?? '-',
            ];
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = [
            'id',
            'chat_id',
            'first_name',
            'second_name',
            'uname',
            'typed_name',
            'phone',
            'sms_code',
            'step',
            __('user.bot_user_created_at'),
        ];

        $columnLetters = range('A', 'J');
        foreach ($columnLetters as $index => $letter) {
            $sheet->setCellValue($letter . '1', $headers[$index]);
            $sheet->getStyle($letter . '1')->getFont()->setBold(true); // Make header bold
        }

        // Add data to cells
        $row = 2;
        foreach ($statistics as $data) {
            $sheet->setCellValue('A' . $row, $data['id']);
            $sheet->setCellValue('B' . $row, $data['chat_id']);
            $sheet->setCellValue('C' . $row, $data['first_name']);
            $sheet->setCellValue('D' . $row, $data['second_name']);
            $sheet->setCellValue('E' . $row, $data['uname']);
            $sheet->setCellValue('F' . $row, $data['typed_name']);
            $sheet->setCellValue('G' . $row, $data['phone']);
            $sheet->setCellValue('H' . $row, $data['sms_code']);
            $sheet->setCellValue('I' . $row, $data['step']);
            $sheet->setCellValue('J' . $row, $data[__('user.bot_user_created_at')]);
            $row++;
        }

        // Auto resize columns
        foreach ($columnLetters as $letter) {
            $sheet->getColumnDimension($letter)->setAutoSize(true);
        }


        // Create writer and set the output stream
        $writer = new Xlsx($spreadsheet);

        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="bot_user_statistics.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}
