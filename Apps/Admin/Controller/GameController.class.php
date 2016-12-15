<?php
namespace Admin\Controller;
class GameController extends CommonController {

   /*显示管理员列表, 定义数据库表名*/
   public function index()	//查 分页显示数据
   {   
      $res = $this->model->queryPage();
      $this->assign($res);
      $this->display('index');
   }

   /*修改用户 一组数据操作2个表,都启用事务回滚*/
   public function update()
   {
		//显示表单内容
    	if(IS_GET)
        {  
          $row = $this->model->find($id);
          $this->assign(['row' => $row]);
         $this->display();
    	}
    	else if(IS_AJAX)
        {   //处理表单提交的修改数据
            $this->validatePost();
            if(false !== $this->model->save())
              $this->ajaxReturn(['status' => 1]);
            else{
              $this->ajaxReturn(['status' => 0]);
            }
    	}
   }

   /*创建*/
   public function create()
   {
      $this->validatePost();  //验证表单数据
      if(false !== $this->model->add())  
      {
        $this->ajaxReturn(['status' => 1]);
      }
      else  $this->ajaxReturn(['status' => 0]);
   }

   //根据id查询信息
   public function getData($id)
   {
      if(!empty($row = $this->model->field(['id', 'game_name', 'desc'])->where(['id' => $id])->find()))
        $this->ajaxReturn($row);
      else $this->ajaxReturn(['status' => 0]);
   }

   public function reverse($id)
   {
         if($this->model->where(['id' => $id])->setField(['isdelete' => I('post.isdelete')]))
         {
            $this->ajaxReturn(['status' => 1]);
         }
         else echo $this->model->_sql();
   }
   //彻底删除
    public function delete($id)
    {   
        $count = 0;
        if($count = $this->model->table(__CARRIER_GAME__)->where(['game_id' => $id])->count())
        {   
            $this->ajaxReturn(['status' => 0, 'error' => $count.'个运营商还有该款游戏,不能删除']);  
        }
        if(false !== $this->model->delete($id))
            $this->ajaxReturn(['status' => 1]);
        $this->ajaxReturn(['status' => 0]);  
    }
}