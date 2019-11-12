@extends('layouts.app')

@section('content')

<div id="main" class="clearfix login top-cont">
     
        <div class="">
            <h4 class="card-header">会員の方</h4>
			
            <p class="my-3 pb-1 mx-1">メールアドレスとパスワードを入力してログインして下さい。</p>
            
            <form method="POST" action="{{ route('login') }}">
                @csrf
                @include('main.shared.userLogin', ['pageType'=>'user'])
            </form>
            
            
        </div>
            
            
        <div class="mb-5 pb-5">
            <h4 class="card-header">会員登録がお済みでない方</h4>
			
            <p class="my-3 pb-2 mx-1">初めての方はこちらより会員登録をして下さい。<br>あらかじめ会員登録を済ませておくと、お買い物が便利になります。</p>
            
            <div class="col-md-7 mx-auto mt-4">
                <a href="{{ url('register') }}" class="btn btn-custom rounded-0 btn-block m-auto">新規会員登録</a>
            </div>
        </div>
            
            
            
	</div>
</div>

@endsection
