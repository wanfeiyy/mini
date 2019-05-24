<?php
/**
 * Created by PhpStorm.
 * User: wks
 * Date: 2018/7/5
 * Time: 上午11:22
 */

namespace App\Utils;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Class Helper
 * @package Medlinker\Util
 */
class Helper
{
    /**
     * 是否开启CURLOPT_VERBOSE
     * 日志将输出在storage_path('logs/curl-verbose.log')
     * @var bool
     */
    const CURL_DEBUG = false;

    public static $curlOptions = [
        //启用时会将头文件的信息作为数据流输出
        CURLOPT_HEADER => false,
        //禁止 cURL 验证对等证书
        CURLOPT_SSL_VERIFYPEER => false,
        //将会根据服务器返回 HTTP 头中的 "Location: " 重定向
        CURLOPT_FOLLOWLOCATION => true,
        //获取的信息以字符串返回，而不是直接输出
        CURLOPT_RETURNTRANSFER => true,

        //Location重定向最大次数
        CURLOPT_MAXREDIRS => 3,
        ////设置成 2，会检查公用名是否存在，并且是否与提供的主机名匹配，0不检查
        CURLOPT_SSL_VERIFYHOST => 0,
        //允许 cURL 函数执行的最长秒数
        CURLOPT_TIMEOUT => 8,
        //在尝试连接时等待的秒数。设置为0，则无限等待
        CURLOPT_CONNECTTIMEOUT => 3,
        //在HTTP请求中包含一个"User-Agent: "头的字符串
        CURLOPT_USERAGENT => 'Medlinker HttpClient V1.0',
    ];

