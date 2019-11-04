<?php
use App\Setting;
use App\TopSetting;
use App\Item;

$set = Setting::first();

$imgUrl = '';

if($type == 'postSingle') {
    $imgUrl = $postRel->thumb_path;
}
elseif($type == 'single') {
    $imgUrl = Item::find($id)->main_img;
    //echo $id;
}


$valArs = [
'url' => Request::url(),
'title' => $metaTitle,
'description' => $metaDesc, /* trim($description) PHP_EOL */
'image' => asset(Storage::url($imgUrl)), /* config('app.url') . Storage::url($obj->thumb_path)*/
'type' => $type == 'top' ? 'website' : 'article',
'site_name' => config('app.name'),
];

$ogArs = [
'og',
'twitter',
'fb'
];

$optionArs = [
'url',
'title',
'description',
'image',
'type',
'site_name'
];

?>



<meta property="fb:app_id" content="{{ $set->fb_app_id }}" />{{-- 15文字の半角数字 --}}

<meta name="twitter:card" content="summary_large_image" /><?php /* image or summary_large_image*/ ?>

<meta name="twitter:site" content="{{ '@' . $set->twitter_id }}" /><?php /* shop8463 */ ?>



@foreach($ogArs as $ogAr)
	@foreach($optionArs as $optionAr)
		<meta property="{{ $ogAr }}:{{ $optionAr }}" content="{{ $valArs[$optionAr] }}" /> 
	@endforeach
@endforeach

{{--
<meta property="{{ $og }}:url" content="{{ $url }}" /> 
<meta property="{{ $og }}:title" content="{{ $title }}" />
<meta property="{{ $og }}:description" content="{{ $description }}" />
<meta property="{{ $og }}:image" content="{{ $imgUrl }}" />
<meta property="{{ $og }}:type" content="{{ $ogType }}" />
<meta property="{{ $og }}:site_name" content="{{ config('app.name') }}" />
--}}
