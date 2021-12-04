<?php


namespace App\Http\Middleware;

use App\Models\AccessKey;
use App\Models\Users;
use App\Models\Token;
use Closure;
use Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CheckTradeApi
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
        $data=$request->input();
        $key_id = $request->input('key_id', '');

        $secret= AccessKey::where('key_id',$key_id)->where('status',1)->first()->key_secret;
        if (empty($secret)) {
            return response()->json(['type' => '900', 'message' => 'key_secret is not authenticated']);
        }
        $sign=$request->input('sign','');
        if (empty($sign)){
            return response()->json(['type' => '900', 'message' => 'the sign is not exist']);
        }
        $timestamp=$request->input('timestamp','');
        if (empty($timestamp)){
            return response()->json(['type' => '900', 'message' => 'the sign is not exist']);
        }
        if(time()-$timestamp>600){
            return response()->json(['type' => '900', 'message' => 'the timestamp is overtime']);
        }
        $nonce=$request->input('nonce');
        if (!empty(Cache::get('nonce:'.$nonce))){
            return response()->json(['type' => '900', 'message' => 'repeat request']);
        }
        $sign=$data['sign'];
        unset($data['XDEBUG_SESSION_START']);
        unset($data['sign']);
        ksort($data);
        $params=http_build_query($data);
        $params= urldecode($params);
        $stringSignTemp=$params . '&SecretKey=' . $secret;
        $sign2=strtoupper(md5($stringSignTemp));
        if($sign!=$sign2) {
            return response()->json(['type' => '900', 'message' => 'sign验证失败']);
        }
        Cache::put('nonce:'.$nonce,$nonce,600);

        return $next($request);
    }
}