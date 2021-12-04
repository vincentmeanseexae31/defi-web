<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Users;

class ValidateUserLocked
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
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        if ($user) {
            if ($user->status == 1) {
                return response()->json(['code' => 500, 'msg' => '账号冻结中,不能进行此操作']);
            }
        } else {
            return response()->json(['code'=>403,'msg'=>'请登录']);
        }
        return $next($request);
    }
}