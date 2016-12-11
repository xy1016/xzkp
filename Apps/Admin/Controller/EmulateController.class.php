<?php
namespace Admin\Controller;

class EmulateController extends \Think\Controller
{
	public function carrier()
	{
	/**
     * [param token 身份验证]
     * [emulate 模拟返回更新数据]
     * @return [json] [要统计的信息]
     */    
	//模拟数据
		$document = 
		[	
			//运营商识别标志， 比如verify_code或者唯一的id carrier_id之类
		    'carrier_id' => I('get.carrier_id'),
		    //运营商总收入
		    'income' => mt_rand(100000, 1000000)/100,
		     //运营商总兑奖额
		    'total_exchange' => mt_rand(50000, 500000)/100,
		    //运营商活跃用户
		    'active_users' =>mt_rand(100, 1000),
		    //运营商人均消费
		    'arpu' => mt_rand(10, 1000)/100,
		    //运营商总订单
		    'orders' => mt_rand(1000, 10000),
		    //运营商最大在线数
		    'max_online' => mt_rand(1000, 10000),
		    //运营商总用户数
		    'total_users' => mt_rand(5000, 10000),
		];
		echo json_encode($document);
	}
}