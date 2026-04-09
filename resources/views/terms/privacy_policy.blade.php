@extends('layouts.app')
@section('title', $sName)
@section('sName', $sName)
@section('description', '홈페이지코리아의 개인정보처리방침을 안내합니다.')

@section('content')
<main class="sub_contents_wrap terms_wrap">

	<section class="terms" aria-labelledby="terms-title">
		<h1 id="terms-title">개인정보처리방침</h1>
	</section>
	
	@include('terms.txt_privacy')

</main>
@endsection

@push('scripts')
<script src="{{ asset('js/portfolio-index.js') }}"></script>
@endpush