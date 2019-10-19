<?php
use App\Setting;
use App\TopSetting;

?>

<div class="clearfix">
    <div class="line social-btn">
    <a class="" href="https://line.me/R/msg/text/?{{ $title }}{{ Request::url() }}">LINEで送る</a>
    </div>
    
    <div class="fb social-btn">
    <a class="" href="https://www.facebook.com/share.php?u={{ Request::url() }}" onclick="window.open(this.href, 'FBwindow', 'width=650, height=450, menubar=no, toolbar=no, scrollbars=yes'); return false;"><i class="fab fa-facebook-f"></i> シェア</a>
    </div>
 
    <div class="twitter social-btn">
    <a class="" href="https://twitter.com/share?url={{ Request::url() }}&text={{ $title }}" target="_blank"><i class="fab fa-twitter"></i> ツイート</a>
    </div>
</div>

