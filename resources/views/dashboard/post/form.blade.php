@extends('layouts.appDashBoard')

@section('content')

<?php 
use App\Category;

//    if($type == 'item') {
//        $name = $orgObj->title;
//        
//        $indexUrl = url('/dashboard/items');
//        $editUrl = url('/dashboard/items/'. $id);
//        
//        $linkId = $orgObj->is_potset ? $orgObj->pot_parent_id : $id;
//        
//        $pageUrl = url('/item/'. $linkId);
//    }
        
    $chunkNumArr = ['a'=>1/*, 'b'=>3, 'c'=>3*/];
?>

	
	<div class="text-left">
        <h1 class="Title">記事編集</h1>
        <p class="Description"></p>
    </div>

    <div class="row">
      <div class="col-md-12 mb-3">
        <div class="bs-component clearfix">
            <div class="mb-4">
                <a href="{{ url('dashboard/posts') }}" class="btn bg-white border border-round border-secondary text-primary"><i class="fa fa-angle-double-left" aria-hidden="true"></i> 一覧へ戻る</a>
                <br>

            </div>
    
            <div class="mt-4 text-right">
                <a href="" class="btn btn-warning border-round text-white" target="_brank">このページを見る <i class="fa fa-angle-double-right" aria-hidden="true"></i></a>
            </div>
        
    	</div>
    </div>
  </div>



    @if (count($errors) > 0)
        <div class="alert alert-danger">
            <strong>Error!!</strong> 追加できません<br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
        
	@if (session('status'))
        <div class="alert alert-success text-uppercase">
            {!! nl2br(session('status')) !!}
        </div>
    @endif
        
    <div style="min-height:900px;" class="col-lg-12 mb-5">
        <form class="form-horizontal" role="form" method="POST" action="/dashboard/posts" enctype="multipart/form-data">
        
        	<div class="form-group mb-0">
                <div class="clearfix mb-5">
                    <button type="submit" class="btn btn-primary btn-block mx-auto w-btn w-25">更　新</button>
                </div>
                
                @if(isset($orgObj))
                    <b class="text-big">[{{ $orgObj->id }}] {{ $name }}の上部コンテンツ</b>
                @endif
            </div>

            {{ csrf_field() }}
            

            <input type="hidden" name="edit_id" value="{{ $id }}">

			<div class="form-group clearfix">
                <div class="col-md-3 float-right mt-0">
                    
                    <div class="checkbox">
                        <label>
                            <?php
                                $checked = '';
                                if(Ctm::isOld()) {
                                    if(old('open_status'))
                                        $checked = ' checked';
                                }
                                else {
                                    if(isset($upper) && ! $upper->open_status) {
                                        $checked = ' checked';
                                    }
                                }
                            ?>
                            
                            <input type="checkbox" name="open_status" value="1"{{ $checked }}> この記事を表示しない
                        </label>
                    </div>
                    
                    <div class="checkbox">
                        <label>
                            <?php
                                $checked = '';
                                if(Ctm::isOld()) {
                                    if(old('is_index'))
                                        $checked = ' checked';
                                }
                                else {
                                    if(isset($upper) && ! $upper->is_index) {
                                        $checked = ' checked';
                                    }
                                }
                            ?>
                            
                            <input type="checkbox" name="is_index" value="1"{{ $checked }}> 目次を表示しない&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        </label>
                    </div>
                    
                </div>
            </div>
        
        	
            <span class="text-small text-secondary d-block mb-2">＊UPする画像のファイル名は全て半角英数字とハイフンのみで構成して下さい。(abc-123.jpg など)</span>
            
            
        
        	@foreach($relArr as $blockKey => $upperRel)
        
                <?php
                    $n = 0;
                    $midCount = 0;
                    
                    $retu = $chunkNumArr[$blockKey]
                    
                ?>
                
                @if(isset($upperRel['section'])) 
                    <p class="text-big p-0 m-0"><b>{{ $upperRel['section']->title }}</b></p>
                @endif
                
                <hr class="mt-3">
        		
                <h4 class="mt-5 mb-3 p-2 bg-secondary text-light text-uppercase block-tgl">記事ブロック</h4>
                
                <div class="block-all-wrap pt-2">
                    <fieldset class="mb-5 form-group">
                        <label class="text-uppercase">大タイトル（h1）<span class="text-danger text-big">*</span></label>
                        
                        <input class="form-control col-md-12{{ $errors->has('block.' .$blockKey. '.section.title') ? ' is-invalid' : '' }}" name="block[{{ $blockKey }}][section][title]" value="{{ Ctm::isOld() ? old('block.' .$blockKey. '.section.title') : (isset($upperRel['section']) ? $upperRel['section']->title : '') }}" placeholder="">

                            @if ($errors->has('block.' .$blockKey. '.section.title'))
                                <div class="text-danger">
                                    <span class="fa fa-exclamation form-control-feedback"></span>
                                    <span>{{ $errors->first('block.' .$blockKey. '.section.title') }}</span>
                                </div>
                            @endif
                        
                        <input type="hidden" name="block[{{ $blockKey }}][section][rel_id]" value="{{ isset($upperRel['section']) ? $upperRel['section']->id : 0 }}">
                    </fieldset>

                    
                    
                    @while($n < $blockCount[$blockKey])
                    
                    	@if(! ($n % $retu))
                        	
                            <?php //中タイトル部分
                            	$midOldName = 'block.' .$blockKey. '.mid_section.'. $midCount .'.title';
                            	$midSecRel = isset($upperRel['mid_section'][$midCount]) ? $upperRel['mid_section'][$midCount] : null;
                            ?>
                            
                            <fieldset class="mt-5 mb-4 form-group">
                                <label>中タイトル-{{ $midCount+1 }}（H2）</label>
                                <input class="form-control col-md-12{{ $errors->has($midOldName) ? ' is-invalid' : '' }}" name="block[{{ $blockKey }}][mid_section][{{ $midCount }}][title]" value="{{ Ctm::isOld() ? old($midOldName) : (isset($midSecRel) ? $midSecRel->title : '') }}" placeholder="">

                                    @if ($errors->has($midOldName))
                                        <div class="text-danger">
                                            <span class="fa fa-exclamation form-control-feedback"></span>
                                            <span>{{ $errors->first($midOldName) }}</span>
                                        </div>
                                    @endif
                                
                                <input type="hidden" name="block[{{ $blockKey }}][mid_section][{{ $midCount }}][rel_id]" value="{{ isset($midSecRel) ? $midSecRel->id : 0 }}">
                                
                                <?php
                                    $rId = isset($upperRel['mid_section'][$midCount]) ? $upperRel['mid_section'][$midCount]->id : 0;
                                ?>

                                @if(Ctm::isEnv('local'))
                                    <br>{{ $rId }} / {{ $midCount }}
                                @endif
                            </fieldset>
                            
                            <?php $midCount++; ?>
                            
                        @endif
                    

                        <div class="border border-gray p-3 mb-4 bg-gray rounded">
                            @include('dashboard.shared.upperContents')
                        </div>

                        <?php $n++; ?>
                    @endwhile
                    
                    
                    <div class="form-group mt-5 mb-5 pt-3">
                        <button type="submit" class="btn btn-primary btn-block w-btn w-25 mx-auto">更　新</button>
                    </div>
                </div>
            
            @endforeach
            
            
            <?php 
            //記事 END ================================== 
            ?>
            
            <hr class="mt-3">
            
            <fieldset class="form-group">
                
                <label>記事カテゴリー <span class="text-danger text-big cate-require">*</span></label>
                
                <select class="form-control select-first col-md-6{{ $errors->has('cate_id') ? ' is-invalid' : '' }}" name="cate_id">
                    <option disabled selected>選択して下さい</option>
                    @foreach($cates as $cate)
                        <?php
                            $selected = '';
                            if(Ctm::isOld()) {
                                if(old('cate_id') == $cate->id)
                                    $selected = ' selected';
                            }
                            else {
                                if(isset($item) && $item->cate_id == $cate->id) {
                                    $selected = ' selected';
                                }
                            }
                        ?>
                        <option value="{{ $cate->id }}"{{ $selected }}>{{ $cate->name }}</option>
                    @endforeach
                </select>
                <span class="text-warning"></span>
                
                @if ($errors->has('cate_id'))
                    <div class="help-block text-danger">
                    	<span class="fa fa-exclamation form-control-feedback"></span>
                        <span>{{ $errors->first('cate_id') }}</span>
                    </div>
                @endif
                
            </fieldset>
            
            
            <div class="clearfix tag-wrap">
                <div class="tag-group form-group{{ $errors->has('tag-group') ? ' is-invalid' : '' }}">
                    <label for="tag-group" class="control-label">タグ</label>
                    
                    <div class="clearfix">
                        <input id="tag-group" type="text" class="form-control col-md-5 tag-control" name="input-tag-group" value="" autocomplete="off" placeholder="Enter tag">

                        <div class="add-btn" tabindex="0">追加</div>

                        <span style="display:none;">{{ implode(',', $allTags) }}</span>

                        <div class="tag-area">
                            @if(count(old()) > 0)
                                <?php
                                    //$tagNames = old($tag->slug);
                                    $tagNames = old('tags');
                                ?>
                            @endif

                            @if(isset($tagNames))
                                @foreach($tagNames as $name)
                                <span><em>{{ $name }}</em><i class="fa fa-times del-tag" aria-hidden="true"></i></span>
                                <input type="hidden" name="tags[]" value="{{ $name }}">
                                @endforeach
                            @endif

                        </div>
                    </div>

                </div>
            </div><?php //tagwrap ?>
            
            
            @include('dashboard.shared.meta')
            
            
            {{--
            <h4 class="mt-5 mb-3 p-2 bg-secondary text-light text-uppercase block-tgl">サムネイル紹介コメント</h4>
            
            <div class="block-all-wrap pt-2">
                <fieldset class="my-3 form-group">
                    <label>タイトル</label>
                    <input class="form-control col-md-12{{ $errors->has('upper_title') ? ' is-invalid' : '' }}" name="upper_title" value="{{ Ctm::isOld() ? old(upper_title) : (isset($orgObj) ? $orgObj->upper_title : '') }}" placeholder="">

                    @if ($errors->has('upper_title'))
                        <div class="text-danger">
                            <span class="fa fa-exclamation form-control-feedback"></span>
                            <span>{{ $errors->first('upper_title') }}</span>
                        </div>
                    @endif
                </fieldset>
                
                <fieldset class="my-3 form-group">
                    <label class="control-label">コメント</label>
                    <textarea class="form-control{{ $errors->has('upper_text') ? ' is-invalid' : '' }}" name="upper_text" rows="10">{{ Ctm::isOld() ? old('upper_text') : (isset($orgObj) ? $orgObj->upper_text : '') }}</textarea>

                    @if ($errors->has('upper_text'))
                        <span class="help-block">
                            <strong>{{ $errors->first('upper_text') }}</strong>
                        </span>
                    @endif
                </fieldset>
                
                <div class="form-group mt-5 mb-5 pt-3">
                    <button type="submit" class="btn btn-primary btn-block w-btn w-25 mx-auto">更　新</button>
                </div>
            </div>
            --}}
            
            <?php 
            //サムネイル上部 END upper_title upper_text ================================== 
            ?>
        
        </form>

    </div>

    

@endsection
