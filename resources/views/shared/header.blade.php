<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    @if(! Ctm::isEnv('product'))
    <meta name="robots" content="noindex, nofollow">
    @endif
    
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@if(isset($metaTitle)){{ $metaTitle }}@endif</title>
    @if(isset($metaDesc))
    <?php $metaDesc = str_replace("\r\n", '', $metaDesc); ?>
    
    <meta name="description" content="{{ $metaDesc }}">
    @endif
    @if(isset($metaKeyword))
    
    <meta name="keywords" content="{{ $metaKeyword }}">
    @endif
    
    <!-- Styles -->
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.11.2/css/all.css" integrity="sha384-zrnmn8R8KkWl12rAZFt4yKjxplaDaT7/EUkKm7AovijfrQItFWR7O/JJn4DAa/gx" crossorigin="anonymous">

    {{-- <link href="{{ asset('css/app.css') }}" rel="stylesheet"> --}}
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css">
    
	@if(isset($isTop) || Request::is('item/*'))
    {{-- <link href="{{ asset('cdn/slick-theme.min.css') }}" rel="stylesheet"> --}}{{-- 1.8.1 --}}
    {{-- <link href="{{ asset('cdn/slick.min.css') }}" rel="stylesheet"> --}}
    
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
	@endif
    
    @if(! Ctm::isAgent('sp') && Request::is('item/*'))
    {{-- <link href="{{ asset('cdn/lightbox.min.css') }}" rel="stylesheet"> --}}{{--  2.10.0 --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.10.0/css/lightbox.min.css" rel="stylesheet">
    @endif
    
    {{-- $getNowはlayout.app.blade内で --}}
    <link href="{{ asset('css/style.css'. $getNow) }}" rel="stylesheet">
    
    @if(Ctm::isAgent('all'))
    <link href="{{ asset('css/style-sp.css' . $getNow) }}" rel="stylesheet">
	@endif
	 
	@if(isset($type))
		@if($type == 'postSingle' || $type == 'single')
			@include('main.shared.twitterCard', [])
      	@endif                  
	@endif


    <!-- Scripts -->
    <script>
        window.Laravel = <?php echo json_encode([
            'csrfToken' => csrf_token(),
        ]); ?>

    </script>

</head>


<?php $switch = 0; ?>
@if(Ctm::isEnv('local') && $switch)
<div style="position: relative; bottom:0; z-index:10000; background:red; width: 100%;">
<?php 
print_r(session('item.data')); 
?>
</div>
@endif


