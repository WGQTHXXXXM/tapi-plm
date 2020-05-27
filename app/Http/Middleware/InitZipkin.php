<?php

namespace App\Http\Middleware;

use Closure;
use App\ServiceFactory\ZipkinContext;
use Zipkin\Propagation\Map;

class InitZipkin
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
        $zc = resolve(ZipkinContext::class);
        $tracing = $zc::createTracing(env('APP_CONSUL_NAME'), gethostbyname(gethostname()));
        $zc->setTracing($tracing);
        $carrier = array_map(function ($header) {
            return $header[0];
        }, request()->header());
        $extractor = $tracing->getPropagation()->getExtractor(new Map());
        $extractedContext = $extractor($carrier);
        $tracer = $tracing->getTracer();

        $span = $tracer->nextSpan($extractedContext);
        $span->start();
        $span->setKind('SERVER');
        $span->setName(request()->path());
        $span->tag("http.method", request()->method());
        $span->tag("http.path", request()->path());
        $span->tag("Client Address", request()->getClientIp());

        $zc->setSpan($span);
        $zc->setTracer($tracer);
        $response = $next($request);
        $span->finish();
        $tracer->flush();
        return $response;
    }
}
