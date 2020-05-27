<?php
namespace App\Models\Abstracts;

use Log;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Exceptions\RestApi\NotFoundRestApiException;
use App\ServiceFactory\ZipkinContext;
use Zipkin\Propagation\Map;

abstract class RestApiModel extends Model
{
    CONST PREFIX_PATH_PARAM = ':';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected static $keyNameInPath = 'id';

    protected static $apiTimeout = 3;

    protected static $defaultHeaders = [
        'Content-Type' => 'application/json',
    ];

    protected static $defaultResponseResolverClassName = JavaRestResponseResolver::class;
    protected static $responseResolverClassName = null;

    protected static $paginationKeyNames = [
        'request' => [
            'page'    => 'pageNo',
            'perPage' => 'pageSize',
        ],
        'response' => [
            'total'   => 'totalCount',
            'page'    => 'pageNo',
            'perPage' => 'pageSize',
            'data'    => 'result',
        ],
    ];

    /*
     * The definition map of apis.
     * format:
     * [
     *     'paginate' => ['method' => 'GET',    'path' => '/users'],
     *     'retrieve' => ['method' => 'GET',    'path' => '/users/:guid'],
     *     'create'   => ['method' => 'POST',   'path' => '/users/:guid'],
     *     'update'   => ['method' => 'PUT',    'path' => '/users/:guid'],
     *     'delete'   => ['method' => 'DELETE', 'path' => '/users/:guid'],
     * ]
     */
    protected static $apiMap = [];

    abstract protected static function getBaseUri();

    /**
     * Get an object.
     *
     * @param $name
     * @param array $queryParams
     * @param null $body
     * @param array $headers
     * @return null|static
     */
    public static function getItem($name, $queryParams = [], $body = null, $headers = [])
    {
        $response = static::callRemoteApi($name, $queryParams, $body, $headers);
        try {
            $data = static::getResponseData($response);
        } catch (NotFoundRestApiException $e) {
            return null;
        }

        $obj = new static();
        if($data){
            $obj->forceFill($data);
        }
        return $obj;
    }

    /**
     * Get a collection which may containt multiple models.
     *
     * @param $name
     * @param array $queryParams
     * @param null $body
     * @param array $headers
     * @return null|Illuminate\Support\Collection
     */
    public static function getCollection($name, $queryParams = [], $body = null, $headers = [])
    {
        $data = self::getData($name,$queryParams,$body,$headers);

        $jsonData = json_encode($data);
        $data = json_decode($jsonData);
        return collect($data);
    }

    /**
     *  Get a paginator.
     *
     * @param $name
     * @param array $queryParams
     * @param null $body
     * @param array $headers
     * @return null|Illuminate\Pagination\LengthAwarePaginator;
     */
    public static function getPaginator($name, $queryParams = [], $body = null, $headers = [])
    {
        if (!array_key_exists('page', $queryParams) || ($queryParams['page'] <= 0)) {
            $queryParams['page'] = 1;
        }
        if (!array_key_exists('per_page', $queryParams) || ($queryParams['per_page'] < 0)) {
            $queryParams['per_page'] = config('app.default_per_page');
        }
        // 按照Remote API的定义，修改page和per_page参数的名称
        if ('page' != static::$paginationKeyNames['request']['page']) {
            $queryParams[static::$paginationKeyNames['request']['page']] = $queryParams['page'];
            unset($queryParams['page']);
        }
        if ('per_page' != static::$paginationKeyNames['request']['perPage']) {
            $queryParams[static::$paginationKeyNames['request']['perPage']] = $queryParams['per_page'];
            unset($queryParams['per_page']);
        }

        $response = static::callRemoteApi($name, $queryParams, $body, $headers);

        try {
            $data = static::getResponseData($response);
        } catch (NotFoundRestApiException $e) {
            return null;
        }

        return static::makeResponsePaginator($data, $queryParams);
    }

    /**
     * Create or update
     *
     * @param $name
     * @param array $queryParams
     * @param null $body
     * @param array $headers
     * @return bool|null
     */
    public static function getData($name, $queryParams = [], $body = null, $headers = [])
    {
        $response = static::callRemoteApi($name, $queryParams, $body, $headers);
        $data = static::getResponseData($response);

        return $data;
    }


    protected static function getResponseData($response)
    {
        Log::debug("\nResponse Status Code: ".$response->getStatusCode()."\nResponse Body: ".$response->getBody());

        $resolver = static::getResolver();
        return $resolver->resolve($response);
    }

    protected static function apiPath($name)
    {
        if ( !isset(static::$apiMap[$name]) || !isset(static::$apiMap[$name]['path'])) {
            throw new \UnexpectedValueException('Do not define the path for "' . $name . '"');
        }
        return static::$apiMap[$name]['path'];
    }

