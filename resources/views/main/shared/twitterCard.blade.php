<?php
use App\Setting;
use App\TopSetting;


$url = Request::url();
$title = $type == 'postSingle' ? $obj->big_title : $obj->title;
$description = $obj->meta_description;
$description = str_replace("\r\n", "", $description)/*trim($description) PHP_EOL */;
//;
$imgUrl = config('app.url') . Storage::url($obj->thumb_path);

$ogType = $type == 'postSingle' ? 'article' : 'website';

?>


<meta property="fb:app_id" content="App-ID" /> <?php /* 15文字の半角数字 */ ?>

<?php /* image or summary_large_image*/ ?>

<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:site" content="@szzs5" /> <?php /* shop8463 */ ?>

<meta property="og:url" content="{{ $url }}" /> 
<meta property="og:title" content="{{ $title }}" />
<meta property="og:description" content="{{ $description }}" />
<meta property="og:image" content="{{ $imgUrl }}" />
<meta property="og:type" content="{{ $ogType }}" />
<meta property="og:site_name" content="{{ config('app.name') }}" />

