<?php
namespace Admin\Model;

class NodeModel extends \Think\Model
{	
	protected $patchValidate = true;
	//条件参数  0有就验证,1必须验证,2 当有值而且不为空
	//时间参数 1新增,2,编辑,3both 4自定义(登录)
	protected $_validate = [
		['remark','require','描述不能为空',0,'',3], 
        ['name','require','方法不能为空',0,'',3], 
        ['name','/^[a-z_]{3,15}$/','只能为字母和下划线',0,'',1], 
		['name','','该权限已存在',1,'unique',0,'',3],
        ['module','require','模块不能为空',0,'',1],
        // ['title','require','标题不能为空, 重要, 写明白作用',0,'',3],
        ['pid','require','控制器不能为空',0,'',1],
        ['name', '', '该权限已存在', 1, 'unique', 4],
	];
	//新增自动填充字段值
	protected $_auto = [
		['addtime', 'time', 1, 'function'], //创建时间
        ['name', 'getPath',1, 'callback']
	];

	//分页查询用户列表
	//pageSize 分页数; $map 分页过滤条件
    public function queryPage()
    {	
        $map['status'] = 1;
        $map['level'] = ['gt' ,1];
        //显示具体节点就行
    	$pagination = getPage($this, $map);
    	$btn = $pagination->show();
    	$list = $this->order('id asc')->where($map)->select();
    	$level = ['1' => '模块', '2' => '控制器', '3' =>'方法'];
    	foreach($list as $key => $value)
    	{
    		$list[$key]['level'] = $level[$value['level']];

    	}
        return ['btn' => $btn, 'list' => $list];
    }

    public function findByID($id)
    {
        $list = $this->where(['id' => $id])->field(['id', 'name','level', 'pid', 'remark'])->find();
        return ['list' => $list];
    }

    public function getChildren($map)
    {
        $res = $this->where($map)->field(['id','name'])->select();
        if($res) return $res;
    }

    protected function getPath($name)
    {
        $pid = I('post.pid');
        $pidName = $this->where(['id' => $pid])->getField('name');
        return ltrim($pidName.'/'.$name, '/');
    }

    public function update_all()
    {
        //取得项目模块绝对路径
        $path = $_SERVER['DOCUMENT_ROOT']. __ROOT__.trim(APP_PATH,'.');
        //白名单，即项目模块
        $whiteList = ['Admin'];
        //控制器黑名单
        $cfilter = ['Empty', 'Common', 'Emulate'];
        //默认权限
        $defaultAction = ['read' => '读', 'write' => '写', 'see' => '可见'];
        //计数
        $count = 0;
        if(is_dir($path))
        {
            $source = opendir($path);
            while($f = readdir($source))
            {  
            //如果不是白名单里面的模块， 跳过
            if(!in_array($f, $whiteList))
               continue;
            //如果保存失败， 说明该节点已存在， 查出其id
            if(empty($id = $this->saveNode($f)))
            {
               $id = $this->where(['name' => $f])->getField('id');  
            }
            //记录成功的节点数
            else $count++;
            if(is_dir($collect = $path.$f.'/'.'Controller/'))
               {
                    $ss = opendir($collect);
                    while($ff = readdir($ss))
                    {     
                    //如果不是控制器，跳过
                        if(($pos = strpos($ff, 'Controller')) === false)
                        continue;
                        $controllerName = substr($ff, 0, $pos);
                        if(in_array($controllerName, $cfilter)) continue; //过滤掉非需控制器
                            $controller = $f.'/'.$controllerName;
                          //如果控制器节点保存失败， 则该节点已存在
                        if(empty($sid = $this->saveNode($controller, 2, $id)))
                            $sid = $this->where(['name' => $controller])->getField('id');  
                        else $count++;
                        foreach($defaultAction as $key => $value)
                        {
                            if($this->saveNode($controller.'/'.$key, 3, $sid, $value))
                            $count++;
                        }
                    }
               }
            }
        }// end for getting modules and controllers
        return $count;
    }

    public function saveNode($name, $level = 1, $pid = 0, $remark = false)//添加新权限
    {
      $data['name'] = $name;
      $data['remark'] = $remark === false?$name:$remark;
      $data['level'] = $level;
      $data['pid'] = $pid;
      $data['addtime'] = time();

      if(!$this->create($data, 4)) return false;
      if($id = $this->add()) return $id;
      else return false;
    }
}