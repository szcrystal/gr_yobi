<?php
//use App\Setting;
use App\Item;


$width = isset($width) ? $width : 90;

?>

@if(isset($item->main_img) && $item->main_img != '')
    <img src="{{ Storage::url($item->main_img) }}" alt="{{ $item->title }}" class="img-fluid" width="{{ $width }}">

@elseif($item->pot_type == 3)
    <?php 
    	$parent = Item::find($item->pot_parent_id);
    ?>
    @if(isset($parent->main_img))
        <img src="{{ Storage::url($parent->main_img) }}" alt="{{ $item->title }}" class="img-fluid" width="{{ $width }}">
    @else
        <span class="no-img mr-2"><small>No Image</small></span>
    @endif
@else
    <span class="no-img mr-2"><small>No Image</small></span>
@endif


