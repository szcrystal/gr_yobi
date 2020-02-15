<?php
use App\Setting;
use App\Item;
use App\Category;

$cartAllClass = Request::is('shop/*') ? 'cart-all' : '';

$getNow = '?up=';
$getNow .= Ctm::isEnv('product') ? str_replace ('.', '', config('app.app_version')) : time();

?>

@include('shared.header')

<body>

@if(isset(Setting::first()->btn_color_1) && Request::is('item/*'))
<style>
    .btn-mizuiro {
        background: #{!! Setting::first()->btn_color_1 !!};
    }
</style>
@endif

@if(isset(Setting::first()->btn_color_2) && Request::is('shop/*'))
<style>
    .btn-kon {
        background: #{!! Setting::first()->btn_color_2 !!};
    }
</style>
@endif

<div id="app" class="{{ $cartAllClass }}">
            
    @if(Ctm::isAgent('sp'))
        @include('shared.headNavSp')
    @else
        @include('shared.headNav')
    @endif

<div class="fix-wrap">
    
    {{-- @yield('belt') --}}
    
    <div class="container-wrap">
        
        <div class="tophead-wrap">
            @if( (isset($type) && $type == 'single' ) || Request::is('shop/*'))
                @if(! Ctm::isAgent('sp'))
                    @include('main.shared.news')
                @endif
            @else
                @include('main.shared.news')
            @endif
            
            @if(Ctm::isAgent('sp'))
                <div class="logos clearfix">
                    <h1>
                        <a href="{{ url('/') }}">
                            <img src="{{ url('images/logo-name.png') }}" alt="{{ config('app.name', 'グリーンロケット') }}">
                            <img src="{{ url('images/logo-symbol.png') }}" alt="{{ config('app.name', 'グリーンロケット') }}-ロゴマーク">
                        </a>
                    </h1>
                </div>
            @endif
            
            @if(isset($isTop) && $isTop)
                @include('main.shared.slider')
                {{-- @include('main.shared.carousel') --}}
            @endif
        </div>
        
        <div class="container">
            <?php $className = isset($className) ? $className : ''; ?>
            
            <div class="pb-4 wrap-all clearfix {{ $className }}"><!-- offset-md-1-->
                @yield('bread')
                @yield('content')
                @yield('leftbar')
            </div>
        </div>
    
    </div>

@include('shared.footer')

</div><!-- for sp-fix-wrap -->
</div><!-- id app -->

<?php
    //$getNow = ! Ctm::isEnv('product') ? '?up=' . time() : '';
    $isProduct = Setting::first()->is_product ? 1 : 0;
?>

<!-- Scripts -->
<script src="//code.jquery.com/jquery-3.2.1.min.js" crossorigin="anonymous"></script>
<script src="//code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

@if(Request::is('shop/form') || Request::is('register') || Request::is('*/register'))
<script type="text/javascript" src="//jpostal-1006.appspot.com/jquery.jpostal.js"></script>
@endif

@if(Request::is('shop/confirm'))
    @if($isProduct)
    	<script src="https://p01.mul-pay.jp/ext/js/token.js"></script>
    @else
    	<script src="https://pt01.mul-pay.jp/ext/js/token.js"></script>
    @endif
@endif

@if(Request::is('shop/cart') || Request::is('shop/form'))
    @if($isProduct)
        <script async="async" src='https://static-fe.payments-amazon.com/OffAmazonPayments/jp/lpa/js/Widgets.js'></script>
    @else
        <script async="async" src='https://static-fe.payments-amazon.com/OffAmazonPayments/jp/sandbox/lpa/js/Widgets.js'></script>
    @endif
@endif

@if(isset($isTop) || Request::is('item/*'))
<script type="text/javascript" src="{{ asset('cdn/slick.min.js') }}"></script>
{{-- <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script> --}}
<script>
    @if(Request::is('item/*'))
        
        $('.slider-single').slick({
            prevArrow: '<span class="s-prev"><i class="fal fa-angle-left"></i></span>',
            nextArrow: '<span class="s-next"><i class="fal fa-angle-right"></i></span>',
        });
        
    @else
    	<?php 
        	// $slideNum = Ctm::isAgent('sp') ? 3 : 7; //naviの画像個数 要：奇数 
//        	touchThreshold: 5,
//          speed: 250,
//          ease: 'linear',        
        ?>
        
        $('.slider-top').slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            dots: true,

            @if(! Ctm::isAgent('sp'))
            centerMode: true,
            variableWidth: true,
            @endif

            autoplay: true,
            autoplaySpeed: 7000,
            arrows: false,
            fade: false,
            pauseOnFocus: false,
            //asNavFor: '.slider-nav',
        });
    @endif
</script>
@endif

@if(! Ctm::isAgent('sp') && Request::is('item/*'))
<script src="{{ asset('cdn/lightbox.min.js') }}" type="text/javascript"></script>{{-- 2.10.0 --}}
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.10.0/js/lightbox.min.js" type="text/javascript"></script> --}}
<script>
    lightbox.option({
    	'fadeDuration': 400,
        'resizeDuration': 500,
    	'positionFromTop': 50,
        'wrapAround': true,
      	'showImageNumberLabel': false,
      	'maxWidth': 800,
    });
</script>
@endif

<script src="{{ asset('js/script.js' . $getNow) }}"></script>

@if(Request::is('shop/thankyou') && isset($saleRel) && count($saleObjs) > 0)
<script>
dataLayer = [{
'transactionId': "{{ $saleRel->order_number }}",
'transactionAffiliation': {{ $saleRel->id }},
'transactionTotal': {{ $saleRel->all_price }},
'transactionTax': 0,
'transactionShipping': 0,
'transactionProducts': [
@foreach($saleObjs as $saleObj)
<?php 
$item = Item::find($saleObj->item_id); 
$title = $item->title;
$cateName = '';

if($item->is_potset) {
	$parent = Item::find($item->pot_parent_id);
    $title = $parent->title . '-' . $title;
    $cateName = Category::find($parent->cate_id)->name;
}
else {
	$cateName = Category::find($item->cate_id)->name;
}
?>

{
'sku': "{{ $item->number }}",
'name': "{{ $title }}",
'category': "{{ $cateName }}",
'price': {{ $saleObj->total_price }},
'quantity': {{ $saleObj->item_count }},
},
@endforeach

]
}];
</script>
@endif

@if(isset(Setting::first()->analytics_code) && Setting::first()->analytics_code != '')
{!! Setting::first()->analytics_code !!}
@endif

</body>
</html>
