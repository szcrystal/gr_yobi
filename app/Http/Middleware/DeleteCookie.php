<?php

namespace App\Http\Middleware;

use Closure;
use Cookie;

class DeleteCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //$_COOKIE = array();
//        print_r($_COOKIE);
//        exit;

        
        /*
        Array (
            [adminer_version] => 0
            [XSRF-TOKEN] => eyJpdiI6IjZMTWJLa2dLMlgzOTBhcXdzc09TdHc9PSIsInZhbHVlIjoibzQ3M3g2UDNOVWdmb0piNVFrb3pSNHMxYjByRFdlcHZFQXo5MEViZUVxUkhMVkFjRGpsNG1uZmRMXC9qRWJVSWQiLCJtYWMiOiI1NTQyNzczMDAzMmQ1NDFiOTRlMWMzOTE4ZDY1MGI1OTcyNjgxY2FjNTIyOGQwMTZhODJlZGJhMGYxY2VhYWFkIn0=
            [_session] => eyJpdiI6IitIRk9hbEZnZXVvZm84Z3FSQm9NdEE9PSIsInZhbHVlIjoiQUlnMWpyc1BpcXpiOFRreUJRNkdBNzdyY1dqd092aGJROUp3NDB3SXM4VWNHYmIzSDczcE5BK1V5VVRcLzdtS1IiLCJtYWMiOiIwNTU0YmJhYTEzZmZjYTgxMzBmMGJmMTkzNjBmZjdiODRjNjk3MzFkYmY2MjQ0OGEwMTkxOGY1YjJiZjk4MWM2In0=
        )
        */
        
        
        //Laravel5.6.30(vendorフォルダ)以上にUpdate(composer update)するとserialize()がなくなるので、エラーが出る
        //それまでのCookieの値（s:9:"1296, 1279"; <=このままの文字列で入っている）が元に戻せなくなりエラーが出る
        
        //逆パターンで、5.6.39から5.6.17へ下げると、unserialize(): Error at offset 0 of 40 bytes エラーになる => Bへ
        
        /* 下記は関係ない ★★★★★★
        vendorのバージョンを上げるならCookieに入れているitem_idsの実装を変えるしかない
        現在、id, の文字列にしているが、fav_key同様、keyにする。そしてitem_idsは消す。
        ★★★★★★
        */
        
        // vendor updateするなら下記を各所に実装する必要がある ================================
        $cookieIds = Cookie::get('item_ids');

        if(strpos($cookieIds, '"') !== false) { //cookieに、s:9:"1296, 1279"; <=このままの文字列で入っている
            $cookieIds = explode('"', $cookieIds);
            setcookie('item_ids', '', time()-60);
            Cookie::queue(Cookie::make('item_ids', $cookieIds[1], config('app.cookie_time')));
            //setcookie('item_ids', '', time()-60);
        }
        
        //fav_keyも古いバージョンではs:9:"の文字列が入る
        $favKey = Cookie::get('fav_key');
        if(strpos($favKey, '"') !== false) { //cookieに、s:9:"1296, 1279"; <=このままの文字列で入っている
            $favKey = explode('"', $favKey);
            
            setcookie('fav_key', '', time()-60);
            Cookie::queue(Cookie::make('fav_key', $favKey[1], config('app.cookie_time')));
            //setcookie('item_ids', '', time()-60);
        }
        
//        $favKey = $_COOKIE['fav_key'];
//        echo $cookieIds;
//        exit;


        
        // B ======================= kernel上では$middleware内（ \App\Http\Middleware\EncryptCookies::class,の前に記載する必要がある）
//        setcookie('adminer_version', '', time()-60);
//        setcookie('XSRF-TOKEN', '', time()-60);
//        setcookie('_session', '', time()-60);
//        setcookie('item_ids', '', time()-60);
//        setcookie('fav_key', '', time()-60);
        
        
        
        return $next($request);
    }
}
