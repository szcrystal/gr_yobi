<?php
use App\Setting;
use App\TopSetting;

$switch = 0;
$thisUrl = $switch ? 'http://emerald.wjg.jp/' : Request::url();

?>

<div class="clearfix text-center">
    <p class="text-big text-bold mb-2">この{{ $naming }}をシェアする</p>
    
    <div class="twitter social-btn">
    <a class="" href="https://twitter.com/share?url={{ $thisUrl }}&text={{ $title }}" target="_blank"><i class="fab fa-twitter"></i></a>
    </div>
    
    <div class="fb social-btn">
    <a class="" href="https://www.facebook.com/share.php?u={{ $thisUrl }}" onclick="window.open(this.href, 'FBwindow', 'width=650, height=450, menubar=no, toolbar=no, scrollbars=yes'); return false;"><i class="fab fa-facebook-f"></i></a>
    </div>
    
    <div class="line social-btn">
    <a class="" href="https://line.me/R/msg/text/?{{ $thisUrl }}"><img src="{{ asset('/images/social/round-default.png') }}" class=""></a>
    </div>
    
</div>

