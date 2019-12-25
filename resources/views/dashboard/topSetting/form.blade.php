@extends('layouts.appDashBoard')

@section('content')
	
	<div class="text-left">
        <h1 class="Title">
        @if(isset($edit))
        TOPページ設定編集
        @else
        TOPページ設定編集
        @endif
        </h1>
        <p class="Description"></p>
    </div>

{{--
    <div class="row">
      <div class="col-sm-12 col-md-6 col-lg-6 col-xl-5 mb-5">
        <div class="bs-component clearfix">
        <div class="pull-left">
            <a href="{{ url('/dashboard/consignors') }}" class="btn bg-white border border-1 border-round border-secondary text-primary"><i class="fa fa-angle-double-left" aria-hidden="true"></i>一覧へ戻る</a>
        </div>
    	</div>
    </div>
  </div>
--}}



    @if (count($errors) > 0)
        <div class="alert alert-danger">
            <strong>Error!!</strong> 追加できません<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
        
	@if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif
        
    <div class="col-lg-12">
        <form class="form-horizontal" role="form" method="POST" action="/dashboard/settings/top-settings" enctype="multipart/form-data">
        	
         	<div class="form-group">
                <button type="submit" class="btn btn-primary d-block w-25 mt-5 mb-2 mx-auto"><span class="octicon octicon-sync"></span>更　新</button>
            </div>

            {{ csrf_field() }}
            
            @if(isset($edit))
                <input type="hidden" name="edit_id" value="{{$id}}">
            @endif
		
            
            <?php
            	$obj = null;
            	if(isset($setting)) $obj = $setting;
                
                $type = 'top';  
            ?>
            
            
            @include('dashboard.shared.contents')
            
            <hr class="mt-5 pt-2 mb-3">
            
            <fieldset class="pt-4 mb-5 form-group">
                <label>TOP人気検索ワード（,半角カンマで区切って下さい）</label>
                <input class="form-control{{ $errors->has('search_words') ? ' is-invalid' : '' }}" name="search_words" value="{{ Ctm::isOld() ? old('search_words') : (isset($setting) ? $setting->search_words : '') }}">

                @if ($errors->has('search_words'))
                    <div class="text-danger">
                        <span class="fa fa-exclamation form-control-feedback"></span>
                        <span>{{ $errors->first('search_words') }}</span>
                    </div>
                @endif
            </fieldset>
            
            <hr>
            
            <label class="mt-4"><i class="fa fa-square text-secondary"></i> TOP-Shopメタ設定</label>
            @include('dashboard.shared.meta')
            
            <label class="mt-4"><i class="fa fa-square text-secondary"></i> TOP-記事メタ設定</label>
            
            <fieldset class="form-group{{ $errors->has('post_meta_title') ? ' has-error' : '' }}">
                <label class="control-label">Meta Title</label>

                <input id="post_meta_title" type="text" class="form-control col-md-12" name="post_meta_title" value="{{ Ctm::isOld() ? old('post_meta_title') : (isset($setting) ? $setting->post_meta_title : '') }}">

                @if ($errors->has('post_meta_title'))
                    <div class="text-danger">
                        <span class="fa fa-exclamation form-control-feedback"></span>
                        <span>{{ $errors->first('post_meta_title') }}</span>
                    </div>
                @endif
            </fieldset>

            <fieldset class="form-group{{ $errors->has('post_meta_description') ? ' has-error' : '' }}">
                <label class="control-label">Meta Description</label>

                <textarea id="post_meta_description" type="text" class="form-control col-md-12" name="post_meta_description" rows="6">{{ Ctm::isOld() ? old('post_meta_description') : (isset($setting) ? $setting->post_meta_description : '') }}</textarea>

                @if ($errors->has('post_meta_description'))
                    <div class="text-danger">
                        <span class="fa fa-exclamation form-control-feedback"></span>
                        <span>{{ $errors->first('post_meta_description') }}</span>
                    </div>
                @endif
            </fieldset>

            <fieldset class="form-group{{ $errors->has('post_meta_keyword') ? ' has-error' : '' }}">
                <label class="control-label">Meta KeyWord<small class="ml-3">（,半角カンマで区切って下さい）</small></label>

                <input id="post_meta_keyword" type="text" class="form-control col-md-12" name="post_meta_keyword" value="{{ Ctm::isOld() ? old('post_meta_keyword') : (isset($setting) ? $setting->post_meta_keyword : '') }}">

                @if ($errors->has('post_meta_keyword'))
                    <div class="text-danger">
                        <span class="fa fa-exclamation form-control-feedback"></span>
                        <span>{{ $errors->first('post_meta_keyword') }}</span>
                    </div>
                @endif
            </fieldset>
            

            
            <div class="form-group mt-3">
                <button type="submit" class="btn btn-primary d-block w-25 mt-5 mx-auto"><span class="octicon octicon-sync"></span>更　新</button>
            </div>


            

        </form>

    </div>

    

@endsection
