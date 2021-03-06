<?php
namespace Admin\Controller;
use Think\Controller;

class CommonController extends Controller {
    protected $model;  
    public function _initialize()
    {
        //检测是否是登录操作， 不是则验证登录状态
        if(!session('mi_game_admin') && ACTION_NAME != 'login')
        {
            $this->redirect('Admin/Admin/login');
            exit;
        }
        //权限判断, 任何方法都检查是否有读的权限
        $action = substr(ACTION_NAME, 0, 6);
        $whitelist = ['login', 'logout', 'editmy'];
        if(IS_GET && !in_array($action, $whitelist))
        {
            $node = MODULE_NAME.'/'.CONTROLLER_NAME.'/read';
            //一些特殊节点需要的权限在具体控制器具体方法中去判断
            if(session('mi_game_admin.id') != 1 && !in_array($node, session('node')))
            {
                $this->error('您权限不足', U('Admin/Index/welcome'));
                die;
            }
        }
        //判断有没有写的权限
        else if(IS_POST && in_array($action, ['create', 'update', 'delete', 'revers']))
        {
            $node = MODULE_NAME.'/'.CONTROLLER_NAME.'/write';
            if(session('mi_game_admin.id') != 1 && !in_array($node, session('node')))
                $this->error('您权限不足', U('Admin/Index/welcome'));  
        }
    }

    public function __construct()
    {
        parent::__construct();
        //Index默认控制器不要读数据库...
        if(CONTROLLER_NAME == 'Index') return;
        $modelName = '\\'.MODULE_NAME.'\\Model\\'.CONTROLLER_NAME.'Model';
        $this->model = new $modelName ;
    }

    //验证表单
    public function validatePost($data = false)
    {   
        if(empty($data)) $data = I('post.');
        if(!$this->model->create($data) || count($data) < 2)
        {   
            $errors = $this->model->getError();
            foreach(I('post.') as $key => $value)
            {
                if(!isset($errors[$key]))
                $error[] = ['ele' => $key, 'status' => 1];
                else $error[] = ['ele' => $key, 'error' => $errors[$key]];
            }
                //ajax返回一个对象数组,第一个成员为验证结果status 第二个成员为错误结果集 
                $this->ajaxReturn(['status' => 0, 'error' =>$error]);
        }
    }
}