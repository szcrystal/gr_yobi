@extends('layouts.appDashBoard')

@section('content')
	
	<div class="text-left">
        <h1 class="Title">
        @if(isset($edit))
        記事カテゴリー編集
        @else
        記事カテゴリー新規追加
        @endif
        </h1>
        <p class="Description"></p>
    </div>

    <div class="row">
      <div class="col-md-12 mb-3">
        <div class="bs-component clearfix">
            <div class="float-left w-25">  
                <a href="{{ url('/dashboard/post-categories') }}" class="btn bg-white border border-1 border-secondary border-round text-primary"><i class="fa fa-angle-double-left" aria-hidden="true"></i>一覧へ戻る</a>
            </div>
            
            <div class="float-right w-25 text-right pr-2">
                <a href="{{url('dashboard/post-categories/create')}}" class="btn btn-info">新規追加</a>
            </div>
            
            {{--
            @if(isset($edit))
                <div class="mt-3 pt-4 mb-2">
                    <a href="{{ url('/dashboard/upper/'. $id. '?type=cate') }}" class="btn btn-success border-round text-white d-block float-left"><i class="fa fa-angle-double-left" aria-hidden="true"></i> 上部コンテンツを編集 </a>
                </div>
            @endif
            --}}
            
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
        <form class="form-horizontal" role="form" method="POST" action="/dashboard/post-categories" enctype="multipart/form-data">
        	
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary btn-block w-btn w-25 mx-auto">更　新</button>
        	</div>

            {{ csrf_field() }}
            
            @if(isset($edit))
                <input type="hidden" name="edit_id" value="{{$id}}">
            @endif

            <fieldset class="form-group">
                <label for="name" class="control-label">記事カテゴリー名</label>

                    <input id="name" type="text" class="form-control col-md-12{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ Ctm::isOld() ? old('name') : (isset($cate) ? $cate->name : '') }}">

                @if ($errors->has('name'))
                <div class="text-danger">
                    <span class="fa fa-exclamation form-control-feedback"></span>
                    <span>{{ $errors->first('name') }}</span>
                </div>
                @endif
            </fieldset>
            
            <fieldset class="form-group">
                <label for="link_name" class="control-label">記事カテゴリーリンク名（メニュー用）</label>

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

            
            {{--
            @include('dashboard.shared.topRecommend', ['obj' => isset($cate) ? $cate : null])
            
            <hr class="mb-5">
            --}}
            
            @include('dashboard.shared.meta', ['obj' => isset($cate) ? $cate : null])
            
            {{--
            @include('dashboard.shared.contents')
            --}}

          <div class="form-group mt-5">
                <button type="submit" class="btn btn-primary btn-block w-btn w-25 mx-auto">更　新</button>
        	</div>

        </form>

    </div>

@endsection