    /**
     * Http GET
     * @param string            $url     请求url.
     * @param array|string|null $params  参数.
     * @param array             $headers 请求头.
     * @return boolean|mixed
     */
    public static function httpGet($url, $params = null, array $headers = [])
    {
        if (is_string($params) || is_array($params)) {
            is_array($params) && $params = http_build_query($params);
            $url = rtrim($url, '?');
            if (strpos($url, '?') !== false) {
                $url .= '&' . $params;
            } else {
                $url .= '?' . $params;
            }
        }

        $ch = curl_init();

        curl_setopt_array($ch, self::$curlOptions);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPGET, true);//HTTP GET
        $headers && curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if (self::CURL_DEBUG) {
            $f = fopen(storage_path('logs/curl-verbose-' . date('Y-m-d') . '.log'), 'w+');
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_STDERR, $f);
        }

        $ret = curl_exec($ch);
        if ($errno = curl_errno($ch)) {
            \Log::error('HttpGet failed', [$url, $headers, $errno, curl_error($ch)]);
            $ret = false;
        }
        curl_close($ch);
        return $ret;
    }

    /**
     * Http POST
     * @param string            $url     请求url.
     * @param array|string|null $params  参数.
     * @param array             $headers 请求头.
     * @return boolean|mixed
     */
    public static function httpPost($url, $params = null, array $headers = [])
    {
        $ch = curl_init();

        curl_setopt_array($ch, self::$curlOptions);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);//HTTP POST

        if (is_string($params) || is_array($params)) {
            is_array($params) && $params = http_build_query($params);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }

        $headers && curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if (self::CURL_DEBUG) {
            $f = fopen(storage_path('logs/curl-verbose-' . date('Y-m-d') . '.log'), 'w+');
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_STDERR, $f);
        }

        $ret = curl_exec($ch);

        if ($errno = curl_errno($ch)) {
            \Log::error('httpPost failed', [$url, $params, $headers, $errno, curl_error($ch)]);
            $ret = false;
        }

        curl_close($ch);
        return $ret;
    }


    /**
     * 分页
     * @param mixed         $query 查询对象.
     * @param integer       $page  页码.
     * @param integer       $size  条数.
     * @param \Closure|null $trans Trans.
     * @param integer       $count 总数.
     * @return array
     */
    public static function queryPaging($query, $page, $size, \Closure $trans = null, $count = 0)
    {
        $data = array();

        if ($count > 0) {
            $data['total'] = $count;
        } else {
            $data['total'] = $query->count();
        }

        $skip = ($page - 1) * $size;
        $data['list'] = $query->skip($skip)->take($size)->get();

        if ($trans) {
            $data['list'] = $data['list']->transform($trans);
        }

        return $data;
    }

    /**
     * 分页
     * @param mixed         $query 查询对象.
     * @param integer       $start 开始数.
     * @param integer       $limit 条数.
     * @param \Closure|null $trans Trans.
     * @return array
     */
    public static function queryLimit($query, $start, $limit, \Closure $trans = null)
    {
        $data = array();
        $data['total'] = $query->count();
        $data['list'] = $query->skip($start)->take($limit + 1)->get();
        $data['more'] = 0;
        if (count($data['list']) == $limit + 1) {
            $data['more'] = 1;
            $data['list']->pop();
        }
        $data['start'] = $start + $limit;

        if ($trans) {
            $data['list'] = $data['list']->transform($trans);
        }

        return $data;
    }

    /**
     * 分页,不需要查询总数
     * @param mixed         $query 查询对象.
     * @param integer       $start 开始数.
     * @param integer       $limit 条数.
     * @param \Closure|null $trans Trans.
     * @return array
     */
    public static function queryPageByMore($query, $start, $limit, \Closure $trans = null)
    {
        $data = array();
        $data['list'] = $query->skip($start)->take($limit + 1)->get();
        $data['more'] = 0;
        if (count($data['list']) == $limit + 1) {
            $data['more'] = 1;
            $data['list']->pop();
        }

        $data['start'] = $start + $limit;
        if ($trans) {
            $data['list'] = $data['list']->transform($trans);
        }

        return $data;
    }

    /**
     * 分页
     * @param mixed         $query 查询对象.
     * @param integer       $start 开始数.
     * @param integer       $limit 条数.
     * @param \Closure|null $trans Trans.
     * @return array
     */
    public static function queryLimitByIdDesc($query, $start, $limit, \Closure $trans = null)
    {
        $data = array();
        $start > 0 && $query->where('id', '<=', $start);
        $data['list'] = $query->orderBy('id', 'desc')->limit($limit + 1)->get();
        $data['more'] = 0;
        $data['start'] = $start + $limit;
        if (count($data['list']) == $limit + 1) {
            $data['more'] = 1;
            $end = $data['list']->pop();
        } else {
            $data['start'] = 0;
            $end = $data['list']->last();
        }

        isset($end['id']) && $data['start'] = $end['id'];
        if ($trans) {
            $data['list'] = $data['list']->transform($trans);
        }

        return $data;
    }


    /**
     * 根据父级id获取所有的子级数据
     * @param array    $parentIdArr 父级id数组.
     * @param string   $pid         关联字段.
     * @param \Closure $closure     闭包（从数据库中查询数据）.
     * @param mixed    $returnId    返回数据（是否返回id).
     * @return array $data
     */
    public static function getAllChildDataByParentId(array $parentIdArr, $pid, \Closure $closure, $returnId = true)
    {
        $idList = [];
        $childList = [];

        $dataArr = $closure($pid, $parentIdArr);   // 从数据库中查询数据

        foreach ($dataArr as $val) {
            $idList[] = intval($val['id']);
        }
        if (!empty($idList)) {
            $childList = self::getAllChildDataByParentId($idList, $pid, $closure, $returnId);
        }
        if ($returnId) {
            $data = array_merge($idList, $childList);  // 返回id
        } else {
            $data = array_merge($dataArr, $childList);  // 返回全部数据
        }

        return $data;
    }

    /**
     * 处理无限级分类，返回带有层级关系的树形结构.
     * @param array   $data  数据数组.
     * @param integer $root  根节点的父级id.
     * @param string  $id    Id字段名.
     * @param string  $pid   父级id字段名.
     * @param string  $child 树形结构子级字段名.
     * @return array  $tree
     */
    public static function getMultilevelTree(array $data, $root = 0, $id = 'id', $pid = 'pid', $child = 'child')
    {
        $tree = [];
        $temp = [];

        foreach ($data as $key => $val) {
            $temp[$val[$id]] = &$data[$key];
        }
        foreach ($data as $key => $val) {
            $parentId = $val[$pid];
            if ($root == $parentId) {
                $tree[] = &$data[$key];
            } else {
                if (isset($temp[$parentId])) {
                    $parent = &$temp[$parentId];
                    $parent[$child][] = &$data[$key];
                }
            }
        }
        return $tree;
    }



    /**
     * 字符串转时间戳
     * @param string $value 字符串时间.
     * @return boolean|Carbon
     */
    public static function asDateTime($value)
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof \DateTime) {
            return Carbon::instance($value);
        }

        if (is_numeric($value)) {
            return Carbon::createFromTimestamp($value);
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value)) {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        }

        try {
            return Carbon::createFromFormat('Y-m-d H:i:s', $value);
        } catch (\InvalidArgumentException $e) {
            return false;
        }
    }


    /**
     * 事务调用
     *
     * @param string   $connection 数据库链接名.
     * @param \Closure $closure    闭包.
     *
     * @return boolean
     */
    public static function transaction($connection, \Closure $closure)
    {
        if (empty($connection) && ! is_string($connection)) {
            throw new \InvalidArgumentException('开启事务的链接名必须提供');
        }

        try {
            DB::connection($connection)->beginTransaction();
            $closure();
            DB::connection($connection)->commit();
            return true;
        } catch (\Exception $e) {
            DB::connection($connection)->rollBack();
            return false;
        }
    }

    /**
     * 替换或增加二维数组的每个item元素
     *
     * @param array $array
     * @param array $replace
     *
     * @return array
     */
    public static function arrayReplaceRecursive(array $array, array $replace)
    {
        if (! is_array(current($array))) {
            return $array;
        }

        return array_replace_recursive(
            $array,
            array_fill(0, count($array), $replace)
        );
    }


    /**
     * 转换数组的key 为蛇形命名方式.
     *
     * @param array $arr
     *
     * @return array|false
     */
    public static function arrayKeySnakeCase(array $arr)
    {
        return array_combine(array_map('snake_case', array_keys($arr)), array_values($arr));
    }

}
