<?php
	$class = '';
	if(isset($type) && $type == 'single') {
    	$class = ' class="single-colop"';
    }
?>

<footer id="colop"{!! $class !!}>
	<div class="clearfix foot-wrap">

        <div class="foot-menu">
        	<div class="mb-2">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <b style="font-size: 1.1em;">{{ config('app.name', 'グリーンロケット') }}</b>
                </a>
                
                <span style="font-size: 2em; vertical-align:-3px">
                	<a href="https://twitter.com/shop8463" target="_brank"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.facebook.com/8463andgreenrocket/" target="_brank"><i class="fab fa-facebook"></i></a>
                </span>
                
                @if(Ctm::isLocal())
                	<a href="{{ url('shop/clear') }}">CLEAR</a>
                 	<a href="{{ url('shop/cart') }}">cart</a>   
                @endif
            </div>
        
        
        	<?php
            	extract(Ctm::getFixPage());
            ?>
            
			<ul>
                @if($fixNeeds)         
                    @foreach($fixNeeds as $fixNeed)
                    <li><a href="{{ url($fixNeed->slug) }}">
                        @if($fixNeed->sub_title != '')
                        <i class="fa fa-angle-right"></i> {{ $fixNeed->sub_title }}
                        @else
                        <i class="fa fa-angle-right"></i> {{ $fixNeed->title }}
                        @endif
                    </a></li>
                    @endforeach
                @endif 
                
                <li><a href="{{ url('contact') }}"><i class="fa fa-angle-right"></i> お問い合わせ</a></li>                 
            </ul>
            
            @if($fixOthers) 
            <aside>
            <ul>
            	@foreach($fixOthers as $fixOther)
				<li><a href="{{ url($fixOther->slug) }}">
					@if($fixOther->sub_title != '')
                    <i class="fa fa-angle-right"></i> {{ $fixOther->sub_title }}
                    @else
                    <i class="fa fa-angle-right"></i> {{ $fixOther->title }}
                    @endif
                </a></li>
				@endforeach
            </ul>
            </aside>
            @endif
        </div>

        <div class="foot-company">                
            <div class="foot-logo">
                <img src="{{ url('images/logo-name.png') }}">
                <img src="{{ url('images/logo-symbol.png') }}">
            </div>
                
             <address>
                <h5>八進緑産株式会社</h5>
                <table class="table">      
                    <tr>
                        <th>営業時間</th>
                        <td>8：00-17：00</td>
                    </tr>
                    <tr>
                        <th>定休日</th>
                        <td>日曜・祝日</td>
                    </tr>
                    <tr>
                        <th>TEL</th>
                        <td>0299-53-0030</td>
                    </tr>
                    <tr>
                        <th>MAIL</th>
                        <td><a href="mailto:info@green-rocket.jp">info@green-rocket.jp</a></td>
                        </tr>
                    <tr>
                        <th>所在地</th>
                        <td><span class="text-small">〒311-3406</span><br>
                        茨城県小美玉市下吉影1627-1</td>
                    </tr>
                </table>
                
              </address>
               <p>※圃場の見学には事前予約が必要です。<br>事前予約が無い場合、圃場見学ができないことがありますので、必ずメールや電話で予約のご連絡をお願いいたします。</p>   
        </div>
    
    </div>    
     
    <p><i class="fa fa-copyright" aria-hidden="true"></i> GREEN LOCKET</p>


</footer>

<span class="top_btn"><i class="fa fa-angle-up"></i></span>

<?php
    $getNow = '';
    //if(Ctm::isLocal())
    	$getNow = '?up=' . time();
?>

<!-- Scripts -->
{{-- integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" --}}
<script src="https://code.jquery.com/jquery-3.2.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

<script type="text/javascript" src="//jpostal-1006.appspot.com/jquery.jpostal.js"></script>
<script src="//code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script src="{{ asset('js/script.js' . $getNow) }}"></script>


