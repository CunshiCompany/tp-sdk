<?php

namespace Cunshi\TpSdk\traits;

/**
 * 初始化搜索条件
 * @author ltj
 */
trait SearchFilter
{
    protected $search_normals = [];  // 普通搜索默认配置
    protected $search_excepts = [];  // 搜索排除
    protected $search_fuzzys = [];  // 模糊搜索默认配置
    protected $search_betweens = [];  // 区间搜索默认配置
    protected $search_not_betweens = [];  // 非区间搜索默认配置
    protected $search_contains = [];  // 包含搜索默认配置
    protected $search_querys = [];  // 原生搜索默认配置

    /**
     * 根据所传参数组装搜索条件并返回
     *
     * @param array $params 需要处理的搜索参数
     * @return array
     */
    protected function getConditions($params = [])
    {
        $conditions = [];

        foreach ($params as $key => $item) {
            if (!$item && $item !== strval(0)) {
                continue;
            }

            if (isset($this->search_normals[$key])) {
                $this->_setNormalCondition($this->search_normals[$key], $item, $conditions);
            }

            if (isset($this->search_excepts[$key])) {
                $this->_setExceptCondition($this->search_excepts[$key], $item, $conditions);
            }

            if (isset($this->search_fuzzys[$key])) {
                $this->_setFuzzyCondition($this->search_fuzzys[$key], $item, $conditions);
            }

            if (isset($this->search_betweens[$key])) {
                $this->_setBetweenCondition($this->search_betweens[$key], $item, $conditions);
            }

            if (isset($this->search_not_betweens[$key])) {
                $this->_setNotBetweenCondition($this->search_not_betweens[$key], $item, $conditions);
            }

            if (isset($this->search_contains[$key])) {
                $this->_setContainCondition($this->search_contains[$key], $item, $conditions);
            }

            if (isset($this->search_querys[$key])) {
                $this->_setQueryCondition($this->search_querys[$key], $conditions);
            }
        }

        return $conditions;
    }

    /**
     * 普通搜索拼装
     *
     * @param string $key 键
     * @param string $value 值
     * @param array $conditions 搜索条件集
     * @return void
     */
    private function _setNormalCondition($key, $value, &$conditions)
    {
        if (strpos($value, ',') !== false) {
            $conditions[] = [
                $key,
                'in',
                explode(',', $value)
            ];
        } else {
            $conditions[] = [
                $key,
                '=',
                $value
            ];
        }
    }

    /**
     * 排除搜索拼装
     *
     * @param string $key 键
     * @param string $value 值
     * @param array $conditions 搜索条件集
     * @return void
     */
    private function _setExceptCondition($key, $value, &$conditions)
    {
        if (strpos($value, ',') !== false) {
            $conditions[] = [
                $key,
                'not in',
                explode(',', $value)
            ];
        } else {
            $conditions[] = [
                $key,
                '<>',
                $value
            ];
        }
    }

    /**
     * 模糊搜索拼装
     *
     * @param string $key 键
     * @param string $value 值
     * @param array $conditions 搜索条件集
     * @return void
     */
    private function _setFuzzyCondition($key, $value, &$conditions)
    {
        $conditions[] = [
            $key,
            'like',
            '%' . $value . '%'
        ];
    }

    /**
     * 区间搜索拼装
     *
     * @param string $key 键
     * @param string $value 值
     * @param array $conditions 搜索条件集
     * @return void
     */
    private function _setBetweenCondition($key, $value, &$conditions)
    {
        $arr = explode('_', $value);

        if (!$arr[0] && $arr[1]) {
            $conditions[] = [
                $key,
                '<=',
                $arr[1]
            ];
        } elseif ($arr[0] && !$arr[1]) {
            $conditions[] = [
                $key,
                '>=',
                $arr[0]
            ];
        } elseif ($arr[0] && $arr[1]) {
            $conditions[] = [
                $key,
                'between',
                [
                    $arr[0],
                    $arr[1]
                ]
            ];
        }
    }

    /**
     * 非区间内搜索拼装
     *
     * @param string $key 键
     * @param string $value 值
     * @param array $conditions 搜索条件集
     * @return void
     */
    private function _setNotBetweenCondition($key, $value, &$conditions)
    {
        $arr = explode('_', $value);

        if (!$arr[0] && $arr[1]) {
            $conditions[] = [
                $key,
                '>',
                $arr[1]
            ];
        } elseif ($arr[0] && !$arr[1]) {
            $conditions[] = [
                $key,
                '<',
                $arr[0]
            ];
        } elseif ($arr[0] && $arr[1]) {
            $conditions[] = [
                $key,
                'not between',
                [
                    $arr[0],
                    $arr[1]
                ]
            ];
        }
    }

    /**
     * 包含搜索拼接
     *
     * @param string $key 键
     * @param string $value 值
     * @param array $conditions 搜索条件集
     * @return void
     */
    private function _setContainCondition($key, $value, &$conditions)
    {
        $conditions[] = [
            $key,
            'in',
            $value
        ];
    }

    /**
     * 按表达式搜索
     *
     * @param string $key 键
     * @param object $value 值
     * @param array $conditions 搜索条件集
     * @return void
     */
    private function _setQueryCondition($value, &$conditions)
    {
        $conditions[] = [
            '',
            'exp',
            $value
        ];
    }
}
