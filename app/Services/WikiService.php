<?php

namespace App\Services;

use App\Exceptions\LogicException;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class WikiService
{
    /**得到项目信息
     * @param Request $request
     * @param $proId
     * @return mixed
     */
    public function getProjectById($proId,$token)
    {
        $client = new Client(['base_uri' => ENV('APP_URL').'/']);
        $res = $client->request('get', 'center/v1/project/'.$proId,
            [
                'headers' => [
                    'Content-type'=> 'application/json',
                    'Authorization'=> $token,
                    "Accept"=>"application/json"],
            ]);

        $data = $res->getBody()->getContents();
        $data = json_decode($data);
        $data->businessObj->wikiId = '2883706';
        return $data;
    }

    /**得到项目列表
     * @param Request $request
     * @param $proId
     * @return mixed
     */
    public function getProjectIndex($token)
    {
        $client = new Client(['base_uri' => ENV('APP_URL').'/']);
        $res = $client->request('get', 'center/v1/project/all',
            [
                'headers' => [
                    'Content-type'=> 'application/json',
                    'Authorization'=> $token,
                    "Accept"=>"application/json"],
            ]);

        $data = $res->getBody()->getContents();
        $data = json_decode($data);
        return $data->businessObj;
    }

    /**
     * 错误代码
     */
    public function rtnException($data)
    {
        if(!empty($data['statusCode'])) {
            if($data['statusCode'] == 403){
                throw new LogicException('wiki没有权限');
            }elseif ($data['statusCode'] == 404){
                throw new LogicException('wiki没找到资源');
            }elseif ($data['statusCode'] == 400){
            throw new LogicException('wiki错误的请求');
            }
        }
    }

    /**新建页面
     * @param $title
     * @param $ancestors
     * @param $token
     * @return mixed
     */
    public function createPage($title,$ancestors,$token)
    {
        try{
//        $cookieJar = CookieJar::fromArray([
//            'JSESSIONID' => '639EF5E9D8EA9D5C44FD20893217CB16'
//        ], '172.16.16.60');
//
            $client = new Client(['base_uri' => config('wiki.url')]);
            $res = $client->request('POST', 'content',
                [
                    'json' => ["title"=>$title,"type"=>"page","space"=>['key'=>config('wiki.space_key')],"ancestors"=>[["id"=>$ancestors]]],
                    'headers' => [
                        'Content-type'=> 'application/json',
                        'Authorization'=> $token,
                        "Accept"=>"application/json"],
                    //'cookies'=>$cookieJar
                ]);

            $data = $res->getBody()->getContents();
            return json_decode($data)->id;
        }catch (\GuzzleHttp\Exception\RequestException $e){
            $data =  $e->getResponse()->getBody();
            $data = json_decode($data, true);
            $this->rtnException($data);
        }

    }

    /**删除页页面
     * @param $id
     * @param $token
     */
    public function deletePage($id,$token)
    {
        $client = new Client(['base_uri' => config('wiki.url')]);
        $res = $client->request('DELETE', 'content/'.$id,
            [
                'headers' => [
                    'Authorization'=> $token,
                    "Accept"=>"application/json"],
            ]);
    }

    /**查找wiki页面
     * @param null $id
     * @param array $arrData
     * @param $token
     * @return mixed
     */
    public function findPage($token,$id=null,$arrQuery=[])
    {
        $uri = 'content';
        if(!empty($id))
            $uri .= '/'.$id;
        if(!empty($arrQuery)) {
            $strQuery = '?'.http_build_query($arrQuery);
            $uri .= $strQuery;
        }
        $client = new Client(['base_uri' => config('wiki.url')]);
        $res = $client->request('GET', $uri,
            [
                'headers' => [
                    'Authorization'=> $token,
                    "Accept"=>"application/json"],
            ]);
        $data = $res->getBody()->getContents();
        return json_decode($data);
    }

    /**
     * 更新一个页面
     * @param $id
     * @param $token
     * @return mixed
     */
    public function updatePage($arrData,$id,$token)
    {
        $version = $this->findPage($token,$id)->version->number+1;

        $dataJson = array_merge(["type"=>"page","version"=>["number"=>$version]],$arrData);

        $client = new Client(['base_uri' => config('wiki.url')]);
        $res = $client->request('PUT', 'content/'.$id,
            [
                'json' => $dataJson,
                'headers' => [
                    'Content-type'=> 'application/json',
                    'Authorization'=> $token,
                    "Accept"=>"application/json"],
            ]);

        $data = $res->getBody()->getContents();
        return json_decode($data)->id;
    }

    /**
     * 得到所有wiki上PLM空间下的页面
     */
    public function getWikiPageAll($token)
    {
        $allId = [];
        $arrNext = ['spaceKey'=>'plm','limit'=>50,'start'=>0];
        while (1){
            $wiki = $this->findPage($token,null,$arrNext);
            $results = $wiki->results;
            $link = $wiki->_links;
            foreach ($results as $item){
                $allId[]=$item->id;
            }
            if(empty($link->next))
                break;
            $strNext = substr($link->next,strpos($link->next,"?")+1);
            parse_str($strNext,$arrNext);
        };
        unset($allId[0]);
        return array_flip($allId);
    }


    /**
     * 文件上传
     */
    public function uploadFile($pageId, $fileName,$token)
    {
        try{
            $client = new Client(['base_uri' => config('wiki.url')]);
            $res = $client->request('POST', 'content/'.$pageId.'/child/attachment',
                [
                    'multipart' => [
                        [
                            'name'=>'comment',
                            'contents'=> ' '
                        ],
                        [
                            'name'=>'file',
                            'contents'=>fopen('/tmp/'.$fileName, 'r'),
                            'filename' => $fileName
                        ]
                    ],
                    'headers' => [
                        'Authorization'=> $token,
                        "Accept"=>"application/json",
                        "X-Atlassian-Token"=>'no-check'
                    ],
                ]);

            $data = $res->getBody()->getContents();

        }catch (\GuzzleHttp\Exception\RequestException $e){
            $data =  $e->getResponse()->getBody();
            $data = json_decode($data, true);
            $this->rtnException($data);
        }
        return json_decode($data)->results[0];
    }

}
