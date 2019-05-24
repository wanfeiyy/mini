<?php
/**
 * CaseConverter
 */

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

/**
 * Trait CaseConverter.
 *
 * <p></p>
 *
 * @package Medlinker\Traits
 * @author luoyu
 */
trait CaseConverter
{
    /**
     * 将输入值转成 array，并将其所有的 key 转换成驼峰命名.
     *
     * @param array|Collection|Model|LengthAwarePaginator|Paginator $data 数据.
     * @return array
     */
    protected function key2CamelCase($data)
    {
        $needChange = false;

        $array = $data;
        if (is_array($data)) {
            $needChange = true;
        } elseif ($data instanceof Collection) {
            $needChange = true;
            $array = $data->toArray();
        } elseif ($data instanceof Model) {
            $needChange = true;
            $array = $data->toArray();
        } elseif ($data instanceof LengthAwarePaginator) {
            // 分页：已知数据的总条数
            $needChange = true;
            $array = $data->toArray();
        } elseif ($data instanceof Paginator) {
            // 分页：数据的总条数未知，LengthAwarePaginator 继承自 Paginator
            $needChange = true;
            $array = $data->toArray();
        }

        return $needChange ? $this->covertCameCaseKey($array) : $data;
    }

    /**
     * 递归的将数组的 key 转成驼峰命名方式.
     *
     * @param array $array 数据.
     * @return array
     */
    private function covertCameCaseKey(array $array)
    {
        $result = [];
        foreach ($array as $key => $value) {
            $key = is_string($key) && !empty($key) ? camel_case($key) : $key;
            if (is_array($value)) {
                $value = $this->covertCameCaseKey($value);
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * 将输入值转成 array，并将其所有的 key 转成蛇形命名.
     *
     * @param mixed $data Data.
     *
     * @return array
     */
    protected function key2SnakeCase($data)
    {
        $params = collect($data)->toArray();

        return $this->covertSnakeCaseKey($params);
    }

    /**
     * 将 request 的 key 转成蛇形命名.
     *
     * @param Request $request  Request.
     * @param array   $onlyKeys 仅获取并转化指定的key.
     *
     * @return array
     */
    protected function request2SnakeCase(Request $request, array $onlyKeys = [])
    {
        $params = empty($onlyKeys) ? $request->all() : $request->only($onlyKeys);

        return $this->covertSnakeCaseKey($params);
    }

    /**
     * 递归的将数组的 key 转成蛇形命名方式.
     *
     * @param array $array 数据.
     *
     * @return array
     */
    private function covertSnakeCaseKey(array $array)
    {
        $result = [];
        foreach ($array as $key => $value) {
            $key = is_string($key) && !empty($key) ? snake_case($key) : $key;
            if (is_array($value)) {
                $value = $this->covertSnakeCaseKey($value);
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * 将多层次数据，转化成扁平化的一层数据.
     *
     * <p>如：[a => [b => 1, c => 2], d => 3] 转化成 [b => 1, c => 2, d => 3]</p>
     *
     * @param array   $data  数据.
     * @param integer $depth 深度.
     *
     * @return array
     */
    private function flatten(array $data, $depth = 1)
    {
        $result = [];

        foreach ($data as $key => $item) {
            $item = $item instanceof Collection ? $item->all() : $item;

            if (is_array($item)) {
                if ($depth === 1) {
                    $result = array_merge($result, $item);
                    continue;
                }

                $result = array_merge($result, $this->flatten($item, $depth - 1));
                continue;
            }

            $result[$key] = $item;
        }

        return $result;
    }
}
