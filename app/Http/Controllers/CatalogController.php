<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Models\Event;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $r)
    {
        $v = $r->validate(['search' => 'nullable|string|max:100', 'city_id' => 'nullable|integer', 'category_id' => 'nullable|integer', 'date' => 'nullable|date_format:Y-m-d']);
        $query = Event::publiclyVisible()->withCount('positions')->with(['cityRecord', 'category', 'organizer.organizerProfile'])->orderBy('starts_at');
        if (! empty($v['search'])) {
            $query->where('title', 'like', '%'.$v['search'].'%');
        }
        foreach (['city_id', 'category_id'] as $f) {
            if (! empty($v[$f])) {
                $query->where($f, $v[$f]);
            }
        }
        if (! empty($v['date'])) {
            $date = CarbonImmutable::parse($v['date'], 'Asia/Jakarta');
            $query->where('starts_at', '<', $date->addDay()->utc())->where('ends_at', '>', $date->utc());
        }

        return view('events.index', ['events' => $query->paginate(12)->withQueryString(), 'cities' => City::where('is_active', true)->orderBy('name')->get(), 'categories' => Category::where('is_active', true)->orderBy('name')->get()]);
    }

    public function show(Event $event)
    {
        abort_unless(Event::publiclyVisible()->whereKey($event->id)->exists(), 404);

        return view('events.show', ['event' => $event->load(['positions.positionSkills.skill', 'positions.schedules', 'positions.requirements', 'cityRecord', 'category', 'organizer.organizerProfile', 'entitlement'])]);
    }
}
