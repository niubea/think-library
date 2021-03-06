<?php

// +----------------------------------------------------------------------
// | Library for ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2018 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://library.thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github 仓库地址 ：https://github.com/czt/ThinkLibrary
// +----------------------------------------------------------------------

namespace library\logic;

use library\Controller;
use think\Db;
use think\db\Query;

/**
 * 数据更新管理器
 * Class Save
 * @package library\logic
 */
class Save extends Logic
{
    /**
     * 表单扩展数据
     * @var array
     */
    protected $data;

    /**
     * 表单额外更新条件
     * @var array
     */
    protected $where;

    /**
     * 数据对象主键名称
     * @var array|string
     */
    protected $pkField;

    /**
     * 数据对象主键值
     * @var string
     */
    protected $pkValue;

    /**
     * ViewForm constructor.
     * @param string|Query $dbQuery
     * @param array $data 表单扩展数据
     * @param string $pkField 指定数据对象主键
     * @param array $where 额外更新条件
     */
    public function __construct($dbQuery, $data = [], $pkField = '', $where = [])
    {
        $this->where = $where;
        $this->request = request();
        $this->data = empty($data) ? $this->request->post() : $data;
        $this->query = is_string($dbQuery) ? Db::name($dbQuery) : $dbQuery;
        $this->pkField = empty($pkField) ? $this->query->getPk() : $pkField;
        $this->pkValue = $this->request->post($this->pkField, null);
    }

    /**
     * 逻辑器初始化
     * @param Controller $controller
     * @return boolean
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function init(Controller $controller)
    {
        $this->controller = $controller;
        // 主键限制处理
        if (!isset($this->where[$this->pkField]) && is_string($this->pkValue)) {
            $this->query->whereIn($this->pkField, explode(',', $this->pkValue));
            if (isset($this->data)) unset($this->data[$this->pkField]);
        }
        // 前置回调处理
        if (false === $this->controller->_callback('_save_filter', $this->query, $this->data)) {
            return false;
        }
        // 执行更新操作
        $result = $this->query->where($this->where)->update($this->data) !== false;
        // 结果回调处理
        if (false === $this->controller->_callback('_save_result', $result)) {
            return $result;
        }
        // 回复前端结果
        if ($result !== false) {
            $this->controller->success('数据记录保存成功!', '');
        }
        $this->controller->error('数据保存失败, 请稍候再试!');
    }

}