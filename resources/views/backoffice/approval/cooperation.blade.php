@extends('backoffice.layouts.app')

@section('title', $boxTitle)

@section('styles')
<link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/approval.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('js/backoffice/approval.js') }}"></script>
@endsection

@section('content')
@include('backoffice.approval._box-content')
@endsection

