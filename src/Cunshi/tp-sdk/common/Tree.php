<?php

namespace Cunshi\TpSdk\common;

class Tree
{
    private static $_instance = null;

    private $_pk;
    private $_pid;
    private $_child;

    public function __construct($pk, $pid, $child)
    {
        $this->_pk    = strval($pk);
        $this->_pid   = strval($pid);
        $this->_child = strval($child);
    }

    public static function getInstance($pk = 'id', $pid = 'pid', $child = 'children')
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($pk, $pid, $child);
        }

        return self::$_instance;
    }

    /**
     * 将目标数组转换为树形结构
     *
     * @param  array   $list 目标数组
     * @param  integer $root 顶级节点
     * @return array
     */
    public function listToTree($list, $root = 0)
    {
        $trees = [];

        foreach ($list as $item) {
            if ($item[$this->_pid] == $root) {
                $item[$this->_child] = $this->listToTree($list, $item[$this->_pk]);
                $trees[] = $item;
            }
        }

        return $trees;
    }

    /**
     * 获取所有父级节点id集合（包括自身）
     *
     * @param  array  $list 目标数组
     * @param  string $id   目标id
     * @return array
     */
    public function getParentIds($list, $id)
    {
        $arr = [];

        foreach ($list as $item) {
            if ($item[$this->_pk] == $id) {
                $arr[] = $item[$this->_pk];
                $arr = array_merge($this->getParentIds($list, $item[$this->_pid]), $arr);
            }
        }
        
        return $arr;
    }

    /**
     * 获取所有子孙节点id集合（不包括自身）
     *
     * @param  array  $list 目标数组
     * @param  string $id   目标id
     * @return array
     */
    public function getChildIds($list, $id)
    {
        $arr = [];

        foreach ($list as $item) {
            if ($item[$this->_pid] == $id) {
                $arr[] = $item[$this->_pk];
                $arr = array_merge($this->getChildIds($list, $item[$this->_pk]), $arr);
            }
        }

        return $arr;
    }
}
