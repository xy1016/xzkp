<?php
namespace Admin\Controller;
class AdminController extends CommonController 
{

    /**
     * [index 分页查数据]
     * @return [array] [分页按钮，分页数据组成的集合]
     */
    public function index()	
    {   
        $map['isdelete'] = 0;
        $res = $this->model->queryPage(5, $map);
        $this->assign($res);
        $this->display('index');
    }

    /**
     * [index_del 显示已删除数据]
     * @return [type] [分页按钮，分页数据组成的集合]
     */
    public function index_del()
    {
        $map['isdelete'] = 1;
        $res = $this->model->queryPage(5, $map);
        $this->assign($res);
        $this->display('index_del');
    }

    /**
     * [update 更新数据]
     * @return [int] [1代表更新成功 0失败]
     */
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
            if(false !== $this->model->data(I('post.'))->save())
                $this->ajaxReturn(['status' => 1]);
            else{
                $this->ajaxReturn(['status' => 0]);
            }
        }
    }

    /**
     * [updateStatus 禁用和启用]
     * @param  [int] $id [用户id]
     * @return [json]     [1成功 0失败]
     */
    public function updateStatus($id)
    {
        if($this->model->where(['id' => $id])->setField(['status' => I('post.status')]))
        {
            $this->ajaxReturn(['status' => 1]);
        }
        else $this->ajaxReturn(['status' => 0]);
    }

    /**
     * [delete_f 删除或者恢复]
     * @param  [int] $id [用户id]
     * @return [json]     [1成功 0失败]
     */
    public function delete_f($id)
    {
        if($this->model->where(['id' => $id])->setField(['isdelete' => I('post.isdelete')]))
        {
            $this->ajaxReturn(['status' => 1]);
        }
        else $this->ajaxReturn(['status' => 0]);
    }

    /**
     * [create 创建用户]
     * @return [json]     [1成功 0失败]
     */
    public function create()
    {
        $this->validatePost();  //验证表单数据
        if(false !== $this->model->add())  
        {
            $this->ajaxReturn(['status' => 1]);
        }
        else  $this->ajaxReturn(['status' => 0]);
    }

    /**
     * [getData 查询对应id信息]
     * @param  [int] $id [用户id]
     * @return [json]     [1成功 0失败]
     */
    public function getData($id)
    {
        if(!empty($row = $this->model->field(['id', 'name', 'username', 'phone', 'email'])->where(['id' => $id])->find()))
            $this->ajaxReturn($row);
        else $this->ajaxReturn(['status' => 0]);
    }

    /**
     * [change_password 修改密码]
     * @param  [int] $id [用户id]
     * @return [json]     [1成功 0失败]
     */
    public function update_password($id)
    {
        if(IS_GET)
        {
            $row = $this->model->where(['id' => $id])->field(['id', 'username'])->find();
            $this->assign('row', $row);
            $this->display();
        }
        else{
            $this->validatePost();
            if($this->model->save())
            {
                $this->ajaxReturn(['status' => 1]);
            }
            else $this->ajaxReturn(['status' => 0]);
        }    
    }
/*
    //删除用户,同时删除role_user表中记录的该用户角色
    public function del($id)
    {
        $this->model->startTrans();
        if($this->model->delete($id))
        {
            if($this->model->table(__ROLE_USER__)->where(['user_id' => $id])->delete())
            {
                $this->model->commit();
                $this->ajaxReturn(['status' => 1]);  
            }
            else $this->model->rollBack();
        }
        //以上操作都没有成功返回,则返回失败状态
        $this->ajaxReturn(['status' => 0]);  
    }*/

    /**
     * [login 验证登录]
     * @return [array] [成功返回1 失败返回错误信息]
     */
    public function login()
    {
        if(!IS_AJAX)
            $this->display();
        else
        {
            if($this->model->validate_login())
            {   
                $this->ajaxReturn(['status' => 1]);
            }
            else  $this->ajaxReturn(['status' => 0, 'info' => current($this->model->getError())]);
        }
    }

    /**
     * [verify 生成验证码]
     * @return [string] [验证码字符串]
     */
    public function verify()
    {
        $verify = new \Think\Verify();
        $verify->length = 4;
        $verify->useNoise = false;
        $verify->useCurve = false;
        $verify->entry();
    }

    /**
     * [logout 退出登录]
     * @return [string] [js字符串]
     */
    public function logout()
    {
        session('[destroy]'); 
        echo "<script>window.open('".U('Admin/Admin/login')."', '_top')</script>";
    }
}