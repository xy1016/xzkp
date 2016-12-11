<?php
namespace Admin\Model;
class RoleModel extends \Think\Model\RelationModel
{   
    protected $patchValidate = true;
    protected $_validate = [
        ['name', 'require', '角色名称不能为空', 1, '', 3], 
        ['name', '/^\w{3,30}$/', '3-20位字母数字下划线', 1, '', 3], 
        ['name', '', '该角色名已存在', 1, 'unique', 3], 
        ['remark','require','描述不能为空',1,'',3], 
        // ['pid','require','请至少选择一个权限',1,'',3], 
    ];

    protected $_link = [
        'admin' => [    //admin目标表表名
            'mapping_type'  => self::MANY_TO_MANY,
            // 'mapping_name'  => 'username',       //目标表表名跟当前模型的字段有冲突的时候使用 
            'mapping_fields' => 'username',         //限定要查询的目标表的字段,默认查出所有字段
            'foreign_key'   => 'role_id',           //中间表中自己这个模型的外键
            'relation_foreign_key' => 'user_id',    //目标表的外键
            'relation_table' => 'shop_role_user',   //中间表表名
        ],
    ];


    public function queryPage($pageSize = 5)
    {   
        
        $count = $this->where($map)->count();
        $pagination = new \Think\Page($count,$pageSize);
        $btn = $pagination->show();
        $list = $this->order('id desc')->limit($pagination->firstRow.','.$pagination->listRows)->relation(true)->select();
        return ['btn' => $btn, 'list' => $list, 'count' => $count, 'status' => 1];
    }

    public function getData()
    {
         $map['status'] = 1;
         $map['level'] = 1;
         $modules = $this->table(__NODE__)->where($map)->field(['id','remark'])->select();
         foreach($modules as $module)
         {
            $collect['module'] = $module;
            $map['pid'] = $module['id'];
            $map['level'] = 2;
            $controllers = $this->field(['id','remark'])->table(__NODE__)->where($map)->select();
            $map['level'] = 3;
            unset($collect['controller']);
            foreach($controllers as $controller)
            {
                $map['pid'] = $controller['id'];
                $actions = $this->field(['id','remark'])->table(__NODE__)->where($map)->select();
                $collect['controller'][] = ['con' => $controller, 'action' => $actions];
            }
            $list[] = $collect;
         }
         dump($list[0]['controller']);
        return ['list' => $list];
    } 

}