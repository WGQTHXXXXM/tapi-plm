<?php
/**
 * Created by PhpStorm.
 * User: weijinlong
 * Date: 2018/8/24
 * Time: 下午4:01
 */

namespace App\Models;

use App\Exceptions\RestApiException;
use App\Models\Abstracts\RestApiModel;
use App\Models\Abstracts\WikiResponseResolver;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class Wiki extends RestApiModel
{
    protected static $responseResolverClassName = WikiResponseResolver::class;
    protected static $apiTimeout = 5;
    public static $apiMap = [
        'getUser' => ['method' => 'GET', 'path' => 'user'],//取得wiki用户
        'getContentPms' => ['method' => 'get', 'path' => '/pages/getcontentpermissions.action'],//取得wiki页面权限
        'setContentPms' => ['method' => 'post', 'path' => '/pages/setcontentpermissions.action'],//设定wiki页面权限
        'getWikiToken' => ['method' => 'POST', 'path' => '/#all-updates'],//取得wiki的token和sessionId
        'addUsersToGroup' => ['method' => 'POST', 'path' => '/admin/users/adduserstogroup.action'],//向组里加人
        'removeUserFromGroup' => ['method' => 'POST', 'path' => '/admin/users/removeuserfromgroup.action'],//从组里删人
        'getContentAttachment' => ['method' => 'get', 'path' => 'content/:id/child/attachment'],//得到页面下所有附件
    ];


    protected static function getBaseUri()
    {
        return config('wiki.url');
    }
////////////////////////////////////////////////

    /*
     * 取得wiki用户
     */
    public static function getUser($params)
    {
        try {
            $response = self::getItem('getUser', $params);
        } catch (RestApiException $e) {
            throw new RestApiException('接口出错：' . $e->getMessage());
        }
        return $response;
    }

    /**
     * 取得wiki页面权限
     */
    public static function getContentPms($params)
    {
        try {
            $response = self::getData('getContentPms', $params);
        } catch (RestApiException $e) {
            throw new RestApiException('接口出错：' . $e->getMessage());
        }
        return $response;
    }

    /**
     * 设定wiki页面权限
     */
    public static function setContentPms($params)
    {
        try {
            $wikiCki = Redis::get('wikiCooki');
            $wikiToken = Redis::get('wikiAtlToken');
            $params['atl_token'] = $wikiToken;
            $str = http_build_query($params);
            $response = self::getData('setContentPms',[],$str,
                ['Content-Type'=>'application/x-www-form-urlencoded','Cookie'=>'JSESSIONID='.$wikiCki]);
            if(!empty($response['actionErrors'])){//说明token和sessionId过期了
                if(!self::getWikiToken())
                    throw new RestApiException('wiki管理员没有权限');

                $wikiCki = Redis::get('wikiCooki');
                $wikiToken = Redis::get('wikiAtlToken');
                $params['atl_token'] = $wikiToken;
                $str = http_build_query($params);
                $response = self::getData('setContentPms',[],$str,
                    ['Content-Type'=>'application/x-www-form-urlencoded','Cookie'=>'JSESSIONID='.$wikiCki]);
                if(!empty($response['actionErrors']))
                    throw new RestApiException($response['actionErrors'][0]);
            }
        } catch (RestApiException $e) {
            throw new RestApiException('接口出错：' . $e->getMessage());
        }
        return $response;
    }

    /**
     * 取得wiki的token和sessionId
     */
    public static function getWikiToken()
    {
        try {
            $response = self::getData('getWikiToken');
        } catch (RestApiException $e) {
            throw new RestApiException('接口出错：' . $e->getMessage());
        }
        $html = (string)$response->getBody();
        Redis::set('wikiCooki',self::getCookie($response));
        Redis::set('wikiAtlToken',self::getAtlToken($html));
        return true;

    }

    private static function getCookie($response)
    {
        $setCookie = $response->getHeader('Set-Cookie')[0];
        $sessionId = explode(';',$setCookie)[0];
        return substr($sessionId,11);
    }
    private static function getAtlToken($html)
    {
        preg_match('/<meta id="atlassian-token" name="atlassian-token" content="\w+">/',$html,$matches);
        return substr($matches[0],59,-2);

    }


    /*
     * 向组里加人
     */
    public static function addUsersToGroup($params)
    {
        try {
            $wikiCki = Redis::get('wikiCooki');
            $wikiToken = Redis::get('wikiAtlToken');
            $params['atl_token'] = $wikiToken;
            $str = http_build_query($params);
            $response = self::getData('addUsersToGroup',[],$str,
                ['Content-Type'=>'application/x-www-form-urlencoded','Cookie'=>'JSESSIONID='.$wikiCki]);
            $html = (string)$response->getBody();
            preg_match('/<div class="aui-message aui-message-error closeable">/',$html,$matches);
            if(!empty($matches)){//说明token和sessionId过期了
                if(!self::getWikiToken())
                    throw new RestApiException('wiki管理员没有权限');

                $wikiCki = Redis::get('wikiCooki');
                $wikiToken = Redis::get('wikiAtlToken');
                $params['atl_token'] = $wikiToken;
                $str = http_build_query($params);
                $response = self::getData('addUsersToGroup',[],$str,
                    ['Content-Type'=>'application/x-www-form-urlencoded','Cookie'=>'JSESSIONID='.$wikiCki]);
                $html = (string)$response->getBody();
                preg_match('/<div class="aui-message aui-message-error closeable">/',$html,$matches);
                if(!empty($matches))//说明token和sessionId过期了
                    throw new RestApiException('会话已过期，请重新提交表单或刷新页面。');
            }
        } catch (RestApiException $e) {
            throw new RestApiException('接口出错：' . $e->getMessage());
        }
        return '成功';
    }

    /*
     * 从组里删人
     */
    public static function removeUserFromGroup($params)
    {
        try {
            $wikiCki = Redis::get('wikiCooki');
            $wikiToken = Redis::get('wikiAtlToken');
            $params['atl_token'] = $wikiToken;
            $str = http_build_query($params);
            $response = self::getData('removeUserFromGroup',[],$str,
                ['Content-Type'=>'application/x-www-form-urlencoded','Cookie'=>'JSESSIONID='.$wikiCki]);
            $html = (string)$response->getBody();
            preg_match('/<div class="aui-message aui-message-error closeable">/',$html,$matches);
            if(!empty($matches)){//说明token和sessionId过期了
                if(!self::getWikiToken())
                    throw new RestApiException('wiki管理员没有权限');

                $wikiCki = Redis::get('wikiCooki');
                $wikiToken = Redis::get('wikiAtlToken');
                $params['atl_token'] = $wikiToken;
                $str = http_build_query($params);
                $response = self::getData('removeUserFromGroup',[],$str,
                    ['Content-Type'=>'application/x-www-form-urlencoded','Cookie'=>'JSESSIONID='.$wikiCki]);
                $html = (string)$response->getBody();
                preg_match('/<div class="aui-message aui-message-error closeable">/',$html,$matches);
                if(!empty($matches))//说明token和sessionId过期了
                    throw new RestApiException('会话已过期，请重新提交表单或刷新页面。');
            }
        } catch (RestApiException $e) {
            throw new RestApiException('接口出错：' . $e->getMessage());
        }
        return '成功';
    }

    /**
     * 下载wiki的文件
     */
    public static function downloadFile($path,$fileName,$token)
    {
        $client = new Client(['base_uri' => config('wiki.url')]);
        $res = $client->request('get', $path, ['headers' => ['Authorization'=> $token]]);

        $stream = $res->getBody()->getContents();

        //返回的文件(流形式)
        header("Content-type: application/octet-stream");
        //按照字节大小返回
        header("Accept-Ranges: bytes");
        //返回文件大小
        header("Accept-Length: ".strlen($stream));
        //这里客户端的弹出对话框，对应的文件名

        $ua = $_SERVER["HTTP_USER_AGENT"];
        if(preg_match("/MSIE/", $ua) || preg_match("/Trident\/7.0/", $ua) || preg_match("/Edge/", $ua)){
            $encoded_filename = urlencode($fileName);
            $encoded_filename = str_replace("+", "%20", $encoded_filename);
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
        } else if (preg_match("/Firefox/", $ua)) {
            header('Content-Disposition: attachment; filename*="utf8\'\'' . $fileName . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
        }
        echo $stream;
    }

    /**
     * 得到wiki页面下所有附件
     */
    public static function getContentAttachment($id)
    {
        try {
            $response = self::getData('getContentAttachment',[':id'=>$id]);
        } catch (RestApiException $e) {
            throw new RestApiException('接口出错：' . $e->getMessage());
        }

        return $response;

    }
    public  function aa()
    {
        $buffer = 102400;
        $ua = $_SERVER["HTTP_USER_AGENT"];
        $name = "";
        $file = "/upload/images/avatar/头像 001.jpg";

        // 文件名处理
        if(empty($name)) $name = time();
        $ext = explode('?',pathinfo($file,PATHINFO_EXTENSION));
        if(empty(pathinfo($name,PATHINFO_EXTENSION))){
            $name = $name.'.'.$ext[0];
        }
        $encoded_filename = urlencode($name);
        $encoded_filename = str_replace("+", "%20", $encoded_filename);

// 网络文件
        if(stripos($file,'http://',0)===0 || stripos($file,'https://',0) === 0){
            $file = @ fopen($file, "r");
            if (!$file) {
                echo "文件找不到。";
            } else {
                header("Content-type: application/octet-stream");
                // 浏览器判断
                if(preg_match("/MSIE/", $ua) || preg_match("/Trident\/7.0/", $ua) || preg_match("/Edge/", $ua)){
                    header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
                } else if (preg_match("/Firefox/", $ua)) {
                    header('Content-Disposition: attachment; filename*="utf8\'\'' . $name . '"');
                } else {
                    header('Content-Disposition: attachment; filename="' . $name . '"');
                }
                while (!feof($file)) {
                    echo fread($file, $buffer);
                }
                fclose($file);
            }
        }
        // 本地文件
        else{
            if (!file_exists($file)) echo "文件找不到。";

            $fp = fopen($file, "r");
            $fileSize = filesize($file);

            $fileData = '';
            while (!feof($fp)) {
                $fileData .= fread($fp, $buffer);
            }
            fclose($fp);

            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-type:application/octet-stream;");
            header("Accept-Ranges:bytes");
            header("Accept-Length:{$fileSize}");
            // 浏览器判断
            if(preg_match("/MSIE/", $ua) || preg_match("/Trident\/7.0/", $ua) || preg_match("/Edge/", $ua)){
                header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
            } else if (preg_match("/Firefox/", $ua)) {
                header('Content-Disposition: attachment; filename*="utf8\'\'' . $name . '"');
            } else {
                header('Content-Disposition: attachment; filename="' . $name . '"');
            }

            header("Content-Transfer-Encoding: binary");
            echo $fileData;
        }

    }

}
