<?php
namespace Admin\Model;

class AdminModel extends CommonModel
{	
	//配置用户数据表名
	protected $patchValidate = true;
	//条件参数  0有就验证,1必须验证,2 当有值而且不为空
	//时间参数 1新增,2,编辑,3both 4自定义(登录)
	protected $_validate = [
        ['name','require','姓名不能为空',0],
		['username','require','用户名不能为空',0],
		['pwd','require','密码不能为空',0], 
		['phone','/^\d{11}$/','手机号码是11位数字',0,'',3],
		['email','email','请输入合法的email地址',0,'',3],
		['pwd2','require','不能为空',0],
		['pwd2','pwd','两次密码不一致',0,'confirm',3],
		['username','','用户名已存在',1,'unique',3],
        ['name','','该姓名已存在',0,'unique',3],
		['email','','Email已存在',0,'unique',3],
        ['oldpwd','require','原始密码不能为空!', 0, '', 2], 
        ['oldpwd','verifyOldPwd','原始密码错误!', 0,'callback', 2], 
    ];

    protected $_validate_login = [
        ['username','require','用户名不能为空',1],
        ['pwd','require','密码不能为空',1], 
        ['username','checkName','用户不存在',1,'callback'],     
        ['username','checkStatus','该用户已被禁用',1,'callback'],  
        ['pwd','checkPwd','密码与用户不匹配', 1,'callback'], 
    ];

	//新增用户自动填充字段值
	protected $_auto = [
		['addtime', 'time', 1, 'function'], //创建时间
		['status', 1, 1],							//状态
		['pwd', 'password_h', 3, 'callback'],		//密码加盐
		['reg_ip', 'get_client_ip', 1, 'function'], //保存注册ip地址
	];

    /**
     * [password_h 密码加盐]
     * @param  [string] $password [原始密码]
     * @return [string]           [加盐密码]
     */
	protected function password_h($password)
	{
		return password_hash($password, PASSWORD_DEFAULT);
	}

	 /**
     * [queryPage 分页显示数据列表]
     * @param  integer $pageSize [分页大小]
     * @param  array   $map      [分页条件]
     * @return [array]           [返回分页查询数据]
     */
    public function queryPage($pageSize = 5, $map = array())
    {	
        $keywords = I('post.keywords');
        if(!empty(trim($keywords)))
        { 
            $where['username'] = ['like', '%'.I('keywords').'%'];
            $where['email'] = ['like', '%'.I('keywords').'%'];
            $where['phone'] = ['like', '%'.I('keywords').'%'];
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        $pagination = getPage($this, $map, 5);
        $btn = $pagination->show();
        $list = $this->alias('a')->order('id desc')->field('a.*,b.role_id')->join('left join __ROLE_USER__ b on a.id = b.user_id')->where($map)->select();
        $status = ['已停用', '已启用'];
        foreach($list as $key => $value)
        {
        	$list[$key]['status'] = $status[$value['status']];
        	// $list[$key]['role'] = $this->getRoleRemark($value['role_id']);
        	$list[$key]['addtime'] = date('Y-m-d H:i:s', $value['addtime']);
        }
        return ['btn' => $btn, 'list' => $list, 'count' => $count, 'status' => 1];
    }
    
    /**
     * [checkName 检查是否存在该用户名]
     * @return [bool]
     */
    protected function checkName()
    {
        if($this->field('id')->where(['username' => I('post.username')])->find())
            return true;
        else return false;
    }

    /**
     * [checkStatus 检查是否被禁用或者删除]
     * @return [bool]
     */
    protected function checkStatus() 
    {
        $map['status'] = 1;
        $map['isdelete'] = 0;
        $map['username'] = I('post.username');
        if($this->field('id')->where($map)->find())
            return true;
        else return false;
    }

    /**
     * [checkPwd 验证密码]
     * @return [bool] [成功或失败]
     */
    public function checkPwd()
    {
        $map['status'] = 1;
        $map['isdelete'] = 0;
        $map['username'] = I('post.username');
        if($row = $this->alias('a')->where($map)->field('a.id,a.username,a.name,a.pwd,b.role_id')->join('left join __ROLE_USER__ b on a.id = b.user_id ')->find())
        {   
            if(password_verify(I('post.pwd'), $row['pwd']))
            {   
                session('node', null); //先删除同PHPSESESSION下可能存在的其他账户信息
                //存储该用户的权限信息
                if($nodeNames = $this->getMyActions($row['role_id']))
                    session('node', $nodeNames);
                if($row['id'] == 1) $row['roleName'] = '超级管理员';
                else $row['roleName'] = M('role')->where(['id' => $row['role_id']])->getField('name');
                session('mi_game_admin', $row);
                return true;
            }
        }
        return false;
    }

    /**
     * [verifyOldPwd 验证用户自己修改密码是是否正确]
     * @return [bool] [返回验证 y or n]
     */
    public function verifyOldPwd()
    {
        if(password_verify(I('post.oldpwd'), session('mi_game_admin.pwd')))
            return true;
        return false;
    }

    /**
     * [getMyActions 获取当前角色的所有权限节点名]
     * @param  [int] $roleID [角色id]
     * @return [array]         [节点名组成的数组]
     */
    public function getMyActions($roleID)
    {   
        if(!empty($roleID))
        {
            $nodeIDs = M('role_node')->where(['role_id' => $roleID])->getField('node_id', true);
            if(count($nodeIDs > 0))
            {
                $nodeIDs = join(',', $nodeIDs);
                $map['id'] = ['in', $nodeIDs];
                $map['level'] = 3;
                if($res = M('node')->where($map)->getField('name', true))
                    return $res;
            }
        }
        return false;
    }

    /**
     * [check_verify description]
     * @param  [string] $code [用户输入的验证码]
     * @return [bool]       [成功或失败]
     */
	protected function check_verify($code, $id = '')
	{    
		$verify = new \Think\Verify(['reset' => false]);
		return $verify->check($code, $id);
	}

    /**
     * [validate_login 登录表单验证]
     * @return [bool] [成功或失败]
     */
    public function validate_login()
    {
        if(!$this->validate($this->_validate_login)->create())
        {
           return false;
        }
        return true;
    }
}