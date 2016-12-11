<?php
namespace Admin\Model;

class CarrierModel extends CommonModel
{	
	protected $patchValidate = true;
	//条件参数  0有就验证,1必须验证,2 当有值而且不为空
	//时间参数 1新增,2,编辑,3both 4自定义(登录)
	protected $_validate = [
        ['name','require','运营商不能为空',0],
        ['name','','该运营商名已存在',0,'unique',3],
        ['server_ip','/^((25[0-5]|2[0-4]\d|[01]?\d\d?)($|(?!\.$)\.)){4}$/','服务器IP地址不合格',0],
    ];

	//自动填充字段值
	protected $_auto = [
		['addtime', 'time', 1, 'function'], //创建时间
        ['operator', 'getOperator', 1, 'callback'], //添加人
        ['server_ip', 'toInt', 3, 'callback'], 
	];

/*    protected $_link = [
        'game' => [    
            'mapping_type'  => self::MANY_TO_MANY,
            'foreign_key'   => 'carrier_id',           
            'relation_foreign_key' => 'game_id',    
            'relation_table' => 'carrier_game',   
        ],

    ];*/

    /**
     * [toInt description]
     * @param  [ipv4] $server_ip [ip地址]
     * @return [int] [转换为整型的ip地址]
     */
    protected function toInt($server_ip)
    {
        return sprintf('%u',ip2long($server_ip));
    }

    protected function getOperator()
    {
        return session('mi_game_admin.username');
    }

    /**
     * [queryPage 分页显示数据列表]
     * @param  integer $pageSize [分页大小]
     * @param  array   $map      [分页条件]
     * @return [array]           [返回分页查询数据]
     */
    public function queryPage()
    {	
        $keywords = I('get.name');
        if(!empty(trim($keywords)))
        { 
            $map['name'] = ['like', '%'.I('get.name').'%'];
        }
        $pagination = getPage($this, $map);
        $btn = $pagination->show();
        // $list = $this->order('id desc')->where($map)->relation(true)->select();
        $list = $this->order('id desc')->where($map)->select();
        $status = ['未开启', '已开启', '维护'];
        foreach($list as $key => $value)
        {

        	$list[$key]['addtime'] = date('Y-m-d H:i:s', $value['addtime']);
            $list[$key]['status'] = $status[$value['status']];
            //如果有被授权的游戏
            if($gameIDs = M('carrier_game')->where(['carrier_id' => $value['id']])->getField('game_id', true))
            {
                $map['id'] = ['in', join(',', $gameIDs)];
                $list[$key]['game'] = M('game')->where($map)->getField('game_name', true);
            }
        }
        return ['btn' => $btn, 'list' => $list, 'count' => $count, 'status' => 1];
    }

    public function queryAgent($map = array())
    {
        $model = M('carrier_agent');
        $pagination = getPage($model, $map, 5);
        $btn = $pagination->show();
        $list = $model->order('addtime desc')->field(['id', 'agent_level', 'state', 'reg_code'])->where($map)->select();
        $state = ['未使用', '已使用'];
        foreach($list as $key => $value)
        {
            $list[$key]['state'] = $state[$value['state']];
        }
        $list = $this->buildAjaxOutPut($list);
        return ['btn' => $btn, 'list' => $list];
    }

    public function buildAjaxOutPut($list)
    {
        $output = '';
        foreach($list as $row)
        {
            $useState = ($row['state'] == '已使用') ? 'used' : 'unused';
            $output .= <<<Eof
            <div class="row">
                <div class="form-group" style="padding-left:1.2em;margin-bottom:3em">
                    <div class="col-sm-12">
                    <div class="col-sm-2 {$useState}">
                        {$row['state']}
                    </div>
                    <div class="col-sm-8" style="padding-top:3px">
                        <span class="reg_code">{$row['agent_level']}级代理员注册码：{$row['reg_code']}</span>
                    </div>
                     <div class="col-sm-1">
                        <button type="button" data-myid="{$row['id']}" title="删除该注册码" class="btn btn-danger btn-xs delBtn" style="padding:3px 18px"> <i class="fa fa-remove"></i> 删 除</button>
                    </div>
                    </div>
                </div>
            </div>
Eof;
        }
        return $output;
    }

