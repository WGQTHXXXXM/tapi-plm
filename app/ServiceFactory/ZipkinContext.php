<?php
namespace App\ServiceFactory;

use Zipkin\Endpoint;
use Zipkin\Samplers\BinarySampler;
use Zipkin\TracingBuilder;
use Zipkin\Reporters\Http\CurlFactory;
use Zipkin\Reporters\Http;
/**
 * Created by PhpStorm.
 * User: weijinlong
 * Date: 2019/3/21
 * Time: 7:04 PM
 */
class ZipkinContext
{
    public $span;

    public $tracer;

    public $tracing;

    /**
     * @return mixed
     */
    public function getSpan()
    {
        return $this->span;
    }

    /**
     * @return mixed
     */
    public function getTracer()
    {
        return $this->tracer;
    }

    /**
     * @param mixed $span
     */
    public function setSpan($span){
        $this->span = $span;
    }

    /**
     * @param mixed $tracer
     */
    public function setTracer($tracer)
    {
        $this->tracer = $tracer;
    }

    /**
     * @return mixed
     */
    public function getTracing()
    {
        return $this->tracing;
    }

    /**
     * @param mixed $tracing
     */
    public function setTracing($tracing)
    {
        $this->tracing = $tracing;
    }

    public static function createTracing($endpointName, $ipv4)
    {
        $endpoint = Endpoint::create($endpointName, $ipv4, null, 80);
        $cf = CurlFactory::create();
        $reporter = new Http($cf,['endpoint_url' => env('ZIPKIN_SERVICE_HOST').':'.env('ZIPKIN_SERVICE_PORT').'/api/v2/spans']);
        $sampler = BinarySampler::createAsAlwaysSample();
        $tracing = TracingBuilder::create()
            ->havingLocalEndpoint($endpoint)
            ->havingSampler($sampler)
            ->havingReporter($reporter)
            ->build();
        return $tracing;
    }
}