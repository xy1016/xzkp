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
            else
            {
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

    /**
     * [edit 修改个人资料]
     * @return [type] [description]
     */
    public function editmy()
    {   
        if(IS_GET)
        {
            $id = session('mi_game_admin.id');
            $list = $this->model->where(['id' => $id])->field(['id', 'name', 'username', 'email', 'phone'])->find();
            $this->assign(['list' => $list]);
            $this->display();
        }
        else if(IS_POST)
        {
            $this->validatePost();
            if(false !== $this->model->data(I('post.'))->save())
                $this->ajaxReturn(['status' => 1]);
            $this->ajaxReturn(['status' => 0]);
        }
    }

    /**
     * [edit_pwd 用户自己修改密码 需要验证原始密码]
     * @return [json] [y or n]
     */
    public function editmy_pwd()
    {   
        $id = session('mi_game_admin.id');
        if(IS_GET)
        {
            if($res = $this->model->where(['id' => $id])->field(['username', 'pic'])->find())
            {
                $this->assign(['list' => $res]);
                $this->display();
            }
        }
        else if(IS_POST)
        {
            $this->validatePost();
            if($this->model->where(['id' => $id])->field('pwd')->save())
            {   
                session('[destroy]'); 
                $this->ajaxReturn(['status' => 1]);
            }
            $this->ajaxReturn(['status' => 0]);
        }
    }

    /**
     * [editmy_pic 修改个人头像, 有点赶工, 可以优化]
     * @return [type] [description]
     */
    public function editmy_pic()
    {
        $avatarPath = './Static/Uploads/Avatar/';
        if(IS_GET)
        {
            $id = session('mi_game_admin.id');
            if($res = $this->model->where(['id' => $id])->field(['id', 'username', 'pic'])->find())
            {
                $this->assign(['list' => $res]);
                $this->display();
            }
        }
        else if(IS_POST)
        {
            $config = array(
                'mimes'         =>  array(), //允许上传的文件MiMe类型
                'exts'          =>  array('jpg', 'gif', 'png', 'jpeg'), //允许上传的文件后缀
                'autoSub'       =>  true, //自动子目录保存文件
                'subName'       =>  $id, //子目录创建方式, 删除用户的时候删除此目录
                'rootPath'      =>  $avatarPath, //保存根路径
            );
            $upload = new \Think\Upload($config);// 实例化上传类
            $info   =   $upload->upload();
            if(!$info) 
            {
                $this->error($upload->getError());// 上传错误提示错误信息
            }
            else
            {// 上传成功
                $result['status'] = 1;
                foreach ($info as $va){
                    $picname = $va['savepath'].$va['savename'];
                }
                if(!$this->model->where(['id' => $id])->setField(['pic' => $picname]))
                {   
                    //保存不成功则删除
                    @unlink($avatarPath.$picname);
                }
                else @unlink($avatarPath.I('uid')); //删除旧图片
            }
        }
    }
}