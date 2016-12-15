<?php
return array(
/*	'APP_SUB_DOMAIN_DEPLOY' => 1, //开启子域名配置
	'APP_SUB_DOMAIN_RULES' => array(
		'm.xzkp.com' => 'Home',
	),*/
	'MODULE_DENY_LIST' => array('Common','RunTemp'),
	'DEFAULT_MODULE' => 'Admin',
	
	'URL_CASE_INSENSITIVE' => true,
	'URL_MODEL' => 2,
				
	'DB_TYPE' => 'mysql',
	// 'DB_HOST' => '192.168.3.2',
	'DB_HOST' => 'localhost',
	'DB_NAME' => 'xzkpmonit',
	'DB_USER' => 'root',
	'DB_PWD' => 'root',
	'DB_PORT' => 3306,
	'DB_PREFIX' => '',
	'DB_CHARSET' => 'utf8',
	'DB_DEBUG' => true,
	// 'SHOW_PAGE_TRACE' => true
);