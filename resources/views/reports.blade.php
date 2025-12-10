@extends('layout')
@section('content')
<div class="container mt-2">
    <div class="py-4">
        <label for="Event" class="pe-2">Event Group</label>
        {{-- <select name="event" id="event" class="py-2">
            <option value="">Select Group</option>
            @if($eventGroupId)
            @php
            $all = ''
            @endphp
            @else
            @php
            $all = 'selected'
            @endphp
            @endif
            <option {{ $all }} value="{{ route('applications') }}">All</option>
            @foreach ($eventGroups as $eventGroup)

            @if($eventGroup->event_group_id == $eventGroupId)
            @php
            $selected = 'selected'
            @endphp
            @else
            @php
            $selected = ''
            @endphp
            @endif
            <option {{ $selected }} value="{{ route('event-applications', ['eventGroupId' => $eventGroup->event_group_id]) }}">{{ $eventGroup->event_group}}</option>
            @endforeach
        </select>  --}}
    </div>


    @endsection
