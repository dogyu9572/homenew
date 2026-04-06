@php
    /** @var \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection $items */
    $items = $items ?? collect();
@endphp

@forelse($items as $item)
<li>
    @php
        $type = $item->category ?: ($item->categories[0] ?? '-');
        $thumb = !empty($item->thumbnail_image)
            ? \Illuminate\Support\Facades\Storage::url($item->thumbnail_image)
            : null;
    @endphp
    <a href="{{ $item->publicListHref() }}" class="box" @if($item->publicListOpensInNewTab()) target="_blank" rel="noopener noreferrer" @endif aria-label="{{ $item->title }} - {{ $type }} 포트폴리오 보기">
        @if($thumb)<span class="imgfit" aria-hidden="true"><img src="{{ $thumb }}" alt=""></span>@endif
        <span class="txt">
            <span class="type">{{ $type }}</span>
            <span class="name">{{ $item->title }}</span>
        </span>
    </a>
</li>
@empty
<li>
    <span class="box">
        <span class="txt">
            <span class="type">-</span>
            <span class="name">등록된 포트폴리오가 없습니다.</span>
        </span>
    </span>
</li>
@endforelse

