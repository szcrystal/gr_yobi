<div class="fixed-top">
<header class="site-header clearfix">

<?php
    use App\User;
    use App\Fix;
    
    //print_r($_SERVER);
?>
    
    @if($_SERVER['SERVER_ADDR'] == '153.121.92.178')
    <div class="site-description {{ Ctm::isEnv('alpha') ? 'text-success' : '' }}">
    @else
    <div class="site-description {{ Ctm::isEnv('alpha') ? 'text-danger' : '' }}">
    @endif
    	<p>植木買うならグリーンロケット：グリーンロケットは初めての植木、お庭づくりを全力で応援します。</p>
    </div>

	<div class="head-first clearfix">
        
        <div class="head-primary float-left clearfix">
            <h1 class="float-left">
                <a href="{{ url('/') }}">
                <img src="{{ url('images/logo-name.png') }}" alt="{{ config('app.name', 'グリーンロケット') }}">
                <img src="{{ url('images/logo-symbol.png') }}" alt="{{ config('app.name', 'グリーンロケット') }}-ロゴマーク">
                </a>
            </h1>
        
            <div class="clearfix s-form ml-2">
               <form class="my-1 my-lg-0" role="form" method="GET" action="{{ url('search') }}">
                   {{-- csrf_field() --}}

                   <input type="search" class="form-control rounded-0" name="s" placeholder="何かお探しですか？" value="{{ Request::has('s') ? Request::input('s') : '' }}">
                   <button class="btn-s"><i class="fa fa-search"></i></button>

               </form>
           </div>
        </div>
        
        <div class="head-navi">
            <ul class="clearfix">
                <li>
                    <?php $firstGuide = Fix::where('slug', 'first-guide')->first(); ?>
                    
                    @if(isset($firstGuide) && $firstGuide->open_status)
                        <a href="{{ url('first-guide') }}">初めての方へ</a>
                    @endif
                </li>
                
                <?php $favUrl = 'favorite'; ?>

                @if(! Auth::check())
                    <li><a href="{{ url('login') }}">ログイン</a></li>
                    
                    <form id="for-favorite" action="" method="POST" style="display: none;">
                        {{ csrf_field() }}
                    </form>
                    
                @else
                    <li><a href="{{ url('mypage') }}">マイページ</a></li>
                    
                    <li><a href="{{ url('/logout') }}"
                                    onclick="event.preventDefault();
                                             document.getElementById('logout-form').submit();">
                                    ログアウト
                            </a>

                            <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                                {{ csrf_field() }}
                            </form>
                    </li>
                    
                    <?php $favUrl = 'mypage/' . $favUrl; ?>
                    
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
                
                <li><a href="{{ url($favUrl) }}"><i class="fal fa-heart"></i></a></li>
                
                <li><a href="{{ url('shop/cart') }}"><i class="fal fa-shopping-cart"></i></a></li>
                <li><a href="{{ url('contact') }}"><i class="fal fa-envelope"></i></a></li>
                
           </ul>
        </div>
        
    </div>{{-- head-first --}}
  
</header>


@if(Ctm::isAgent('sp'))
    @include('shared.navSp')
@else
    @include('shared.navPc')
@endif

</div><!-- fix -->