    /**
     * [create 存储新建运营商]
     * @return [bool]
     */
    public function createData()
    {
        $this->startTrans();
        if(!empty($id = $this->add()))  
        {   
            if(count($_POST['game_name']) > 0)
            { 
                foreach($_POST['game_name'] as $value)
                {
                    $data['carrier_id'] = $id;
                    $data['game_id'] = $value;
                    $dataList[] = $data;
                }
                if(!M('carrier_game')->addAll($dataList))
                {
                    $this->rollback();
                    return false;
                }
            }
        }
        $this->commit();
        return true;
    }

    /**
     * [updateData 更新运营商信息，有授权游戏， 删除旧的重新添加， 没有提交游戏过来， 全部删除]
     * @param  [int] $id [运营商id]
     * @return [bool]    
     */
    public function updateData($id)
    {
        if(false !== $this->save())  
        {   
            $carrier_game = M('carrier_game');
            //如果有授权游戏提交过来，正常流程
            if(count($_POST['game_name']) > 0)
            { 
                foreach($_POST['game_name'] as $value)
                {
                    $data['carrier_id'] = $id;
                    $data['game_id'] = $value;
                    $dataList[] = $data;
                }
                //先删再添加
                if(false === $carrier_game->where(['carrier_id' => $id])->delete() || !$carrier_game->addAll($dataList))
                {
                    $this->rollback();
                    return false;
                }
            }
            else //否则删除所有授权游戏
            {
                if(false === $carrier_game->where(['carrier_id' => $id])->delete())
                {
                    $this->rollback();
                    return false;
                }
            }
            $this->commit();
            return true;
        }
        else return false;    
    }

    public function updateDesk()
    {   
        $post = I('post.');
        $map['carrier_id'] = $post['id'];
        $map['game_id'] = $post['game_id'];
        $model = new self('carrier_game');
        if($model->where($map)->setField($post['column'], $post['value']))
        {
            return true;
        }
        return false;
    } 

    /**
     * [getCarrierDesk 查询运营商名称，id, 授权的游戏，每款游戏桌数，充值预警，日活预警， 以及游戏的具体信息， 如名称]
     * @param  [int] $id [运营商id]
     * @return [arr]     [返回运营商信息和授权游戏相关信息]
     */
    public function getCarrierDesk($id)
    {
        $rows = $this->alias('a')->field('a.name, a.id, b.game_id, b.practise_desk, b.arena_desk, b.charge_w, b.daily_aw')->join('left join __CARRIER_GAME__ b on a.id = b.carrier_id')->where(['a.id' => $id])->select();
        // 如果返回记录只有一条， 并且这条中的游戏id还为空，那么该运营商没有被授权任何游戏
        if(count($rows) == 1 && empty($rows[0]['game_id']))
        {
            $list['game'] = null;
        }
        else
        {
            foreach($rows as $row)
            {
                $arr = '';
                $arr['game_id'] = $row['game_id'];
                $arr['practise_desk'] = $row['practise_desk'];
                $arr['arena_desk'] = $row['arena_desk'];
                $arr['charge_w'] = $row['charge_w'];
                $arr['daily_aw'] = $row['daily_aw'];
                $arr['game_name'] = M('game')->where(['id' => $row['game_id']])->getField('game_name');
                $list['game'][] = $arr;
            }
        }
        $list['id'] = $rows[0]['id'];
        $list['name'] = $rows[0]['name'];
        return $list;
    }

    /**
     * [findChecked 查询运营商已被授权的游戏]
     * @param  [int] $id [运营商id]
     * @return [arr]     [运营商和有授权的游戏信息]
     */
    public function findChecked($id)
    {
        $arr = $this->alias('a')->field('a.id,b.game_id')->join('join __CARRIER_GAME__ b on a.id = b.carrier_id')->where(['a.id' => $id])->getField('game_id', true);
        return $arr;
    }
}