<?php

namespace App\Services\Calendar;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use App\Models\CalendarEvent;
use App\Traits\GlobalTrait;

class CalendarService
{
  /**
   * @var GlobalTrait
   */
  use GlobalTrait;

  /**
   * RoleService index
   * @param  Request $request
   * @return Response
   */
  public function index($request): Response
  {
    $records = CalendarEvent::orderBy('start')
      ->when(isset($request->date), function ($q) use ($request) {
          $q->where('date', $request->date);
      })
      ->get();

    return response([
      'records' => $records
    ]);
  }

  public function store($request): Response
  {
    $validator = Validator::make($request->all(), [
      'date'  => 'required',
      'event' => 'sometimes',
      'start' => 'required',
      'end'   => 'required',
      'override' => 'sometimes'
    ]);

    if ($validator->fails()) {
      return response([
        'errors' => $validator->errors()->all()
      ], 400);
    }

    if(isset($request->override)) {
      CalendarEvent::where('date', $request->date)->delete();
    }

    $event = CalendarEvent::create([
      'date'  => $request->date,
      'event' => $request->event,
      'start' => $request->start,
      'end'   => $request->end,
    ]);

    return response([
      'record' => $event
    ]);
  }

  public function update($calendarEvent, $request): Response
  {
    $validator = Validator::make($request->all(), [
      'date'  => 'required',
      'event' => 'sometimes',
      'start' => 'required',
      'end'   => 'required',
    ]);

    if ($validator->fails()) {
      return response([
        'errors' => $validator->errors()->all()
      ], 400);
    }

    $calendarEvent = CalendarEvent::whereId($calendarEvent)->first();

    $calendarEvent->update([
      'date'  => $request->date,
      'event' => $request->event,
      'start' => $request->start,
      'end'   => $request->end,
    ]);

    return response([
      'record' => $calendarEvent
    ]);
  }

  public function show($calendarEvent): Response
  {
    $calendarEvent = CalendarEvent::whereId($calendarEvent)->first();
    return response([
      'record' => $calendarEvent
    ]);
  }

  public function destroy($calendarEvent): Response
  {
    $calendarEvent = CalendarEvent::whereId($calendarEvent)->first();
    $calendarEvent->delete();
    return response([
      'record' => $calendarEvent ? true : false
    ]);
  }
}
