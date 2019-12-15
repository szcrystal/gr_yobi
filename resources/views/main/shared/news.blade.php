<?php
use App\TopSetting;
?>

<div class="clearfix">
    {!! TopSetting::get()->first()->contents !!}
</div>
    
    
