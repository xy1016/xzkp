<?php
namespace Admin\Controller;

class IndexController extends CommonController {
    public function index()
    {	
    	$id = session('mi_game_admin.id');
    	$row = M('admin')->where(['id' => $id])->field('username, pic')->find();
    	$this->assign(['myInfo' => $row]);
     	$this->display();
    }
}