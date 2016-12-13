<?php
namespace Admin\Model;

class AnalysizeModel extends \Think\Model
{	
	protected $tableName = 'carrier_statistics';
    protected $_validate = [
        ['carrier_id','/^\d$/','运营商id不合法', 1],
        ['carrier_id','checkExistence','不存在的运营商', 1, 'callback'],
        ['income','/^\d*\.\d*$/U','营收格式不合法', 1],
        ['arpu','/^\d*\.\d*$/U','arpu格式不合法', 1],
        ['active_users','/^\d+$/U','活跃用户不是整数', 1],
        ['orders','/^\d+$/U','订单数不是整数', 1],
    ];

    protected $_auto = 
    [
        ['updated_time', 'getDate', 3, 'callback'],
    ];

    protected function getDate()
    {
        return date('Y-m-d');
    }

    protected function checkExistence($carrier_id)
    {
        if(M('carrier')->where(['id' => $carrier_id])->count())
            return true;
        return false;
    }

	public function queryPage($map = array())
    {   
        $map['status'] = 1;
        $map['updated_time'] = date('Y-m-d');
        if(I('updated_time') != '')
            $map['updated_time'] = I('updated_time');
        if(I('carrierID') != '')
        {
            $map['carrier_id'] = I('carrierID');
        } 
        $pagination = getPage($this, $map, 5);
        $btn = $pagination->show();
        $list = $this->alias('a')->order('b.id asc')->field('a.income, a.active_users, a.arpu, a.orders, b.name, b.id')->join('join __CARRIER__ b on a.carrier_id = b.id')->where($map)->select();
        if(count($list) == 0) $list = null;
        unset($map['updated_time']);
        $sum = $this->order('updated_time asc')->field('updated_time, sum(income) as total_income')->where($map)->group('updated_time')->limit(14)->select();
        //数据整理下再输出到前端
        $chartTitle = (count($list) > 1) ? '运营商最近14天营收图':$list[0]['name'].'最近30天营收图';
        foreach($sum as $value)
        {     
            $arr['total_income'][] = $value['total_income'];
            $arr['updated_time'][] = $value['updated_time'];
        }
        foreach($arr as $k => $v)
        {
            $arr[$k] = json_encode($arr[$k]);
        }
        //查处下拉搜索框的运营商信息
        $carriers = M('carrier')->where(['status' => 1])->field(['id', 'name'])->select();
        return ['list' => $list, 'statistics' => $arr, 'btn' => $btn, 'chartTitle' => $chartTitle, 'carriers' => $carriers];
    }
}