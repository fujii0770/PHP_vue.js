<?php


namespace App\Http\Middleware;
use Closure;

class CheckBrowserReferer
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
        $referer = $request->headers->get('referer');
        if(stripos($referer,'https://www.google.com') !== false || stripos($referer,'https://www.bing.com/') !== false ||
            stripos($referer,'https://search.yahoo.com') !== false || stripos($referer,'https://www.baidu.com') !== false ||
            stripos($referer,'https://www.ask.com') !== false || stripos($referer,'https://www.aolsearch.com') !== false ||
            stripos($referer,'https://duckduckgo.com') !== false || stripos($referer,'https://www.wolframalpha.com') !== false ||
            stripos($referer,'https://yandex.com') !== false || stripos($referer,'https://www.webcrawler.com') !== false ||
            stripos($referer,'https://www.naver.com') !== false || stripos($referer,'https://www.search.com') !== false||
            stripos($referer,'https://www.yahoo.com') !== false || stripos($referer,'https://search.aol.com') !== false){
            abort(403);
        }else{
            return $next($request);
        }
    }
}