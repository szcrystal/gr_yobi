<div class="pt-0">
    
    <?php
        $autoFocus = 'autofocus';
        $mainClass = 'col-md-12 mx-auto';
        $isCart = 0;
        
        if($pageType == 'cart') {
            $autoFocus = '';
            $mainClass = '';
            $isCart = 1;
        }
    ?>
                
    @if (count($errors->login) > 0)
        
        <div class="alert alert-danger text-small">
            <i class="far fa-exclamation-triangle"></i> 確認して下さい。
            <ul class="pl-3 pt-1 mb-0">
                @foreach ($errors->login->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    {{--
    <form method="POST" action="{{ route('login') }}">
        @csrf
    --}}

        <fieldset class="form-group {{ $mainClass }}">
            <label for="email" class="col-form-label text-small pb-0">メールアドレス</label>

            <div class="">
                <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" {{ $autoFocus }}>

                @if ($errors->has('email'))
                    <span class="invalid-feedback">
                        <span class="fa fa-exclamation form-control-feedback"></span>
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
                @endif
            </div>
        </fieldset>

        <fieldset class="form-group {{ $mainClass }}">
            <label for="password" class="col-form-label text-small pb-0">パスワード</label><!-- text-md-right -->

            <div class="">
                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" >

                @if ($errors->has('password'))
                    <span class="invalid-feedback">
                        <span class="fa fa-exclamation form-control-feedback"></span>
                        <strong>{{ $errors->first('password') }}</strong>
                    </span>
                @endif
            </div>
        </fieldset>

        @if(! $isCart)
        <fieldset class="form-group {{ $mainClass }}">
            <div class="col-md-8 mt-3">
                <div class="checkbox">
                    <input id="login-remember" type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="login-remember" class="checks">ログイン状態を保存する</label>
                </div>
            </div>
        </fieldset>
        @endif
        
        <fieldset class="form-group mt-3">
            <div class="mt-2">
                
                @if(Request::has('to_cart')) {{-- url ?to_cart=1 にセットされているto_cart --}}
                <input type="hidden" name="to_cart" value="1">
                @endif
                
                <input type="hidden" name="previous" value="{{ session('_previous.url') }}">
                
                {{-- <input type="submit" name="login1" value="ログイン" form="login" class="btn btn-custom btn-block rounded-0"> --}}
                <button type="submit" class="btn btn-custom btn-block rounded-0 py-2" name="loginBtn" value="1">ログイン</button>

            </div>
        </fieldset>
        
        @if(! $isCart)
        <div class="pt-2 text-right">
            <a class="w-100" href="{{ route('password.request') }}">
                パスワードをお忘れの方 <i class="fal fa-angle-double-right"></i>
            </a>
        </div>
        @endif
        
    {{--
    </form>
    --}}
    
</div>

