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
        
    $chunkNumArr = ['p'=>1/*, 'b'=>3, 'c'=>3*/];
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
    
    		@if($edit)
                <div class="mt-4 text-right">
                    <a href="{{ url('post/'. $id) }}" class="btn btn-warning border-round text-white" target="_brank">このページを見る <i class="fa fa-angle-double-right" aria-hidden="true"></i></a>
                </div>
            @endif
        
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
            </div>

            {{ csrf_field() }}
            

            <input type="hidden" name="edit_id" value="{{ $id }}">

			<div class="form-group clearfix">
                <div class="col-md-3 float-right mt-0 mb-4 pr-0 pl-5">
                    
                    <div class="checkbox">
                        <label>
                            <?php
                                $openChecked = '';
                                if(Ctm::isOld()) {
                                    if(old('open_status'))
                                        $openChecked = ' checked';
                                }
                                else {
                                    if(isset($postRel) && ! $postRel->open_status) {
                                        $openChecked = ' checked';
                                    }
                                }
                            ?>
                            
                            <input type="checkbox" name="open_status" value="1"{{ $openChecked }}> この記事を表示しない
                        </label>
                    </div>
                    
                    <div class="checkbox">
                        <label>
                            <?php
                                $indexChecked = '';
                                if(Ctm::isOld()) {
                                    if(old('is_index'))
                                        $indexChecked = ' checked';
                                }
                                else {
                                    if(isset($postRel) && $postRel->is_index) {
                                        $indexChecked = ' checked';
                                    }
                                }
                            ?>
                            
                            <input type="checkbox" name="is_index" value="1"{{ $indexChecked }}> 目次を表示しない
                        </label>
                    </div>
                    
                </div>
            </div>
        
        	
            <span class="text-small text-secondary d-block mb-2">＊UPする画像のファイル名は全て半角英数字とハイフンのみで構成して下さい。(abc-123.jpg など。他各所画像も同様です。)</span>
            
            
            <div class="form-group clearfix mb-5 thumb-wrap">
                <fieldset class="w-25 float-right">
                    <div class="col-md-12 checkbox text-right px-0">
                        <label>
                            <?php
                                $checked = '';
                                if(Ctm::isOld()) {
                                    if(old('del_mainimg'))
                                        $checked = ' checked';
                                }
                                else {
                                    if(isset($postRel) && $postRel->del_mainimg) {
                                        $checked = ' checked';
                                    }
                                }
                            ?>

                            <input type="hidden" name="del_mainimg" value="0">
                            <input type="checkbox" name="del_mainimg" value="1"{{ $checked }}> この画像を削除
                        </label>
                    </div>
                </fieldset>
                
                <fieldset>
                    <div class="float-left col-md-4 px-0 thumb-prev">
                        @if(Ctm::isOld())
                            @if(old('thumb_path') != '' && old('thumb_path'))
                            	<img src="{{ Storage::url(old('thumb_path')) }}" class="img-fluid">
                            @elseif(isset($postRel) && $postRel->thumb_path)
                            	<img src="{{ Storage::url($postRel->thumb_path) }}" class="img-fluid">
                            @else
                            	<span class="no-img">No Image</span>
                            @endif
                        @elseif(isset($postRel) && $postRel->thumb_path)
                        	<img src="{{ Storage::url($postRel->thumb_path) }}" class="img-fluid">
                        @else
                        	<span class="no-img">No Image</span>
                        @endif
                    </div>
                    

                    <div class="float-left col-md-8 pl-3 pr-0">
                        <fieldset class="form-group{{ $errors->has('thumb_path') ? ' is-invalid' : '' }}">
                            <label for="main_img">記事サムネイル画像</label>
                            <input class="form-control-file thumb-file" id="thumb_path" type="file" name="thumb_path">
                        </fieldset>
                    
                        @if ($errors->has('thumb_path'))
                            <span class="help-block text-danger">
                                <strong>{{ $errors->first('thumb_path') }}</strong>
                            </span>
                        @endif
                        
                        <span class="text-small text-secondary">＊記事サムネイル画像は原則必要なものとなります。<br>削除後の未入力など注意して下さい。</span>
                    
                    </div>
                </fieldset>
                
            </div>
            
            
        
        	@foreach($relArr as $blockKey => $upperRel)
        
                <?php
                    $n = 0;
                    $midCount = 0;
                    
                    $retu = $chunkNumArr[$blockKey]
                    
                ?>
                
                <fieldset class="mb-5 form-group">
                    <label class="text-uppercase">大タイトル（h1）<span class="text-danger text-big">*</span></label>
                    
                    <input class="form-control col-md-12{{ $errors->has('block.' .$blockKey. '.section.title') ? ' is-invalid' : '' }}" name="block[{{ $blockKey }}][section][title]" value="{{ Ctm::isOld() ? old('block.' .$blockKey. '.section.title') : (isset($postRel) ? $postRel->big_title : '') }}" placeholder="">

                        @if ($errors->has('block.' .$blockKey. '.section.title'))
                            <div class="text-danger">
                                <span class="fa fa-exclamation form-control-feedback"></span>
                                <span>{{ $errors->first('block.' .$blockKey. '.section.title') }}</span>
                            </div>
                        @endif
                    
                    <input type="hidden" name="block[{{ $blockKey }}][section][rel_id]" value="{{ isset($upperRel['section']) ? $upperRel['section']->id : 0 }}">
                </fieldset>
                
                <hr class="mt-1">
        		
                <h5 class="mt-2 mb-2 p-2 bg-secondary border border-secondary text-light text-uppercase block-tgl">記事ブロック <i class="fa fa-angle-down"></i></h5>
                
                <div class="block-all-wrap pt-1">
                	
                    <span class="text-small">
                    	・1つ以上の中タイトルが入力されることが前提となります。<br>
                        ・入力された1つ目中タイトルから、次に入力される中タイトルまでが1つのアウトライン（段落）となり、目次内等で区分けされます。<br>
                        ・最後の中タイトルに対してブロック入力が1つもない場合、更新可能ですが警告が出ます。（タイトルだけ存在し内容がないというオモテにて不恰好な表示となります。）<br>
                        ・画像の横幅について
                    </span>
 
                    @while($n < $blockCount[$blockKey])
                    
                    	@if(! ($n % $retu))
                        	
                            <?php //中タイトル部分
                            	$midOldName = 'block.' .$blockKey. '.mid_section.'. $midCount .'.title';
                            	$midSecRel = isset($upperRel['mid_section'][$midCount]) ? $upperRel['mid_section'][$midCount] : null;
                            ?>
                            
                            <fieldset class="form-group mt-4 pt-2 mb-4">
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

                                @if(! Ctm::isEnv('local'))
                                    <br>{{ $rId }} / {{ $midCount }}
                                @endif
                            </fieldset>
                            
                            <?php $midCount++; ?>
                            
                        @endif
                    
                        <div class="border border-gray p-3 mb-5 bg-gray rounded">
                            @include('dashboard.shared.upperContents', ['type'=>'post'])
                        </div>

						<hr class="border-dotted border-secondary">
                        
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
            
            <fieldset class="form-group mt-5 pt-2">
                
                <label>記事カテゴリー <span class="text-danger text-big">*</span></label>
                
                <select class="form-control col-md-6{{ $errors->has('cate_id') ? ' is-invalid' : '' }}" name="cate_id">
                    <option disabled selected>選択して下さい</option>
                    
                    @foreach($postCates as $cate)
                        <?php
                            $selected = '';
                            if(Ctm::isOld()) {
                                if(old('cate_id') == $cate->id)
                                    $selected = ' selected';
                            }
                            else {
                                if(isset($postRel) && $postRel->cate_id == $cate->id) {
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
            
            
            @include('dashboard.shared.meta', ['obj'=> isset($postRel) ? $postRel : null, 'type'=>'post'])
            
            
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
            
            <div class="form-group mt-5 pt-3 mb-0">
                <div class="clearfix mb-0">
                    <button type="submit" class="btn btn-primary btn-block mx-auto w-btn w-25">更　新</button>
                </div>
            </div>
        
        </form>

    </div>

    

@endsection
