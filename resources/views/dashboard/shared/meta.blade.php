<?php

$isPost = isset($type) && $type == 'post';

$isItem = isset($isItem) ? 1 : 0;

if(isset($isItem)) {
    $nameFormat = 'cont[%s]';
    $oldFormat = 'cont.%s';
}
else {
    $nameFormat = '%s';
    $oldFormat = '%s';
}

?>

<fieldset class="form-group{{ $errors->has(sprintf($oldFormat, 'meta_title')) ? ' has-error' : '' }}">
    <label for="meta_title" class="control-label">Meta Title
    @if($isPost)
    	<small class="ml-3">未入力の時は大タイトルがmeta titleになります</small>
    @endif
    </label>

    <input id="meta_title" type="text" class="form-control col-md-12" name="{{ sprintf($nameFormat, 'meta_title') }}" value="{{ Ctm::isOld() ? old(sprintf($oldFormat, 'meta_title')) : (isset($obj) ? $obj->meta_title : '') }}">

    @if ($errors->has(sprintf($oldFormat, 'meta_title')))
        <div class="text-danger">
            <span class="fa fa-exclamation form-control-feedback"></span>
            <span>{{ $errors->first(sprintf($oldFormat, 'meta_title')) }}</span>
        </div>
    @endif
</fieldset>

<fieldset class="form-group{{ $errors->has(sprintf($oldFormat, 'meta_description')) ? ' has-error' : '' }}">
    <label for="slug" class="control-label">Meta Description</label>

    <textarea id="meta_description" type="text" class="form-control col-md-12" name="{{ sprintf($nameFormat, 'meta_description') }}" rows="6">{{ Ctm::isOld() ? old(sprintf($oldFormat, 'meta_description')) : (isset($obj) ? $obj->meta_description : '') }}</textarea>

    @if ($errors->has(sprintf($oldFormat, 'meta_description')))
        <div class="text-danger">
            <span class="fa fa-exclamation form-control-feedback"></span>
            <span>{{ $errors->first(sprintf($oldFormat, 'meta_description')) }}</span>
        </div>
    @endif
</fieldset>

<fieldset class="form-group{{ $errors->has(sprintf($oldFormat, 'meta_keyword')) ? ' has-error' : '' }}">
    <label for="slug" class="control-label">Meta KeyWord<small class="ml-3">（,半角カンマで区切って下さい）</small></label>

    <input id="meta_keyword" type="text" class="form-control col-md-12" name="{{ sprintf($nameFormat, 'meta_keyword') }}" value="{{ Ctm::isOld() ? old(sprintf($oldFormat, 'meta_keyword')) : (isset($obj) ? $obj->meta_keyword : '') }}">

    @if ($errors->has(sprintf($oldFormat, 'meta_keyword')))
        <div class="text-danger">
            <span class="fa fa-exclamation form-control-feedback"></span>
            <span>{{ $errors->first(sprintf($oldFormat, 'meta_keyword')) }}</span>
        </div>
    @endif
</fieldset>
            
