@extends('layout')

@viteReactRefresh
@vite(['resources/js/app.tsx'])

@section('content')

<div class="p-6 bg-white border-b border-gray-200" id="root">
</div>
@endsection