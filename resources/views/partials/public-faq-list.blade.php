{{--
  $faqItems: iterable (id, title, content)
  $idPrefix: aria-controls / id 접두사 (페이지별 고유)
  $variant: 'main' | 'service' — 메인은 .faq_title 유지
--}}
@php
    $variant = $variant ?? 'service';
@endphp
@forelse ($faqItems as $item)
	@php
		$conId = ($idPrefix ?? 'faq').'-con-'.$item->id;
	@endphp
	@if ($variant === 'main')
		<li><h3 class="faq_title"><button type="button" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="{{ $conId }}">{{ $item->title }}</button></h3>
			<div class="con" id="{{ $conId }}">{!! $item->content !!}</div>
		</li>
	@else
		<li>
			<h3><button type="button" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="{{ $conId }}">{{ $item->title }}</button></h3>
			<div class="con" id="{{ $conId }}">{!! $item->content !!}</div>
		</li>
	@endif
@empty
	<li class="faq_empty"><p class="tb">등록된 자주 묻는 질문이 없습니다.</p></li>
@endforelse
