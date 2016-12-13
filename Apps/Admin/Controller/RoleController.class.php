<?php
namespace Admin\Controller;
class RoleController extends CommonController {

   /*显示列表*/
   public function index() //查 分页显示数据
   {   
      $this->assign($this->model->queryPage());
      $this->display();
   }

   /*修改角色*/
    public function update($id)
    {
      //显示表单内容
        if(IS_GET && IS_AJAX)
        {  
            $res = $this->model->findByID($id);
            $this->assign($res);
            $this->display();
        }
        elseif(IS_POST && IS_AJAX)
        {   //处理表单提交的修改数据
            $role['name'] = I('post.name');
            $role['remark'] = I('post.remark');
            $this->model->where(['id' => $id])->save($role); //先修改role表
            if(false !== $this->model->table(__ROLE_NODE__)->where(['role_id' => $id])->delete())
            {
                if($this->model->saveByRoleID($id))
                    $this->ajaxReturn(['status' => 1]);
            }
            $error[] = ['ele' => 'remark', 'error' => '写入数据失败, 请等候更佳的网络连接'];
            $this->ajaxReturn(['status' => 0, 'error' =>$error]);
        }
    }

   //找到该角色下的权限节点
    public function own_role($id)
    {
        $res = M('role_node')->where(['role_id' => $id])->getField('node_id', true);
        $this->ajaxReturn(['status' => 1, 'res' => $res]);
    }

    public function create()
    {  
        if(IS_GET && IS_AJAX)
        {
            $this->assign($this->model->getData()); 
            $this->display();
        }
        else if(IS_POST && IS_AJAX)
        {
            $this->validatePost();
            $post = I('post.');
            $role['name'] = $post['name'];
            $role['remark'] = $post['remark'];
            if($id = $this->model->add($role))
            {
                if($this->model->saveByRoleID($id))
                    $this->ajaxReturn(['status' => 1]);   
            }
            $error[] = ['ele' => 'remark', 'error' => '写入数据失败, 请等候更佳的网络连接!'];
            $this->ajaxReturn(['status' => 0, 'error' =>$error]);
        }
    }

    /**
     * [allocate 给用户分配角色， 超管不在此列表]
     * @return [json] [y or n]
     */
    public function allocate()
    {
        if(IS_GET && IS_AJAX)
        {
            $list['role'] = $this->model->field(['id', 'name'])->select();
            $map['status'] = 1;
            $map['isdelete'] = 0;
            $map['id'] = ['gt', 1];
            $list['list'] = M('admin')->field('id, username')->where($map)->select();
            $this->assign($list);
            $this->display(); 
        }
        else if(IS_POST && IS_AJAX)
        {   
            $post = I('post.');
            foreach($post as $key => $value)
            {
                if(empty($value))
                    $error[] = ['ele' => $key, 'error' => '不能为空'];
            }
            if(count($error) > 0) $this->ajaxReturn(['status' => 0, 'error' => $error]);
            $model = M('role_user');
            if($model->where(['user_id' => $post['user_id']])->count())
                $model->data($post)->save();
            else $model->add($post);
            $this->ajaxReturn(['status' => 1]);
        }
    }
/*  
   public function get_action($id)
   {  
      $map['level'] = 3;
      $map['pid'] = $id;
      $map['status'] = 1;
      if($res = $this->model->field(['id','remark'])->table(__NODE__)->where($map)->select())
      $this->ajaxReturn(['status' =>1, 'data' => $res]);
   }*/

    /**
     * [del 删除角色, 同时删除绑定该角色跟用户的绑定关系]
     * @param  [int] $id [角色id]
     * @return [json]     [y or n]
     */
    public function del($id) 
    {
        $this->model->startTrans();
        $count = 0;
        $flag = true;
        if(!$this->model->table(__ROLE__)->where(['id' => $id])->delete())
        {
            $flag = false;
        } 
        if(!$this->model->table(__ROLE_NODE__)->where(['role_id' => $id])->delete()) $flag = false;
        if($this->model->table(__ROLE_USER__)->find($id)) //确认该角色下是否有用户
        {
         if(!$this->model->table(__ROLE_USER__)->where(['role_id' => $id])->delete())
            $flag = false;
        }
        if(!flag)
        {
         $this->model->rollback;
         $this->ajaxReturn(['status' => 0, 'error' => '请稍候尝试!']);
        }
        //如果flag还为true 说明删除都成功
        $this->model->commit();
        $this->ajaxReturn(['status' => 1]);
    }

   public function test()
   {    

        $this->model->findByID(1);
   }
}