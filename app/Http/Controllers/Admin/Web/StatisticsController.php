<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\SportType;
use App\Models\Stadium;
use App\Models\Court;
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

        $statistics = [];

        foreach ($stadiums as $stadium) {
            $statistics[] = [
                'stadium' => $stadium,
                'statistic' => $this->statisticsRepository->stadiumStatistics($stadium),
            ];
        }
        $sportTypes = SportType::all();
        $ownerStadium = User::query()->whereDoesntHave('roles', function ($query) {
            $query->where('name', 'user')
                ->orWhere('name', 'trainer');
        })->get();
        return view('admin.statistics.stadiums', compact('statistics', 'sportTypes', 'ownerStadium'));
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

        $statistics = [];

        foreach ($courts as $court) {
            $statistics[] = [
                'court' => $court,
                'statistic' => $this->statisticsRepository->courtStatistics($court),
            ];
        }

        $sportTypes = SportType::all();
        $ownerStadium = User::query()->whereDoesntHave('roles', function ($query) {
            $query->where('name', 'user')
                ->orWhere('name', 'trainer');
        })->get();
        return view('admin.statistics.courts', compact('statistics', 'sportTypes', 'ownerStadium'));
    }

    public function sportType(Request $request): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $sportTypeId = $request->input('sport-type-id');

        if (Auth::user()->roles()->first()->name == 'owner stadium') {
            $sportTypes = Auth::user()->stadiumOwner()->first()->sportTypes()->get();
            $allSportTypes =  Auth::user()->stadiumOwner()->first()->sportTypes()->get();
        } else {
            $sportTypes = SportType::all();
            $allSportTypes = SportType::all();
        }


        if ($sportTypeId && $sportTypeId === 'all') {
            $sportTypes = SportType::with('courts.bookings')->get();
        }

        if ($sportTypeId && $sportTypeId !== 'all') {
            $sportTypes = SportType::with('courts.bookings')->where('id', $sportTypeId)->get();
        }

        $statistics = [];

        foreach ($sportTypes as $sportType) {
            $statistics[] = [
                'spotType' => $sportType,
                'statistic' => $this->statisticsRepository->sportTypeStatistics($sportType),
            ];
        }

        return view('admin.statistics.sport-types', compact('statistics', 'sportTypes', 'allSportTypes'));
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
        $sheet->setCellValue('A1', __('dashboard.metric'));
        $sheet->setCellValue('B1', __('dashboard.value'));

        // Add data to cells
        $row = 2;
        foreach ($statistics as $key => $value) {
            $sheet->setCellValue('A' . $row, $key);
            $sheet->setCellValue('B' . $row, is_array($value) ? json_encode($value) : $value);
            $row++;
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
            ];
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', __('stadium.stadium'));
        $sheet->setCellValue('B1', __('stadium.bot_hours'));
        $sheet->setCellValue('C1', __('stadium.manual_hours'));
        $sheet->setCellValue('D1', __('stadium.total_hours'));
        $sheet->setCellValue('E1', __('stadium.bot_revenue'));
        $sheet->setCellValue('F1', __('stadium.manual_revenue'));
        $sheet->setCellValue('G1', __('stadium.total_revenue'));

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
            $row++;
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
            ];
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', __('court.court'));
        $sheet->setCellValue('B1', __('court.bot_hours'));
        $sheet->setCellValue('C1', __('court.manual_hours'));
        $sheet->setCellValue('D1', __('court.total_hours'));
        $sheet->setCellValue('E1', __('court.bot_revenue'));
        $sheet->setCellValue('F1', __('court.manual_revenue'));
        $sheet->setCellValue('G1', __('court.total_revenue'));

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
            $row++;
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

    public function exportSportTypeStatistics(): StreamedResponse
    {
        $role = Auth::user()->roles()->first()->name;

        if (Auth::user()->roles()->first()->name == 'owner stadium') {
            $sportTypes = Auth::user()->stadiumOwner()->first()->sportTypes()->get();
        } else {
            $sportTypes = SportType::all();
        }
        $statistics = [];

        foreach ($sportTypes as $sportType) {
            $mostBookedTimeSlot = $this->statisticsRepository->sportTypeStatistics($sportType)['most_booked_time_slot'];
            $statistics[] = [
                'sport_type' => $sportType->name,
                'total_bookings' => $this->statisticsRepository->sportTypeStatistics($sportType)['total_bookings'],
                'total_revenue' => $this->statisticsRepository->sportTypeStatistics($sportType)['total_revenue'],
                'manual_revenue' => $this->statisticsRepository->sportTypeStatistics($sportType)['manual_revenue'],
                'bot_revenue' => $this->statisticsRepository->sportTypeStatistics($sportType)['bot_revenue'],
                'most_booked_date' => $this->statisticsRepository->sportTypeStatistics($sportType)['most_booked_date'] ?? '-',
                'most_booked_time_slot' => $mostBookedTimeSlot ? $mostBookedTimeSlot['start_time'].' - '.$mostBookedTimeSlot['end_time'] : '-',
            ];
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', __('sportType.name'));
        $sheet->setCellValue('B1', __('book.all_book'));
        $sheet->setCellValue('C1', __('court.total_revenue'));
        $sheet->setCellValue('D1', __('court.manual_revenue'));
        $sheet->setCellValue('E1', __('court.bot_revenue'));
        $sheet->setCellValue('F1', __('dashboard.Дата наибольшего бронирования'));
        $sheet->setCellValue('G1', __('dashboard.Cамый загруженный временной интервал'));

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
            $row++;
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
}
