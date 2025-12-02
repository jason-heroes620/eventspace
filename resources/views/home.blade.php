@extends('layout')
@section('content')
<div class="container py-2">
    <h3> Welcome, {{ Auth::user()->name }}</h3>
</div>
<div class="container mx-auto row justify-content-evenly py-4">
    @foreach($applications as $app)

    @if ($app->status == 'A')
    <div class="col-8 col-md-3 border bg-success">
        <div class="row justify-content-center px-4 py-2 align-items-center">
            <div class="row justify-content-between w-full">
                <span class="col-10 text-left text-white py-2 bg-success">
                    <h6>APPROVED</h6>
                </span>
                <span class="col-2 text-center text-white py-2">
                    <h4>{{ $app->total }}</h4>
                </span>
            </div>
            {{-- <div class="row w-full justify-content-between">
                <span class="col-5 text-right text-white py-2">
                    <h6>Paid - {{ $paid }}</h6>
                </span>
                <span class="col-7 text-left text-white py-2">
                    <h6>Pending - {{ $pending }}</h6>
                </span>
            </div> --}}
        </div>
    </div>
    @elseif ($app->status == 'R')
    <div class="col-8 col-md-3 border bg-danger">
        <div class="row justify-content-center px-4 py-2 align-items-center">
            <span class="col-10 text-left text-white py-2 bg-danger">
                <h6>REJECTED</h6>
            </span>
            <span class="col-2 text-center text-white py-2">
                <h4>{{ $app->total }}</h4>
            </span>
        </div>
    </div>
    @elseif ($app->status == 'N')
    <div class="col-8 col-md-3 border bg-info">
        <div class="row justify-content-center px-4 py-2 align-items-center">
            <span class="col-10 text-left text-white py-2 bg-info">
                <h6>NEW</h6>
            </span>
            <span class="col-2 text-center text-white py-2">
                <h4>{{ $app->total }}</h4>
            </span>
        </div>
    </div>
    @endif
    @endforeach
</div>
@endsection