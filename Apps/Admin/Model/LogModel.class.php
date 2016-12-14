<?php
namespace Admin\Model;

class LogModel extends CommonModel
{	
	protected $tableName = 'sys_log';
	protected $translation;

	public function __construct()
	{
		parent::__construct();
		//加载静态翻译文件
		$this->translation = include('log_translate.php');
	}

	public function queryPage($map = array())
    {   
        //搜素map条件
        if(I('get.userID') != '')
            $map['userID'] = I('get.userID');
        if(I('get.start') != '')
            $map['ctime'] = ['egt', I('get.start')];
        if(I('get.end') != '')
            $map['ctime'] = ['elt', I('get.end')];
        $pagination = getPage($this, $map, 10);
        $btn = $pagination->show();
        //管理员列表
        $admins = M('admin')->order('id desc')->field(['username', 'id'])->select();
        $list = $this->alias('a')->order('ctime desc')->field('a.*, b.username')->where($map)->join('left join admin b on a.userID = b.id')->select();
        foreach($list as $key => $value)
        {
            //描述
            $translate = '';
            //翻译表名
            $table = $value['affected_table'];
            $action = $list[$key]['action'];
            //描述翻译
            $desc = json_decode($value['description']);
            //如果静态文件中有该表的翻译信息
           	if(array_key_exists($table, $this->translation))
           	{	
           		$myTranslate = $this->translation[$table];
            	$table = $myTranslate['tableName'];
            	foreach($desc as $k => $v)
	            {  
                    //如果字段名有翻译
	            	if(array_key_exists($k, $myTranslate))
                    {   
                        //如果字段名的翻译是数组， 即对字段值也有翻译
                        if(is_array($myTranslate[$k]))
                        {   
                            $columName = key($myTranslate[$k]);
                            //如果是update行为
                            if($action == 2)
                            {
                                $vv = json_decode($v);
                                foreach($vv as $vvv)
                                {
                                    $collect[] = $myTranslate[$k][$columName][$vvv];
                                }
                                $vv = join(' 改为 ', $collect);
                            }
                            else
                            {
                                $vv = $myTranslate[$k][$columName][$v];  
                            }
                            $translate .= "<span class='myColumn'>{$columName}</span> <span class='myValue'>{$vv}</span> ";
                        }
                        //如果字段值没有翻译而且还是更新
                        else if($action == 2)
                        {
                            $v = json_decode($v);
                            $v = join(' 改为 ', $v);
                            $translate .= "<span class='myColumn'>{$myTranslate[$k]}</span> <span class='myValue'>{$v}</span> ";
                        }
                        //没翻译也不是更新
                        else
                        {
                            $translate .= "<span class='myColumn'>{$myTranslate[$k]}</span> <span class='myValue'>{$v}</span> ";
                        }
                    }
	            }
           	}
           	else
           	{
           		foreach($desc as $k => $v)
	            {
	            	$translate .= "<span class='myColumn'>{$k}</span> 为 <span class='myValue'>{$v}</span> ";
	            }
           	}

            //翻译条件where
            if(!empty($value['where']))
            {
                $mywhere = json_decode($value['where']);
                $where = '';
                foreach($mywhere as $wk => $wv)
                {
                    $where .= $wk . '=' . $wv.' ';
                }
                $where = rtrim($where);
                $where = '('.$where.')';
            }

            switch($action)
            {
            	case '1':
            		$prefix = "添加了新的<span class='tableName'>{$table}</span> {$where}：";
            		break;
            	case '2':
            		$prefix = "修改了<span class='tableName'>{$table}</span> {$where}：";
            		break;
            	case '3':
                    $prefix = "删除 <span class='tableName'>{$table}</span> {$where}: 中的 ";
            		break;
            }
            $list[$key]['description'] = $prefix.$translate;
        }
        return ['btn' => $btn, 'list' => $list, 'admins' => $admins];
    }
}