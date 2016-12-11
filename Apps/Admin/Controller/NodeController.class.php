<?php
namespace Admin\Controller;
class NodeController extends CommonController {

    /*显示用户列表*/
    public function index()	//查 分页显示数据
    {   
        $res = $this->model->queryPage();
        $this->assign($res);
        $this->display();
    }

    public function update_all() //遍历模块找到模块和控制器
    {
        $count = $this->model->update_all();
        $this->ajaxReturn(['status' => 1, 'info' => $count]);
    }

    public function create()
    {
        if(IS_GET && IS_AJAX)
        {
            $map['status'] = 1;
            $map['level'] = 1;
            $res = $this->model->where($map)->field(['id','name'])->select();
            $this->assign('list',$res);
            $this->display();
        }
        else if(IS_POST && IS_AJAX)
        {
            $this->validatePost();
            if($this->model->add())
                $this->ajaxReturn(['status' => 1]);
            else $this->ajaxReturn(['status' => 0]);
        }
    }

    public function update($id)
    {
        if(IS_GET && IS_AJAX)
        {
            $res = $this->model->findByID($id);
            $this->assign($res);
            $this->display();
        }
        else if(IS_POST && IS_AJAX)
        {
            $this->validatePost();
            if(false !== $this->model->save())
                $this->ajaxReturn(['status' => 1]);
            else $this->ajaxReturn(['status' => 0]);
        }
    }

    public function child($id)
    {
        $map['pid'] = $id;
        $res = $this->model->getChildren($map);
        $this->ajaxReturn($res);
    }

    public function test()
    {
        $this->model->findByID(6);
    }
}