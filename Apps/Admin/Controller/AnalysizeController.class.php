<?php
namespace Admin\Controller;
class AnalysizeController extends CommonController {

    /**
     * [index 查询运营商统计信息]
     * @return [string] [渲染查询到的统计信息到视图]
     */
    public function index()	
    {   
        $res = $this->model->queryPage();
        $this->assign($res);
        $this->display('index');
    }

    /**
     * [updateData 更新运营商统计信息， 同一天重复更新会当天旧有]
     * @return [json] [y or n]
     */
    public function updateData()
    {   
        //获取运营商信息url, 有多少个运营商就有多少个url, 模拟会请求id 1-7的运营商统计信息
        for($i = 1; $i <= 7; $i++)
        {
            $url = 'http://m.xzkp.com/Admin/Emulate/carrier/carrier_id/'.$i;
            if(!empty($data = curlGet($url)))
            {   
                //远端数据需要验证， 防注入
                $data = json_decode($data, true);
                $this->validatePost($data);
                //先检查该运营商是否已有当天的统计数据， 如果无， 则添加， 有则更新
                //目前是按返回运营商当天的数据来设计， 如果返回运营商当天数据， 则要修改
                if($this->model->where(['carrier_id' => $data['carrier_id'], ['updated_time' => date('Y-m-d')] ])->count())
                    $res = $this->model->where(['carrier_id' => $data['carrier_id'], ['updated_time' => date('Y-m-d')] ])->save();
                else $res = $this->model->add();
            }
        }
        $this->ajaxReturn(['status' => 1]);
    }
}