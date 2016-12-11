<?php
namespace Admin\Model;

/*
系统日志专用
 */
class CommonModel extends \Think\Model
{	
    private $beforeUpdateData = '';
	// 插入成功后的回调方法
    //$data 模型数据 $options 表达式 
    //action 1添加 2修改 3删除
    protected function _after_insert($data, $options) 
    {
        if($options['table'] == 'sys_log') return false;
            $this->filterData($data);
        if(!empty($id = $this->getLastInsID()))
        {
            $where = ['id' => $id];
        }
        else if(!empty($pk = $this->getPk()))
        {
            $where = [$pk => $data[$pk]];
        }
        $where = json_encode($where);
        $this->saveLog($options['table'], 1, json_encode($data), $where);
    }

    // 更新数据前的回调方法
    protected function _before_update($data, $options) 
    {
        if($options['table'] == 'sys_log') return false;
        $this->filterData($data);
        $possible_updated_columns = array_keys($data);
        $this->beforeUpdateData = $this->where($options['where'])->field($possible_updated_columns)->find();
    }

    protected function _after_update($data, $options)
    {
        if($options['table'] == 'sys_log') return false;
        $this->filterData($data, array_keys($options['where']));
        $diff = array_diff_assoc($data, $this->beforeUpdateData);
        //如果没有数据被修改
        if(empty($diff) || count($diff) == 0 ) return false;
        foreach($diff as $key => $value)
        {
            $diff[$key] = json_encode([$this->beforeUpdateData[$key], $value]);
        }
        $desc = json_encode($diff);
        $where = json_encode($options['where']);
        $this->saveLog($options['table'], 2, $desc, $where);
    }

    protected function _after_delete($data, $options)
    {
        if($options['table'] == 'sys_log') return false;
        $this->filterData($data, array_keys($options['where']));
        $where = json_encode($options['where']);
        $this->saveLog($options['table'], 3, null, $where);
    }

    private final function filterData(&$data, $where = array())
    {  
        //过滤掉data中一些本身跟具体操作无关或者值为空的字段
        $filter = ['addtime', 'reg_ip', 'ip'];
        $filter = array_merge($filter, $where);
        foreach($data as $key => $value)
        {
            if((empty($value) && $value !== 0) || in_array($key, $filter))
            {
                unset($data[$key]);
            }
        }
    }

    private final function saveLog($table, $action, $desc, $where)
    {
        $log['userID'] = session('mi_game_admin.id');
        $log['ip'] = ipToInt(get_client_ip());
        $log['affected_table'] = $table;
        $log['action'] = $action;
        $log['description'] = $desc;
        if(!empty($where)) $log['where'] = $where;
        M('sys_log')->data($log)->add();
    }
}