<?php
/**
 * 分页类传入一些参数， 修改分页页码大小以及更改样式等
 * @param $m 模型，引用传递
 * @param $where 查询条件
 * @param int $pagesize 每页查询条数
 * @return \Think\Page
 */
function getPage(&$m, $where, $pagesize = 10){
    // $m1 = clone $m;//浅复制一个模型
    $count = $m->where($where)->count();//连惯操作后会对join等操作进行重置
    // $m = $m1;//为保持在为定的连惯操作，浅复制一个模型
    $p = new Think\Page($count,$pagesize);
    // $p->setConfig('header','<li class="rows">共<b>%TOTAL_ROW%</b>条记录&nbsp;&nbsp;每页<b>%LIST_ROW%</b>条&nbsp;&nbsp;第<b>%NOW_PAGE%</b>页/共<b>%TOTAL_PAGE%</b>页</li>');
    $p->setConfig('prev','上一页');
    $p->setConfig('next','下一页');
    $p->setConfig('last','末页');
    $p->setConfig('first','首页');
    // $p->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
    $p->setConfig('theme', "%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%") ;
    $p->parameter=I('get.');
    $m->limit($p->firstRow,$p->listRows);
    return $p;
}

/**
 * [ipToInt ip转化为证书保存]
 * @param  [string] $ip [ip地址]
 * @return [int]     [转换为整型的ip地址]
 */
function ipToInt($ip)
{
    return sprintf('%u',ip2long($ip));
}

/**
 * [getCode 生成随机字符串]
 * @return [string] [21位随机字符加下划线拼接的当前时间戳]
*/
function generateCode($strlen = 31, $headChar = false)
{   
    if(!empty($strlen)) $strlen = ($strlen >= 21) ? $strlen-11:11;
    $stack = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $len = strlen($stack);
    $str = '';
    for($i = 0; $i <= $strlen; $i++)
    {
        $str .= $stack[mt_rand(0, $len)];
    }
    $str .= '_' . time();
    if(!empty($headChar)) $str = $headChar.$str;
    return $str;
}

/**
 * curl扩展模拟get请求
 * @param  string $url 要请求的地址
 * @return array      请求到的数据转换后的数组
 */
function curlGet($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $res = curl_exec($ch);
    curl_close();
    return $res;
}

/**
 * curl模拟POST请求
 * @param  string $url  要请求的接口地址
 * @param  array $data 要传的POST数据
 * @return array       最终转换后的数据数组
 */
function curlPost($url, $data = array()) 
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);  //设置以POST方式请求
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    //请求到的数据，以结果的方式返回
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $res = curl_exec($ch);
    curl_close();
    return json_decode($res, true);
}