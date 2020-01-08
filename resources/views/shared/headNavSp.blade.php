<?php
use App\Item;
use App\User;

//This is For SP. 
//PC -> headNav.php

?>


<div class="fixed-top">

<header class="site-header clearfix">

    <div class="s-form-wrap">
        <div class="clearfix s-form">
            <form class="" role="form" method="GET" action="{{ url('search') }}">
                {{-- csrf_field() --}}

                <input type="search" class="form-control rounded-0" name="s" value="{{ Request::has('s') ? Request::input('s') : '' }}" placeholder="何かお探しですか？">
                <button class="btn-s"><i class="fa fa-search"></i></button>
            </form>
        </div>
    </div>
    
    <div class="heart-tgl">
        @if(Auth::check())
            <a href="{{ url('mypage/favorite') }}">
        @else
            <a href="{{ url('favorite') }}">
        @endif
    	
        <i class="fal fa-heart"></i>
        </a>
	</div>
 
    <div class="nav-tgl">
        <i class="fal fa-bars"></i>
    </div>

</header>

{{--
<div class="logos clearfix">
    <h1>
        <a href="{{ url('/') }}">
            <img src="{{ url('images/logo-name.png') }}" alt="{{ config('app.name', 'グリーンロケット') }}">
            <img src="{{ url('images/logo-symbol.png') }}" alt="{{ config('app.name', 'グリーンロケット') }}-ロゴマーク">
        </a>
    </h1>
</div>
--}}
    

<div class="nav-sp-wrap">
    <nav>

        <?php
            use App\Category;
            use App\CategorySecond;
            $cates = Category::all();
        ?>
        
        <div class="nav-sp">
        	<p>グリーンロケットは初めての植木、お庭づくりを全力で応援します</p>
            
            <ul class="mt-3 pb-3">
                
                @if(Auth::check())
                    <li class="">
                        <a href="{{ url('mypage/favorite') }}">
                            <i class="fal fa-heart"></i> <b>お気に入り</b>
                        </a>
                    </li>
                    
                    <li><a href="{{ url('mypage') }}"><i class="fal fa-user"></i> <b>マイページ</b></a></li>
                    
                    <li>
                        <a href="{{ url('/logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fal fa-sign-out"></i> <b>ログアウト</b>
                        </a>

                        <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                            {{ csrf_field() }}
                        </form>
                    </li>
                @else
                    <li class="">
                        <a href="{{ url('favorite') }}">
                            <i class="fal fa-heart"></i> <b>お気に入り</b>
                        </a>
                    </li>
                    
                    <li class="">
                        <a href="{{ url('login') }}">
                            <i class="fal fa-sign-in"></i> <b>ログイン</b>
                        </a>
                    </li>
                
                @endif
            
            </ul>
            
            <ul class="mt-4 pt-1 bg-lightgray">
                <li class="has-child">
                    初めての方へ {{-- <i class="fal fa-caret-down" aria-hidden="true"></i> --}}
                    
                    <?php
                        extract(Ctm::getFixPage());
                    ?>
                
                    @if(count($fixOthers) > 0) 
                        <ul class="list-unstyled nav-child">
                            @foreach($fixOthers as $fixOther)
                                <li><a href="{{ url($fixOther->slug) }}">
                                    @if($fixOther->sub_title != '')
                                    {{ $fixOther->sub_title }}
                                    @else
                                    {{ $fixOther->title }}
                                    @endif
                                    
                                    {{--  <i class="fal fa-angle-double-right"></i> --}}
                                </a></li>
                            @endforeach
                        </ul>
                    @endif
                </li>
                
                <li class="has-child">
                    ご利用ガイド {{-- <i class="fal fa-caret-down" aria-hidden="true"></i> --}}
                    <ul class="list-unstyled nav-child">
                        
                        @if(count($fixNeeds) > 0)         
                            @foreach($fixNeeds as $fixNeed)
                            <li><a href="{{ url($fixNeed->slug) }}">
                                @if($fixNeed->sub_title != '')
                                    {{ $fixNeed->sub_title }}
                                @else
                                    {{ $fixNeed->title }}
                                @endif
                                
                                {{--  <i class="fal fa-angle-double-right"></i> --}}
                            </a></li>
                            @endforeach
                        @endif
                        
                        <li><a href="{{ url('contact') }}">お問い合わせ</a></li>
                    </ul>
                </li>
            </ul>
            
            <p class="menu-exp">カテゴリー</p>
            <ul class="">
                @foreach($cates as $cate)
                    <li class="">
                        <a href="{{ url('category/' . $cate->slug) }}">
                            {{ $cate->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

    </nav>
    </div>


</div><!-- fixed-top -->



{{--
<div class="head-navi">
    <p class="aniv">初めてでも安心！全品6ヶ月枯れ保証！3ヶ月取置き可能！</p>
</div>
--}}


<div class="icon-belt">
    <ul class="clearfix">
    	<li><a href="{{ url('/') }}"><i class="fal fa-home"></i></a></li>

        @if(! Auth::check())
            <li><a href="{{ url('login') }}"><i class="fal fa-sign-in"></i></a></li>
            <li><a href="{{ url('lookfor') }}"><i class="fal fa-search"></i></a></li>
            
            {{--
            <li><a href="{{ url('favorite') }}"><i class="fal fa-heart"></i></a></li>
            --}}
            
            <form id="for-favorite" action="" method="POST" style="display: none;">
                {{ csrf_field() }}
            </form>
        @else
            <li><a href="{{ url('mypage') }}"><i class="fal fa-user"></i></a></li>
            <li><a href="{{ url('lookfor') }}"><i class="fal fa-search"></i></a></li>
            
            {{--
            <li><a href="{{ url('mypage/favorite') }}"><i class="fal fa-heart"></i></a></li>
            --}}
            
            {{--
            <li class="dropdown show">
              <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-user"></i></a>

                  <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                    <span class="ml-3"><b>{{ User::find(Auth::id())->name }}</b> 様</span>
                    <a class="dropdown-item mt-1" href="{{ url('mypage') }}">マイページ <i class="fa fa-angle-right"></i></a>
                    <a class="dropdown-item mt-1" href="{{ url('mypage/history') }}">購入履歴 <i class="fa fa-angle-right"></i></a>
                    <a class="dropdown-item mt-1" href="{{ url('mypage/favorite') }}">お気に入り <i class="fa fa-angle-right"></i></a>
                    
                    
                    <a href="{{ url('/logout') }}" class="dropdown-item mt-1"
                            onclick="event.preventDefault();
                                     document.getElementById('logout-form').submit();">
                            ログアウト <i class="fa fa-angle-right"></i>
                    </a>

                    <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                        {{ csrf_field() }}
                    </form>
                  </div>
            </li>
            --}}
        @endif
        
        
        <li><a href="{{ url('shop/cart') }}"><i class="fal fa-shopping-cart"></i></a></li>
        <li><a href="{{ url('contact') }}"><i class="fal fa-envelope"></i></a></li>
        
        {{--
        @if(Auth::check())
        	<li><a href="{{ url('/logout') }}"
                            onclick="event.preventDefault();
                                     document.getElementById('logout-form').submit();">
                            <i class="fal fa-sign-out"></i>
                </a>

                <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                    {{ csrf_field() }}
                </form>
            </li>
        @endif
        --}}
        
   </ul> 
</div>


