<?php
namespace Admin\Controller;
class LogController extends CommonController {

    public function index()	
    {   
        $map = '';
        $res = $this->model->queryPage($map);
        $this->assign($res);
        $this->display('index');
    }
}