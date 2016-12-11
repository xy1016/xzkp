<?php
namespace Admin\Controller;
class CarrierController extends CommonController 
{
    /**
     * [index 分页查数据]
     * @return [array] [分页按钮，分页数据组成的集合]
    */
    public function index()	//查 分页显示数据
    {   
        $res = $this->model->queryPage();
        $res['gameList'] = $this->getGameList();
        $this->assign($res);
        $this->display('index');
    }

    /**
     * [update 更新数据]
     * @return [json] [1代表更新成功 0失败]
     */
    public function update($id)
    {   
        if(IS_GET && IS_AJAX)
        {
            $list = $this->model->find($id);
            $list['server_ip'] = long2ip($list['server_ip']);
            $this->assign(['list' => $list, 'gameList' => $this->getGameList()]);
            $this->display();
        }
        else if(IS_POST && IS_AJAX)
        {
            $this->validatePost();
            if($this->model->updateData($id))
                $this->ajaxReturn(['status' => 1]);
            else $this->ajaxReturn(['status' => 0]);
        }
    }

    /**
     * [updateDesk 更新运营商游戏桌数，预警等设定]
     * @param  [int] $id [运营商id]
     * @return [json]     [成功或失败]
     */
    public function updateDesk($id)
    {
        if(IS_GET && IS_AJAX)
        {
            if(!empty($list = $this->model->getCarrierDesk($id)))
            {  
                $this->assign(['list' => $list]);
                $this->display();
            }
        }
        else if(IS_POST && IS_AJAX)
        {
            if($this->model->updateDesk())
                $this->ajaxReturn(['status' => 1]);
            else $this->ajaxReturn(['status' => 0]);
        }
    }

    /**
     * [agent description]
     * @param  [int] $id [运营商id]
     * @return [查询返回运营商id和名称，post提交生成注册码并返回成功状态]
     */
    public function agent($id)
    {
        if(IS_GET && IS_AJAX)
        {
            $list = $this->model->where(['id' => $id])->field(['id', 'name'])->find();
            $this->assign(['list' => $list]);
            $this->display();
        }
        else if(IS_POST && IS_AJAX)
        {   
            $headChar = 'A'.I('post.agent_level');
            // $model = M('carrier_agent');
            $model = new \Admin\Model\CommonModel('carrier_agent');
            //未使用的代理员注册码不让重复
            do
            {
                $reg_code = generateCode(21, $headChar);
            //若操作量很多此句要来优化索引！
            }while($model->where(['carrier_id' => $id, 'reg_code' => $reg_code, 'state' => 0])->count() > 0);
            $data['reg_code'] = $reg_code;
            $data['agent_level'] = I('post.agent_level');
            $data['carrier_id'] = $id;
            if($model->data($data)->add())
                $this->ajaxReturn(['status' => 1]);
            else $this->ajaxReturn(['status' => 0]); 
        }
    }

    /**
     * [ajaxAgent 返回运营商所有的代理注册码]
     * @param  [int] $id [运营商id]
     * @return [string]     [拼接好的运营商注册码html段 前端ajax输出]
     */
    public function ajaxAgent($id)
    {
        $map['carrier_id'] = $id;
        $this->ajaxReturn($this->model->queryAgent($map));
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
     * [reverse 删除或者恢复]
     * @param  [int] $id [该记录id]
     * @return [json]     [1成功 0失败]
     */
    public function reverse($id)
    {
         if($this->model->where(['id' => $id])->setField(['isdelete' => I('post.isdelete')]))
         {
            $this->ajaxReturn(['status' => 1]);
         }
    }

    /**
     * [create 创建]
     * @return [json]     [1成功 0失败]
     */
    public function create()
    {
        if(!IS_POST)
        {
            $list = $this->getGameList();
            $this->assign(['list' => $list]);
            $this->display();
        }
        
        $this->validatePost();  //验证表单数据
        if($this->model->createData())
            $this->ajaxReturn(['status' => 1]);
        else  $this->ajaxReturn(['status' => 0]);
    }

    /**
     * [getData 查询对应id信息]
     * @param  [int] $id [该记录id]
     * @return [json]     [对应id的数据]
     */
    public function getChecked($id)
    {
        $this->ajaxReturn($this->model->findChecked($id));
    }

    public function detail($id)
    {
        $row = $this->model->field(['id', 'name', 'server_ip', 'note', 'operator', 'addtime'])->where(['id' => $id])->find();
        $row['addtime'] = date('Y-m-d H:i:s', $row['addtime']);
        $this->assign(['list' => $row]);
        $this->display('detail');
    }

    public function getCode()
    {
        echo generateCode();
    }

    /**
     * [getGameList 可用游戏列表]
     * @return [array] [游戏名和id组成的二维数组]
     */
    public function getGameList()
    {
        $res = $this->model->table(__GAME__)->field(['id', 'game_name'])->select();
        return $res;
    }

   /**
    * [del 根据id彻底删除数据]
    * @param  [int] $id [该记录id]
    * @return [json]     [1成功 0失败]
    */
    public function del($id)
    {
        if($this->model->delete($id))
            $this->ajaxReturn(['status' => 1]);
        else $this->ajaxReturn(['status' => 0]);
    }

    /**
     * [delCode 删除代理注册码]
     * @param  [int] $id [代理注册码id]
     * @return [json]     [y or n]
     */
    public function delCode($id)
    {
        if(M('carrier_agent')->delete($id))
            $this->ajaxReturn(['status' => 1]);
        else $this->ajaxReturn(['status' => 0]);
    }

    public function test()
    {
        $this->model->queryPage(5);
    }
}