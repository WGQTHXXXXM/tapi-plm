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

abstract class RestApiModelWithConsul extends RestApiModel
{
    /**
     * 接口服务名称（name）
     *
     * @var string
     */
    protected static $service_name = '';

    //abstract protected static function getBaseUri();
    protected static function getBaseUri()
    {
        $consul = app()->make(\App\ServiceFactory\Consul::class);

        if (empty(static::$service_name)) {
            throw new \Exception("服务名称为空，无法找到相应服务地址");
        }
        $service = $consul->getServices(static::$service_name)
            ->getFirst();
        if ($service == false) {
            throw new \Exception("服务信息不完整，无法使用服务: " .static::$service_name);
        }
        return $service->getBaseUri();
    }
}
