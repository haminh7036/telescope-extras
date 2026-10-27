<?php

namespace Haminh7036\TelescopeExtras\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InjectAssets
{
    /**
     * Append the extras JS/CSS to the Telescope dashboard page.
     *
     * Only the dashboard route ("telescope") is touched, so API responses and
     * mail previews are returned exactly as Telescope produced them.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (! $request->routeIs('telescope') || ! $response instanceof Response) {
            return $response;
        }

        $content = $response->getContent();
        $position = strripos($content, '</body>');

        if ($position === false) {
            return $response;
        }

        return $response->setContent(substr_replace($content, $this->assets(), $position, 0));
    }

    /**
     * Get the inline asset tags.
     *
     * @return string
     */
    protected function assets()
    {
        $dist = __DIR__.'/../../../resources/dist';

        return '<style>'.file_get_contents($dist.'/telescope-extras.css').'</style>'
            .'<script>'.file_get_contents($dist.'/telescope-extras.js').'</script>';
    }
}
