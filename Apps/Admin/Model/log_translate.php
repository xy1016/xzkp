<?php
/**
 * 数据库表名和字段以及字段值翻译
 */
return 
[	
	'game' => 
	[
		'tableName' => '游戏',
		'game_name' => '游戏名',
		'desc' => '描述',
		'isdelete' => ['状态' => ['正常', '删除']],
	],
	'admin' => 
	[
		'tableName' => '管理员',
		'pwd' => '密码',
		'username' => '用户名',
		'name' => '姓名',
		'email' => '邮箱',
		'phone' => '手机号码',
		'sex' => '性别',
		'isdelete' => ['存活' => ['正常', '删除']],
		'status' => ['状态' => ['停用', '启用']],
	],
	'carrier' => 
	[
		'tableName' => '运营商',
		'name' => '名称',
		'server_ip' => '服务器IP',
		'verify_code' => '开机码',
		'note' => '备注',
		'operator' => '添加人',
		'status' => ['服务器状态' => ['停用', '启用']],
	],
	'carrier_agent' => 
	[
		'tableName' => '运营商代理',
		'agent_level' => '代理商级别',
		'reg_code' => '代理商注册码',
		'state' => ['注册码状态' => ['未使用', '已使用']],
	],
	'carrier_game' => 
	[
		'tableName' => '运营商游戏',
		'practise_desk' => '练习厅桌数',
		'arena_desk' => '竞技厅桌数',
		'charge_w' => '充值预警',
		'daily_w' => '日活预警',
		'state' => ['注册码状态' => ['未使用', '已使用']],
	],
];