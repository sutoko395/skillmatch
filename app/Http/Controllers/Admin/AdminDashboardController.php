<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Models\Order;
use App\Models\Skill;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $totalVolunteers = User::where('role', 'volunteer')->count();
        $totalOrganizers = User::where('role', 'organizer')->count();
        $totalTransactions = Order::count();
        $totalEvents = Event::count();
        $totalSkills = Skill::count();
        $totalCategories = Category::count();
        $pendingEvents = Event::where('status', 'pending')->count();

        $firstDates = collect([
            User::min('created_at'),
            Order::min('created_at'),
        ])->filter()->map(fn ($date) => Carbon::parse($date));

        $firstDate = $firstDates->isEmpty()
            ? now()->startOfYear()
            : $firstDates->sort()->first();

        $currentYear = now()->year;
        $years = range($firstDate->year, $currentYear);
        rsort($years);

        $range = $request->input('range', 'year');

        if (!in_array($range, ['all', 'year', 'month'], true)) {
            $range = 'year';
        }

        $year = (int) $request->input('year', $currentYear);

        if (!in_array($year, $years, true)) {
            $year = $currentYear;
        }

        $monthInput = $request->input('month');
        $month = is_numeric($monthInput) && (int) $monthInput >= 1 && (int) $monthInput <= 12
            ? (int) $monthInput
            : null;

        if ($range === 'month' && $month === null) {
            $month = now()->month;
        }

        if ($range === 'all') {
            $startDate = $firstDate->copy()->startOfMonth();
            $endDate = now()->endOfMonth();

            $periods = collect();
            $cursor = $startDate->copy();

            while ($cursor <= $endDate) {
                $periods->push($cursor->copy());
                $cursor->addMonth();
            }

            $labels = $periods
                ->map(fn ($date) => $date->translatedFormat('M Y'))
                ->values();
        } elseif ($range === 'year') {
            $startDate = Carbon::create($year, 1, 1)->startOfDay();
            $endDate = Carbon::create($year, 12, 31)->endOfDay();

            $periods = collect(range(1, 12))->map(
                fn ($number) => Carbon::create($year, $number, 1)
            );

            $labels = $periods
                ->map(fn ($date) => $date->translatedFormat('M'))
                ->values();
        } else {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            $periods = collect(range(1, $startDate->daysInMonth));

            $labels = $periods->map(
                fn ($day) => str_pad((string) $day, 2, '0', STR_PAD_LEFT)
            )->values();
        }

        $monthlyCounts = function ($query) use ($range, $startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);

            if ($range === 'month') {
                return $query
                    ->selectRaw('DAY(created_at) as period, COUNT(*) as total')
                    ->groupByRaw('DAY(created_at)')
                    ->get()
                    ->keyBy(fn ($row) => (int) $row->period);
            }

            return $query
                ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as total')
                ->groupByRaw('YEAR(created_at), MONTH(created_at)')
                ->get()
                ->keyBy(fn ($row) => $row->year . '-' . $row->month);
        };

        $volunteerCounts = $monthlyCounts(
            User::where('role', 'volunteer')
        );

        $organizerCounts = $monthlyCounts(
            User::where('role', 'organizer')
        );

        $transactionCounts = $monthlyCounts(Order::query());

        $buildSeries = function ($counts) use ($periods, $range, $startDate) {
            return $periods->map(function ($period, $index) use ($counts, $range, $startDate) {
                $key = $range === 'month'
                    ? (int) $period
                    : ($range === 'year'
                        ? $startDate->year . '-' . ($index + 1)
                        : $period->year . '-' . $period->month);

                return (int) ($counts->get($key)?->total ?? 0);
            })->values();
        };

        $chartData = [
            'volunteers' => $buildSeries($volunteerCounts),
            'organizers' => $buildSeries($organizerCounts),
            'transactions' => $buildSeries($transactionCounts),
        ];

        return view('admin.dashboard', compact(
            'totalVolunteers',
            'totalOrganizers',
            'totalTransactions',
            'totalEvents',
            'totalSkills',
            'totalCategories',
            'pendingEvents',
            'labels',
            'chartData',
            'range',
            'year',
            'month',
            'years'
        ));
    }
}