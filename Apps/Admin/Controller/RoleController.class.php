<?php
namespace Admin\Controller;
class RoleController extends CommonController {

   /*显示列表*/
   public function index() //查 分页显示数据
   {   
      $res = $this->model->queryPage();
      $this->assign($res);
      $this->display();
   }

   /*修改角色*/
   public function update($id)
   {
      //显示表单内容
      if(IS_GET)
      {  
         $row = $this->model->find($id);
         $res = $this->model->getData();
         $this->assign(['row' => $row, 'list' => $res]);
         $this->display();
      }
      elseif(intval($id) > 0){   //处理表单提交的修改数据
         $this->validatePost();
         if(count($_POST['permission3']) < 1) $this->ajaxReturn(['status' => 0]);
         $role['name'] = I('post.name');
         $role['remark'] = I('post.remark');
         // $this->model->startTrans(); //开启事务处理
         $this->model->where(['id' => $id])->save($role); //先修改role表
         //处理 role_node表
         //删除旧的$node
         $this->model->table(__ROLE_NODE__)->where(['role_id' => $id])->delete();
         if(1)
         {
            for($i = 3; $i >=0; $i--)
            {
               foreach(array_values($_POST['permission'.$i]) as $node)
               {
                  $data['node_id'] = $node;
                  $data['role_id'] = $id;
                  $data['level'] = $i;
                  $dataList[] = $data;
               }
            }
            if($this->model->table(__ROLE_NODE__)->field(['node_id', 'role_id', 'level'])->addAll($dataList))
                  $this->ajaxReturn(['status' => 1]);
            else
            {
               $error[] = ['ele' => 'remark', 'error' => '写入数据失败, 请等候更佳的网络连接'];
               $this->ajaxReturn(['status' => 0, 'error' =>$error]);
            }
         }
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
         if($res = $this->model->getData())
         $this->assign($res); 
         $this->display();
      }
      else if(IS_POST && IS_AJAX){
         $this->validatePost();
         if(count($_POST['permission3']) < 1) $this->ajaxReturn(['status' => 0]);
         $role['name'] = I('post.name');
         $role['remark'] = I('post.remark');
         $this->model->startTrans(); //开启事务处理
         $id = $this->model->add($role);
         if(!$id)  //如果role表写入失败,滚回
         {
            $error[] = ['ele' => 'remark', 'error' => '写入数据失败, 请等候更佳的网络连接'];
            $this->ajaxReturn(['status' => 0, 'error' =>$error]);
         }
         for($i = 3; $i >=0; $i--)
            {
               foreach(array_values($_POST['permission'.$i]) as $node)
               {
                  $data['node_id'] = $node;
                  $data['role_id'] = $id;
                  $data['level'] = $i;
                  $dataList[] = $data;
               }
            }
         //批量添加到数据库
         if($this->model->table(__ROLE_NODE__)->field(['node_id', 'role_id', 'level'])->addAll($dataList))
         {
            $this->model->commit();
            $this->ajaxReturn(['status' => 1]);
         }
         else
         {
            $this->model->rollback(); //事务回滚
            $error[] = ['ele' => 'remark', 'error' => '写入数据失败, 请等候更佳的网络连接'];
            $this->ajaxReturn(['status' => 0, 'error' =>$error]);
         }
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
      if(!$this->model->table(__ROLE__)->where(['id' => $id])->delete()) $flag = false;
      if(!$this->model->table(__ROLE_NODE__)->where(['role_id' => $id])->delete()) $flag = false;
      if($this->model->table(__ROLE_USER__)->find($id)) //确认该角色下是否有用户
      {
         if(!$this->model->table(__ROLE_USER__)->where(['role_id' => $id])->delete())
            $flag = false;
      }
      if(!flag)
      {
         $this->model->rollback;
         $this->ajaxReturn(['status' => 0]);
      }
      //如果flag还为true 说明删除都成功
      $this->model->commit();
      $this->ajaxReturn(['status' => 1]);
   }

   public function test()
   {    

        $this->model->getData();
   }
}