<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Invite;

class InviteMiddleware
{
    /**
     * Метод обрабатывает входящий запрос
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //если в запросе не передан параметр 'code', перенаправляем пользователя
        if (!$request->has('code')) {
            return redirect(route('invtersOnly'));
        }
        $code = $request->input('code');
        //получаем инвайт - метод вернет объект только если он существует и еще не использован
        $invite = Invite::getInviteByCode($code);
        //если объект не получен, перенаправляем пользователя
        if (!$invite) {
            return redirect(route('invtersOnly'));
        }

        //все в порядке, продолжаем обработку запроса
        return $next($request);
    }
}