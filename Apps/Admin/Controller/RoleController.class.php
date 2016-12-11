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

   public function get_action($id)
   {  
      $map['level'] = 3;
      $map['pid'] = $id;
      $map['status'] = 1;
      if($res = $this->model->field(['id','remark'])->table(__NODE__)->where($map)->select())
      $this->ajaxReturn(['status' =>1, 'data' => $res]);
   }

    public function del($id) //删除角色表中的角色
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