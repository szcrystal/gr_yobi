<?php
//use App\Setting;
use App\Item;


$width = isset($width) ? $width : 90;

if($item->pot_type == 3) {
    $item = Item::find($item->pot_parent_id);
}
?>

@if(isset($item->main_img) && $item->main_img != '')
    <img src="{{ Storage::url($item->main_img) }}" alt="{{ $item->title }}" class="img-fluid" width="{{ $width }}">
@else
    <span class="no-img mr-2"><small>No Image</small></span>
@endif


