@if(isset($orgObj->upper_title) || isset($orgObj->upper_text))
    <div class="upper-introduce-wrap mb-4">
        @if(isset($orgObj->upper_title) && $orgObj->upper_title != '')
            <h3 class="upper-title">{{ $orgObj->upper_title }}</h3>
        @endif
        
        @if(isset($orgObj->upper_text) && $orgObj->upper_text != '')
            <p class="upper-text px-1 m-0">{!! nl2br($orgObj->upper_text) !!}</p>
        @endif
    
    </div>
@endif
