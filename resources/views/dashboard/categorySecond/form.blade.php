@extends('layouts.appDashBoard')

@section('content')
	
	<div class="text-left">
        <h1 class="Title">
	@if(isset($edit))
    子カテゴリー編集
	@else
	子カテゴリー新規追加
    @endif
    </h1>
    <p class="Description"></p>
    </div>

    <div class="row">
      <div class="col-sm-12 col-md-6 col-lg-6 col-xl-5 mb-5">
        <div class="bs-component clearfix">
        <div class="pull-left">
            <a href="{{ url('/dashboard/categories/sub') }}" class="btn bg-white border border-1 border-secondary border-round text-primary"><i class="fa fa-angle-double-left" aria-hidden="true"></i>一覧へ戻る</a>
        </div>
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
        
    <div class="col-lg-10">
        <form class="form-horizontal" role="form" method="POST" action="/dashboard/categories/sub">

            {{ csrf_field() }}
            
            @if(isset($edit))
                <input type="hidden" name="edit_id" value="{{$id}}">
            @endif
            
            <fieldset class="mb-4 form-group">
                <label>親カテゴリー</label>
                <select class="form-control col-md-6{{ $errors->has('parent_id') ? ' is-invalid' : '' }}" name="parent_id">
                    <option disabled selected>選択して下さい</option>
                    @foreach($cates as $cate)
                        <?php
                            $selected = '';
                            if(Ctm::isOld()) {
                                if(old('parent_id') == $cate->id)
                                    $selected = ' selected';
                            }
                            else {
                                if(isset($subCate) && $subCate->parent_id == $cate->id) {
                                    $selected = ' selected';
                                }
                            }
                        ?>
                        <option value="{{ $cate->id }}"{{ $selected }}>{{ $cate->name }}</option>
                    @endforeach
                </select>
                
                @if ($errors->has('parent_id'))
                    <div class="help-block text-danger">
                        <span class="fa fa-exclamation form-control-feedback"></span>
                        <span>{{ $errors->first('parent_id') }}</span>
                    </div>
                @endif
                
            </fieldset>

            <fieldset class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                <label for="name" class="control-label">カテゴリー名</label>

                <input id="name" type="text" class="form-control col-md-10" name="name" value="{{ Ctm::isOld() ? old('name') : (isset($subCate) ? $subCate->name : '') }}" required>

                @if ($errors->has('name'))
                <div class="text-danger">
                    <span class="fa fa-exclamation form-control-feedback"></span>
                    <span>{{ $errors->first('name') }}</span>
                </div>
                @endif
            </fieldset>


            <fieldset class="form-group{{ $errors->has('slug') ? ' has-error' : '' }}">
                <label for="slug" class="control-label">スラッグ</label>

                <input id="slug" type="text" class="form-control col-md-10" name="slug" value="{{ Ctm::isOld() ? old('slug') : (isset($subCate) ? $subCate->slug : '') }}" required>

                @if ($errors->has('slug'))
                    <div class="text-danger">
                        <span class="fa fa-exclamation form-control-feedback"></span>
                        <span>{{ $errors->first('slug') }}</span>
                    </div>
                @endif
            </fieldset>

          <div class="form-group mt-5">
            <div class="">
                <button type="submit" class="btn btn-primary btn-block w-btn w-25 mx-auto">更　新</button>
            </div>
        </div>

        </form>

    </div>

@endsection