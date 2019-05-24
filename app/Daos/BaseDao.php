<?php
/**
 * Created by PhpStorm.
 * User: wanfeiyy
 * Date: 2018/11/14
 * Time: 下午4:48
 */

namespace App\Daos;

use App\Utils\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * 该基类实现了最基础的crud. 以及批量更新与批量添加数据, 复杂的查询由各个子类型实现
 *
 * Class BaseDao
 * @package Medlinker\Daos
 */
abstract class BaseDao
{

    /**
     * @var Builder
     */
    private $builder;

    /**
     * @return Model
     */
    abstract protected function getDao();


    private function setSort(Builder $builder, $column, $direction = 'desc')
    {
        return $builder->orderBy($column, $direction);
    }

    /**
     * @param array $where 条件.
     * @param mixed $cols Select的列名.
     * @param array $sort 排序.
     *
     * @return Model|null
     */
    public function getRow(array $where = [], $cols = ['*'], array $sort = [])
    {
        $cols = $this->getCols($cols);
        $builder = $this->getSimpleBuilder($where);
        empty($builder) && $builder = $this->getDao();
        foreach ($sort as $k => $v) {
            (is_string($k) && is_string($v)) && $this->setSort($builder, $k, $v);
        }

        return $builder->first($cols);
    }


    /**
     * @param $id
     * @param array $cols
     * @return Model|null
     */
    public function getById($id, $cols = ['*'])
    {
        return $this->getRow(['id' => $id], $cols);
    }

    /**
     * @param  mixed $cols 字段.
     *
     * @return array
     */
    private function getCols(&$cols)
    {
        if (is_string($cols)) {
            $cols = explode(',', $cols);
        }

        return $cols;
    }

    /**
     * 设置查询构建器.
     *
     * @param Builder $builder 查询构建器.
     *
     * @return $this
     */
    protected function setBuilder(Builder $builder)
    {
        $this->builder = $builder;
        return $this;
    }


    /**
     * @param array $attributes
     * @param array $values
     *
     * @return Model
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        return $this->getDao()->updateOrCreate($attributes, $values);
    }

    /**
     * 获取查询构建器.
     *
     * @return Builder
     */
    protected function getBuilder()
    {
        return $this->builder;
    }

    /**
     * @return $this
     */
    protected function resetBuilder()
    {
        $this->builder = null;
        return $this;
    }

    /**
     * ['a' => ['like', '%22%'], 'b' => ['in', [1,2,3]], 'c' => ['>=', 3], 'd' => ['between' => [1,5]]]
     *
     * 如果子类调用了setBuilder, 用子类的builder
     *
     * @param array $where 查询条件
     * @param mixed $cols 查询数据
     *
     * @return Builder | null
     */
    protected function getSimpleBuilder(array $where)
    {
        if (($builder = $this->getBuilder()) !== null) {
            return $builder;
        }

        if (empty($where)) {
            return null;
        }

        foreach ($where as $key => $val) {
            empty($builder) && $builder = $this->getDao();
            $operator = '=';
            if (is_array($val) && count($val) == 2) {
                list($operator, $value) = $val;
                $val = $value;
            }

            if ($operator === '=') {
                $builder = $builder->where($key, $val);
            } elseif ($operator === 'in') {
                if (! $val instanceof Collection) {
                    $val = (array) $val;
                }

               ! empty($val) && $builder = $builder->whereIn($key, $val);
            } elseif ($operator === 'between') {
                $builder = $builder->whereBetween($key, $val);
            } elseif ($operator === 'or') {
                $builder = $builder->orWhere($key, $val);
            } elseif($operator === 'not in'){
                $builder = $builder->whereNotIn($key, $val);
            }else {
                $builder = $builder->where($key, $operator, $val);
            }
        }

        // $this->builder = $builder;
        return empty($builder) ? null : $builder;
    }

