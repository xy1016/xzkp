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
            if(false !== M('role_node')->where(['role_id' => $id])->delete())
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

    /**
     * [create 浏览和创建角色]
     * @return [json] [返回创建结果]
     */
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
     * [update_allocate 给用户分配角色， 超管不在此列表]
     * @return [json] [y or n]
     */
    public function update_allocate()
    {
        if(IS_GET && IS_AJAX)
        {
            $map['status'] = 1;
            $map['isdelete'] = 0;
            $map['id'] = ['gt', 1];
            $list['list'] = M('admin')->where($map)->field('id, username')->select();
            $this->assign($list);
            $this->display('allocate'); 
        }
        else if(IS_POST && IS_AJAX)
        {   
            $post = I('post.');
            foreach($post as $key => $value)
            {
                if($value == '')
                    $this->ajaxReturn(['status' => 0]);
            }
            $model = M('role_user');
            if($model->where(['user_id' => $post['user_id']])->count())
                $model->data($post)->save();
            else $model->add($post);
            $this->ajaxReturn(['status' => 1]);
        }
    }

    public function update_myroles()
    {
        $user_id = I('post.user_id');
        $myRoleID = M('role_user')->where(['user_id' => $user_id])->getField('role_id');
        $roles = $this->model->getField('id, name', true);
        if(!empty($myRoleID))
            $output[] = "<option value='{$myRoleID}' selected= 'selected'>{$roles[$myRoleID]}</option>";
        else $output[] = "<option value='' selected= 'selected'>新分配一个角色</option>";
        foreach($roles as $key => $value) 
        {
            if($key == $myRoleID) continue;
            $output[] = "<option value='{$key}'>{$value}</option>";
        }
        $this->ajaxReturn(['status' => 1, 'info' => $output]);
    } 

    /**
     * [del 删除角色, 同时删除绑定该角色跟用户的绑定关系]
     * @param  [int] $id [角色id]
     * @return [json]     [y or n]
     */
    public function delete_role($id) 
    {
        $this->model->startTrans();
        $flag = true;
        //删除角色
        if(!$this->model->table(__ROLE__)->where(['id' => $id])->delete())
        {
            $flag = false;
        } 
        //删除角色的权限关系
        if($flag && !$this->model->table(__ROLE_NODE__)->where(['role_id' => $id])->delete()) $flag = false;
        //删除角色绑定的用户关系
        if($flag && false === $this->model->table(__ROLE_USER__)->where(['role_id' => $id])->delete()) $flag = false;
        if(!flag)
        {
            $this->model->rollback;
            $this->ajaxReturn(['status' => 0, 'error' => '请稍候尝试!']);
        }
        //如果flag还为true 说明删除都成功
        $this->model->commit();
        $this->ajaxReturn(['status' => 1]);
    }
}