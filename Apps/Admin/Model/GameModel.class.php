<?php
namespace Admin\Model;

class GameModel extends CommonModel
{	
	//配置用户数据表名
	protected $patchValidate = true;
	//条件参数  0有就验证,1必须验证,2 当有值而且不为空
	//时间参数 1新增,2,编辑,3both 4自定义(登录)
	protected $_validate = [
        ['game_name','require','游戏名不能为空',0],
		['desc','require','描述不能为空',0],
		['game_name','','游戏名已存在',1,'unique',3],
    ];

	//新增用户自动填充字段值
	protected $_auto = [
		['addtime', 'time', 1, 'function'], //创建时间						//状态
		['reg_ip', 'getIp', 1, 'callback'], //保存操作ip地址
	];

    protected function getIp()
    {
        return ip2long(get_client_ip());
    }
	//分页查询
	//pageSize 分页数; $map 分页过滤条件
    public function queryPage()
    {	
      $keywords = I('post.keywords');
      if(!empty(trim($keywords)))
      { 
         $where['game_name'] = ['like', '%'.I('keywords').'%'];
         $where['desc'] = ['like', '%'.I('keywords').'%'];
         $where['_logic'] = 'or';
         $map['_complex'] = $where;
      }
    	$pagination = getPage($this, $map, 5);
    	$btn = $pagination->show();
    	$list = $this->where($map)->select();
    	foreach($list as $key => $value)
    	{
    		$list[$key]['addtime'] = date('Y-m-d H:i:s', $value['addtime']);
        $list[$key]['reg_ip'] = long2ip($value['reg_ip']);
    	}
    	return ['btn' => $btn, 'list' => $list, 'count' => $count, 'status' => 1];
    }
}