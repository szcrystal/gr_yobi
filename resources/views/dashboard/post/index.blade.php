@extends('layouts.appDashBoard')

@section('content')

<?php
use App\PostCategory;
use App\PostCategorySecond;
?>

    <div class="text-left">
		<h1 class="Title"> 記事一覧</h1>
		<p class="Description"></p>
    </div>


    <div class="row">

    </div>
    <!-- /.row -->


  
    {{--
    <div class="row -row-compact-sm -row-compact-md -row-compact-lg">
      <div class="col-sm-12 col-md-6 col-lg-6 col-xl-5 -sameheight">
        <div class="Card DashboardStats">
            <form role="form" method="POST" action="">
                <div class="form-group input-group">
                    <input type="text" class="form-control">
                    <span class="input-group-btn">
                        <button class="btn btn-secondary" type="button"><i class="fa fa-search"></i></button>
                    </span>
                </div>
            </form>
          </div>
    	</div>
  	</div>
    --}}


	<div class="row">
	@if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif
    </div>

    {{-- $itemObjs->links() --}}


    <!-- Example DataTables Card-->
    <div class="row">
    <div class="col-md-12">
    <div class="mb-3">
    	
        <div class="mb-5 text-right">
            <a href="{{url('dashboard/posts/create')}}" class="btn btn-info mr-2 px-3">新規追加</a>
            {{-- <a href="{{ url('dashboard/items/csv') }}" class="btn btn-light border border-secondary px-3">CSV DL</a> --}}
        </div>
        
        {{--
        <div class="mb-5">
        	<p class="mb-1">■ 最近の更新（5件）</p>
        	@foreach($recentObjs as $recent)
            	<a href="{{ url('dashboard/items/'. $recent->id) }}">（{{ $recent->id }}）[{{ $recent->number }}] {{ $recent->title }}</a><br>
            @endforeach
        
        </div>
        --}}
        
		{{--
		<div>
        	<span class="changeSearch">SEARCH</span>
        </div>
        --}}
        
        
        <div class="">
          <div class="table-responsive">
            <table id="dataTable" class="table table-striped table-bordered table-hover bg-white" width="100%" cellspacing="0">
            
              <thead>
                <tr>
                  <th style="min-width:2em;">ID</th>
                  <th style="min-width:6em;">大タイトル</th>
                  <th style="min-width:5em;">カテゴリー</th>
                  <th>メタ設定</th>
                  <th>View数</th>
                  <th>作成日</th>
                  <th></th>
                  <th></th>
                  
                </tr>
              </thead>

              
              <tbody>
              @foreach($postRels as $postRel)
                <tr>
                  <td>{{ $postRel->id }}</td>
                  
                  	<td>
                  		{{ $postRel->big_title }}<br>
                		
                        @if($postRel->open_status)
                            <span class="text-success">公開中</span><br>
                        @else
                            <span class="text-danger">非公開</span><br>
                        @endif
                    </td>
                  
                  <!--
                  <td>
                  @if($postRel->thumb_path != '')
                  <img src="{{ Storage::url($postRel->thumb_path) }}" width="70" height="60">
                  @else
                  <span class="no-img">No Image</span>
                  @endif
                  </td>
                  -->
                  
                  <td>
                  	@if(isset($postRel->cate_id))
                    	{{ PostCategory::find($postRel->cate_id)->name }}
                        
                        @if(isset($postRel->catesec_id))
                        <br><small>{{ PostCategorySecond::find($postRel->catesec_id)->name }}</small>
                        @endif
                    @endif
                </td>
                
                <td class="text-small">
                	<p class="m-0 p-0"><b>{{ $postRel->meta_title }}</b></p>
                    {{ $postRel->meta_description }}
                </td>
                
                <td>
                	{{ $postRel->view_count }}
                </td>
                
                <td>
                  	{{ Ctm::changeDate($postRel->created_at, 1) }}
                </td>
                  
                <td>
                  	<a href="{{url('dashboard/posts/'. $postRel->id)}}" class="btn btn-success btn-sm center-block">編集</a><br>
                  	<small class="text-secondary ml-1">ID{{ $postRel->id }}/</small>
                </td>
                
                <td></td>
                  

                </tr>
            @endforeach

              </tbody>
            </table>
          </div>
        </div>
        
        
        

        <!-- <div class="card-footer small text-muted"></div> -->

    </div><!-- /.card -->
    
    </div>
    	
        
    </div>
    
    {{-- $itemObjs->links() --}}

        
@endsection