    /**
     * @param array $where 查询条件.
     * @param array $cols  查询字段.
     * @param array $sort  排序.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getRows(array $where = [], $cols = ['*'], array $sort = [])
    {
        $cols = $this->getCols($cols);
        $builder = $this->getSimpleBuilder($where);
        empty($builder) && $builder = $this->getDao();
        foreach ($sort as $k => $v) {
            (is_string($k) && is_string($v)) && $this->setSort($builder, $k, $v);
        }

        return $builder->get($cols);
    }


    /**
     * @param array $data DB数据.
     * @return mixed
     */
    public function create(array $data)
    {
        $ret = $this->getDao()->create($data);
        return $ret;
    }


    /**
     * @param array $where 更新条件.
     * @param array $data 更新数据.
     *
     * @return bool|int
     */
    public function update(array $where = [], array $data)
    {
        $builder = $this->getSimpleBuilder($where);
        if ($builder === null) {
            return false;
        }

        $ret = $builder->update($data);
        return $ret;
    }


    public function updateById($id, array $data)
    {
        return $this->update(['id' => $id], $data);
    }


    /**
     * @param array $where 删除条件.
     *
     * @return bool|int
     */
    public function del(array $where = [])
    {
        $builder = $this->getSimpleBuilder($where);
        if ($builder === null) {
            return false;
        }

        $ret = $builder->delete();
        return $ret;
    }

    /**
     * 批量插入.
     *
     * @param array $data DB数据.
     *
     * @return mixed
     */
    public function batchInsert(array $data)
    {
        $ret = $this->getDao()->insert($data);
        return $ret;
    }


    /**
     * 批量更新.
     *
     * @param array | Collection $data 更新的数据.
     * @param string $whenField 设置的字段的key.
     * @param string $whereField 更新条件key.
     *
     * @return bool | mixed
     */
//    public function batchUpdate($data, $whenField = 'id', $whereField = 'id')
//    {
//        try {
//            $ret = $this->getDao()->batchUpdate($data, $whenField, $whereField);
//            return $ret;
//        } catch (\Exception $e) {
//            Log::error('批量更新失败:', [get_called_class(), $e->getMessage(), $e->getLine()]);
//            return false;
//        }
//    }

    /**
     * 查询条数.
     *
     * @param array $where 查询条件.
     *
     * @return int
     */
    public function count(array $where)
    {
        $builder = $this->getSimpleBuilder($where);
        return empty($builder) ? 0 : $builder->count();
    }

    /**
     * @return array
     */
    public function getFillable()
    {
        return $this->getDao()->getFillable();
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->getDao()->getTable();
    }


    public function getRelationRow(array $where, $with = [], $fields = ['*'])
    {
        return $this->getRelationData($where, $with)->first($fields);
    }

    public function getRelationRows(array $where, $with = [], $fields = ['*'])
    {
        return $this->getRelationData($where, $with)->get($fields);
    }


    private function getRelationData(array $where, $with = [])
    {
        $builder = $this->getSimpleBuilder($where);
        if (! empty($with)) {
            foreach ($with as $k => $v) {
                if (is_string($k) && $v instanceof \Closure) {
                    $builder->with([$k => $v]);
                }
            }
        }

        return $builder;
    }


    /**
     * 列表查询.
     *
     * @param array $where            查询条件.
     * @param array $options          列表选项.
     * @param \Closure|null $closure  字段格式化等.
     * @param array $with             关联查询.
     *
     * @return array
     */
    public function getList(array $where = [], array $options = [], \Closure $closure = null, $with = [])
    {
        if (empty($where) && $this->builder === null) {
            return [
                'start' => 0,
                'more' => 0,
                'list' => []
            ];
        }

        $query = $this->getSimpleBuilder($where)->select(empty($options['column']) ? ['*'] : $options['column']);
        if (! empty($with)) {
            foreach ($with as $k => $v) {
                if (is_string($k) && $v instanceof \Closure) {
                    $query->with([$k => $v]);
                }
            }
        };

        $data = Helper::queryLimitByIdDesc(
            $query,
            empty($options['start']) ? 0 : $options['start'],
            empty($options['limit']) ? 0 : $options['limit'],
            $closure
        );

        empty($options['toCollection']) && $data['list'] = $data['list']->toArray();
        return $data;
    }

}