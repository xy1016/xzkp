<?php
namespace Admin\Model;
class RoleModel extends CommonModel
{   
    protected $patchValidate = true;
    protected $_validate = [
        ['name', 'require', '角色名称不能为空', 1, '', 3], 
        // ['name', '/^\w{3,30}$/', '3-20位字母数字下划线', 1, '', 3], 
        ['name', '', '该角色名已存在', 1, 'unique', 3], 
        ['remark','require','描述不能为空',1,'',3], 
        // ['pid','require','请至少选择一个权限',1,'',3], 
    ];

    /**
     * [queryPage 查询角色列表， 同时查处每个角色下所有的用户]
     * @return [array] [返回角色信息数组]
     */
    public function queryPage()
    {   
        $pagination = getPage($this);
        $btn = $pagination->show();
        $list = $this->order('id desc')->field(['id', 'remark', 'name'])->select();
        $model = M('role_user');
        foreach($list as $key => $value)
        {
            $list[$key]['myuser'] = $model->alias('a')->field('b.username')->where(['role_id' => $value['id']])->join('join __ADMIN__ b on a.user_id = b.id')->select();
        }
        return ['btn' => $btn, 'list' => $list];
    }

    public function getData()
    {
         $map['status'] = 1;
         $map['level'] = 1;
         //找出模块数量, 本项目暂时只有一个模
         $model = M('node');
         $ids = $model->where($map)->getField('id', true);
         foreach($ids as $id)
         {
            $map['pid'] = $id;
            $map['level'] = 2;
            $controllers = $model->field(['id','remark'])->where($map)->select();
            $map['level'] = 3;
            // unset($collect['controller']);
            foreach($controllers as $controller)
            {
                $map['pid'] = $controller['id'];
                $actions = $model->field(['id','remark'])->where($map)->select();
                $collect[] = ['con' => $controller, 'action' => $actions];
            }
            $list[] = $collect;
         }
        return ['list' => $list];
    } 

    public function saveByRoleID($id)
    {
        //保存角色的子权限
        $post = I('post.');
        if(count($post['permission3']) > 0)
        {
            for($i = 3; $i >= 2; $i--)
            {
                foreach(array_values($post['permission'.$i]) as $node)
                {
                    $data['node_id'] = $node;
                    $data['role_id'] = $id;
                    $data['level'] = $i;
                    $dataList[] = $data;
                }
            }
            //批量添加到数据库
            if(!M('role_node')->addAll($dataList))
            {
                return false;
            }
        }
        return true;
    }

    public function findByID($id)
    {   
        $res = $this->getData();
        $res['role'] = $this->where(['id' => $id])->field(['id', 'name', 'remark'])->find();
        return $res;
    }

    protected function getChildren($id, $column)
    {
/*        $permission3 = $this->table(__ROLE_NODE__)->field(['role_id', 'node_id'])->where(['role_id' => $id, 'level' =])->getField('node_id', true);
        if($column != 'name') return $permission3;
        foreach($permission3 as $nodeID)
        {
            $res = $this->table(__NODE__)->field(['id', 'name'])->where(['id' => $nodeID])->getField($column, true);
        }
        return $res;*/
    }
}