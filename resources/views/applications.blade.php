@extends('layout')
@section('content')
<div class="container mt-2">
    <div class="py-4">
        <label for="Event" class="pe-2">Event</label>
        <select name="event" id="event" class="py-2">
            <option value="">Select an event</option>
            @if($eventId)
            @php
            $all = ''
            @endphp
            @else
            @php
            $all = 'selected'
            @endphp
            @endif
            <option {{ $all }} value="{{ route('applications') }}">All</option>
            @foreach ($events as $event)

            @if($event->id == $eventId)
            @php
            $selected = 'selected'
            @endphp
            @else
            @php
            $selected = ''
            @endphp
            @endif
            <option {{ $selected }} value="{{ route('event-applications', ['eventId' => $event->id]) }}">{{ $event->event_name}}</option>
            @endforeach
        </select>
    </div>
    <table class="table table-striped table-condensed table-hover table-sm mb-5">
        <thead>
            <tr class="table-info">
                <th>No</th>
                <th scope="col">Shop/Brand Name/Educational Institute Name</th>
                <th scope="col">Contact Person</th>
                <th scope="col">Contact No.</th>
                <th scope="col">Email</th>
                <th scope="col">Date of Submission</th>
                <th scope="col">Code</th>
                <th scope="col">Status</th>
                <th scope="col">Payment</th>
                <th></th>
            </tr>
        </thead>
        <tbody class="small">
            @foreach($applications as $index => $data)
            <tr class="align-middle">
                <td>{{ $index + $applications->firstItem() }}</td>
                <td>{{ $data->organization }}</td>
                <td>{{ $data->contact_person }}</td>
                <td>{{ $data->contact_no }}</td>
                <td>{{ $data->email }}</td>
                <td>{{ date('d/m/Y H:i A', strtotime($data->created)) }}</td>
                <td>{{ $data->application_code }}</td>
                @if($data->status == 'N')
                <td class="text-center bg-info text-black">{{ $data->status }}</td>
                @elseif ($data->status == 'A')
                <td class="text-center bg-success text-white">{{ $data->status }}</td>
                @else
                <td class="text-center bg-danger text-white">{{ $data->status }}</td>
                @endif
                <td class="text-center">{{ $data->payment_status }}</td>
                <td><a class="btn btn-sm btn-warning" href="{{ route('application-detail', ['id' => $data->id, 'page' => request()->query('page'), 'event' => $eventId]) }}">view</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="container row col-6 col-md-3 col-lg-2">
        <div class="bg-info">
            <span class="text-black">N = New</span>
        </div>
        <div class="bg-success">
            <span class="text-white">A = Approved</span>
        </div>
        <div class="bg-danger">
            <span class="text-white"> R = Rejected</span>
        </div>
    </div>
    <div class="d-flex justify-content-center pagination">
        {{ $applications->links() }}
    </div>
</div>
</body>

<script>
    $('#event').on('change', function(e) {
        var link = $("option:selected", this).val();
        if (link) {
            location.href = link;
        }
    });
</script>

@endsection