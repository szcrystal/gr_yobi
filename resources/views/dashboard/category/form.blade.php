@extends('layouts.appDashBoard')

@section('content')
	
	<div class="text-left">
        <h1 class="Title">
	@if(isset($edit))
    親カテゴリー編集
	@else
	親カテゴリー新規追加
    @endif
    </h1>
    <p class="Description"></p>
    </div>

    <div class="row">
      <div class="col-sm-12 col-md-6 col-lg-6 col-xl-5 mb-3">
        <div class="bs-component clearfix">
            <div class="">  
                <a href="{{ url('/dashboard/categories') }}" class="btn bg-white border border-1 border-secondary border-round text-primary"><i class="fa fa-angle-double-left" aria-hidden="true"></i>一覧へ戻る</a>
            </div>
            
            @if(isset($edit))
                <div class="mt-3 pt-4 mb-2">
                    <a href="{{ url('/dashboard/upper/'. $id. '?type=cate') }}" class="btn btn-success border-round text-white d-block float-left"><i class="fa fa-angle-double-left" aria-hidden="true"></i> 上部コンテンツを編集 </a>
                </div>
            @endif
        </div>
        
        
          
    </div>
  </div>

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
        <form class="form-horizontal" role="form" method="POST" action="/dashboard/categories" enctype="multipart/form-data">
        	
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary btn-block w-btn w-25 mx-auto">更　新</button>
        	</div>

            {{ csrf_field() }}
            
            @if(isset($edit))
                <input type="hidden" name="edit_id" value="{{$id}}">
            @endif
            
            <div class="form-group clearfix mt-5 pt-2 mb-5 thumb-wrap">
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
                                if(isset($cate) && $cate->del_mainimg) {
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
                        @if(count(old()) > 0)
                            @if(old('main_img') != '' && old('main_img'))
                                <img src="{{ Storage::url(old('main_img')) }}" class="img-fluid">
                            @elseif(isset($cate) && $cate->main_img)
                                <img src="{{ Storage::url($cate->main_img) }}" class="img-fluid">
                            @else
                                <span class="no-img">No Image</span>
                            @endif
                        @elseif(isset($cate) && $cate->main_img)
                            <img src="{{ Storage::url($cate->main_img) }}" class="img-fluid">
                        @else
                            <span class="no-img">No Image</span>
                        @endif
                    </div>


                    <div class="float-left col-md-8 pl-3 pr-0">
                        <fieldset class="form-group{{ $errors->has('main_img') ? ' is-invalid' : '' }}">
                            <label for="main_img">サムネイル画像</label>
                            <input class="form-control-file thumb-file" id="main_img" type="file" name="main_img">
                        </fieldset>

                        @if ($errors->has('main_img'))
                            <span class="help-block text-danger">
                                <strong>{{ $errors->first('main_img') }}</strong>
                            </span>
                        @endif

                        <span class="text-small text-secondary">＊サムネイル画像は原則必要なものとなります。<br>削除後の未入力など注意して下さい。</span>

                    </div>
                </fieldset>
            </div>
            

            <fieldset class="form-group">
                <label for="name" class="control-label">カテゴリー名</label>

                    <input id="name" type="text" class="form-control col-md-12{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ Ctm::isOld() ? old('name') : (isset($cate) ? $cate->name : '') }}">

                @if ($errors->has('name'))
                <div class="text-danger">
                    <span class="fa fa-exclamation form-control-feedback"></span>
                    <span>{{ $errors->first('name') }}</span>
                </div>
                @endif
            </fieldset>
            
            <fieldset class="form-group">
                <label for="link_name" class="control-label">カテゴリーリンク名（メニュー用）</label>

                    <input id="link_name" type="text" class="form-control col-md-12{{ $errors->has('link_name') ? ' is-invalid' : '' }}" name="link_name" value="{{ Ctm::isOld() ? old('link_name') : (isset($cate) ? $cate->link_name : '') }}">

                @if ($errors->has('link_name'))
                <div class="text-danger">
                    <span class="fa fa-exclamation form-control-feedback"></span>
                    <span>{{ $errors->first('link_name') }}</span>
                </div>
                @endif
            </fieldset>


            <fieldset class="form-group">
                <label for="slug" class="control-label">スラッグ（半角英数字・ハイフンのみ）</label>

                    <input id="slug" type="text" class="form-control col-md-12{{ $errors->has('slug') ? ' is-invalid' : '' }}" name="slug" value="{{ Ctm::isOld() ? old('slug') : (isset($cate) ? $cate->slug : '') }}">

                @if ($errors->has('slug'))
                    <div class="text-danger">
                        <span class="fa fa-exclamation form-control-feedback"></span>
                        <span>{{ $errors->first('slug') }}</span>
                    </div>
                @endif
            </fieldset>
            
            <hr class="mt-5">
            
            <?php
            	$obj = null;
            	if(isset($cate)) $obj = $cate;
            ?>
            
            @include('dashboard.shared.topRecommend')
            
            <hr class="mb-5">
            
            @include('dashboard.shared.meta')
            
            {{--
            @include('dashboard.shared.contents')
            --}}

          <div class="form-group mt-5">
                <button type="submit" class="btn btn-primary btn-block w-btn w-25 mx-auto">更　新</button>
        	</div>

        </form>

    </div>

@endsection