    protected static function apiMethod($name)
    {
        if ( !isset(static::$apiMap[$name]) || !isset(static::$apiMap[$name]['method'])) {
            throw new \UnexpectedValueException('Do not define the method for the "' . $name . '"');
        }
        return static::$apiMap[$name]['method'];
    }

    protected static function defaultToken()
    {
        return request()->header('Authorization', null);
    }

    protected static function defaultHeaders()
    {
        $defaultHeader = [
            'Authorization' => self::defaultToken()
        ];

        return $defaultHeaders = array_merge($defaultHeader, static::$defaultHeaders);
    }

    protected static function makeUrl($path, $args = [])
    {
        $pieces = explode('/', $path);
        foreach ( $pieces as $key => $piece ) {
            if ((strlen($piece) > 0) && ($piece[0] == static::PREFIX_PATH_PARAM)) {
                if ( !isset($args[$piece])) {
                    throw new \UnexpectedValueException('Do not found the parameter "' . $piece . '"');
                }
                $pieces[$key] = $args[$piece];
            }
        }
        $path = implode('/', $pieces);

        foreach ( $args as $key => $param ) {
            if ($key[0] == static::PREFIX_PATH_PARAM) {
                unset($args[$key]);
            }
        }

        if (count($args) <= 0) {
            return $path;
        }
        $queryString = http_build_query($args, '', '&', PHP_QUERY_RFC3986);
        return $path . '?' . $queryString;
    }

    protected static function callRemoteApi($name, $queryParams = [], $body = null, $headers = [])
    {
        $method = static::apiMethod($name);
        $url = static::makeUrl(static::apiPath($name), $queryParams);

        $client = new HttpClient([
            'base_uri'    => static::getBaseUri(),
            'timeout'     => static::$apiTimeout,
            'http_errors' => false,
        ]);

        $bodyString = null;

        if ($body) {
            if (is_string($body)) {
                $bodyString = $body;
            } else if (is_array($body)) {
                $bodyString = json_encode($body);
            } else if ($body instanceof Arrayable) {
                $bodyString = json_encode($body->toArray());
            } else {
                $bodyString = (string)$body;
            }
        }

        $headers = array_merge(static::defaultHeaders(), $headers);
        $zc = app(ZipkinContext::class);
        if(!empty($zc->getTracing())){
            $injector = $zc->getTracing()->getPropagation()->getInjector(new Map());
            if(static::getResolver() == JavaRestResponseResolver::class){
                $tracer = $zc->getTracer();
                $span = $tracer->newChild($zc->getSpan()->getContext());
                $span->start();
                $span->setKind('CLIENT');
                $span->setName($method.':'.$name);
                $injector($span->getContext(), $headers);
            }else{
                $injector($zc->getSpan()->getContext(), $headers);
            }
        }
        Log::debug("\nRestModel: \n\tHost: " . static::getBaseUri() . "\n\tURL: $url\n\tMethod: $method\n\tHeaders: \n" . var_export($headers, true) . "\n\tBody: $bodyString\n");

        $request = new Request($method, $url, $headers, $bodyString);
        $response = $client->send($request);
        if(static::getResolver() == JavaRestResponseResolver::class) {
            $span->finish();
        }
        return $response;
    }

    protected static function getResolver()
    {
        $resolverClassName = static::$responseResolverClassName ?: static::$defaultResponseResolverClassName;
        $resolver = new $resolverClassName;
        if (! ($resolver instanceof Contracts\ResponseResolver)) {
            throw new \UnexpectedValueException('The response resolver is invalid.');
        }
        return $resolver;
    }

    protected static function makeResponsePaginator($data, $queryParams)
    {
        if ( !isset($data[static::$paginationKeyNames['response']['data']])) {
            return new LengthAwarePaginator([], 0, $queryParams[static::$paginationKeyNames['request']['perPage']], $queryParams[static::$paginationKeyNames['request']['page']]);
        }

        $items = [];
        foreach ( $data[static::$paginationKeyNames['response']['data']] as $item ) {
            $obj = new static();
            $obj->forceFill($item);
            $items[] = $obj;
        }

        $total = $data[static::$paginationKeyNames['response']['total']];
        $page = empty(static::$paginationKeyNames['response']['page']) ? $queryParams[static::$paginationKeyNames['request']['page']] : $data[static::$paginationKeyNames['response']['page']];
        $perPage = empty(static::$paginationKeyNames['response']['perPage']) ? $queryParams[static::$paginationKeyNames['request']['perPage']] : $data[static::$paginationKeyNames['response']['perPage']];
        $lengthAwarePaginator = new LengthAwarePaginator($items, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
        ]);

        return $lengthAwarePaginator;
    }

}
