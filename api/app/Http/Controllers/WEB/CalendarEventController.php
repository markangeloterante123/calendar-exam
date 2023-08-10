<?php

namespace App\Http\Controllers\WEB;

use App\Models\CalendarEvent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Calendar\CalendarService;

class CalendarEventController extends Controller
{
    /**
     * @var CalendarService
     */
    protected $calendarService;

    /**
     * UserController constructor
     * @param CalendarService $calendarService
     */
    public function __construct(CalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->calendarService->index($request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return $this->calendarService->store($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CalendarEvent  $calendarEvent
     * @return \Illuminate\Http\Response
     */
    public function show($calendarEvent)
    {
        return $this->calendarService->show($calendarEvent);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CalendarEvent  $calendarEvent
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $calendarEvent)
    {
        return $this->calendarService->update($calendarEvent, $request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CalendarEvent  $calendarEvent
     * @return \Illuminate\Http\Response
     */
    public function destroy($calendarEvent)
    {
        return $this->calendarService->destroy($calendarEvent);
    }
}
