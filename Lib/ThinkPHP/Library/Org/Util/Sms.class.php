<?php
namespace Org\Util;

/*错误码对照表
 * -1:消息个数大于5
 *-2:消息内容不是base64格式
 *-3:没有消息内容
 *-4:没有目的号码
 *-5:desttype填写错误
 *-6:手机号码不正确
 *-7:扩展号码超长
 *-8:needreply填写错误
 *-9:余额不足
 *-10:数据库准备失败
 *-11:用户名密码错误
 *-12:xml格式错误
 *-13:Action填写错误
 *-13:不正确的Action
 *-14:消息内容加上签名长度大于201字
 *-15:signid填写错误，未找到对应签名
 *-16:联通全网发送失败
 *-17:联通全网下发源号码配置错误，与管理员确认
 *-18:该签名为指定号码下发，不支持长号码扩展
 *-19:短信内容含有敏感词汇
 *-20：usermsgid长度不等于12位
 *-21：usermsgid不是由数字和英文字母组成
 *-22：内容中含有全角的左方括号
 *-23:签名错误
 *-24:模板编号错误
 *-25:发送内容不匹配模板
 *后台登录地址：http://121.42.158.175:8057/dmlmControl/message/login.jsp
 *后台登录帐号：090101，密码：112112
 * */
class Sms{
	private $destid = array(); //目标号码
	private $msg; //短信内容
	private $error; //错误信息
	private $err_code; //错误代码
	private $way; //发送通道,0=北京讯博，1=上海联逾
	public function __construct($destid, $msg, $way=0){
		$this->destid = $destid;
		$this->msg = $msg;
		$this->way = $way;
	}
	
	/*
	 * 发送短信
	 * */
	public function send(){
		switch($this->way){
			case 0:
				return $this->_wayxb();
				break;
			case 1:
				return $this->_wayly();
				break;
			default:
				return false;
		}
	}
	
	/*
	 * 北京讯博无限科技有限公司
	 * */
	private function _wayxb(){
		$msgid = substr(uniqid(),0,12);
		$spid = '069574514f634504bf699f6d13236557';
		$url = 'http://121.42.158.175:8057/messageControl';
		
		$xml = '<?xml version="1.0" encoding="utf-8"?>';
		$xml .= '<Body>';
		$xml .= '<channelId>'.$spid.'</channelId>';
		$xml .= '<submit>';
		$xml .= '<usermsgid>'.$msgid.'</usermsgid>';
		$xml .= '<desttermid>'.$this->destid[0].'</desttermid>';
		$xml .= '<msgcontent>'.base64_encode($this->msg).'</msgcontent>';
		$xml .= '<tempid>115</tempid>';
		$xml .= '</submit>';
		$xml .= '</Body>';
		$params = $xml;
		$headers[] = 'Content-Type: text/xml; charset=utf-8';
		$headers[] = 'Action:submitreq_new';
		
		$ch=curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		$response = curl_exec($ch);
		curl_close($ch);
		if($response != ''){
			$response = json_decode($response);
			if($response->result == '0'){
				return true;
			}else{
				$this->error = '短信发送失败';
				$this->err_code = $response->result;
				return false;
			}
		}else{
			$this->error = '短信发送失败';
			return false;
		}			
	}
	
	/*
	 * 上海联逾信息技术有限公司
	 * */
	private function _wayly(){
		//http://lianyus.com:8080
		$url = 'http://115.29.44.189:8080/sms/smsInterface.do';
		$username = 'maiyouhudong';
		$password = 'Migame26608665';
		$params = "username=".$username."&password=".$password."&mobile=".$this->destid[0]."&content=".$this->msg."&planTime=";
		$ch=curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		$response = curl_exec($ch);
		curl_close($ch);
		if($response){
			$obj = simplexml_load_string($response);
			if($obj->resultcode == '0'){
				return true;
			}else{
				$this->error = '短信发送失败';
				$this->err_code = $obj->resultcode;
				return false;
			}
		}else{
			$this->error = '短信发送失败';
			return false;
		}	
	}
	
/* 	public function singleSend(){
		$msgid = substr(uniqid(),0,12);
		$xml = '<?xml version="1.0" encoding="utf-8"?>';
		$xml .= '<Body>';
		$xml .= '<channelId>'.$this->spid.'</channelId>';
		$xml .= '<submit>';
		$xml .= '<usermsgid>'.$msgid.'</usermsgid>';
		$xml .= '<desttermid>'.$this->destid[0].'</desttermid>';
		$xml .= '<msgcontent>'.base64_encode($this->msg).'</msgcontent>';
		$xml .= '<tempid>115</tempid>';
		$xml .= '</submit>';
		$xml .= '</Body>';
		$params = $xml;
		$headers[] = 'Content-Type: text/xml; charset=utf-8';
		$headers[] = 'Action:submitreq';
		
		$ch=curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		$response = curl_exec($ch);
		curl_close($ch);
		if($response != ''){
			$response = json_decode($response);
			$log_content = "date[".date('Y/m/d H:i:s')."]mobile[".$this->destid[0]."]msgid[".$msgid."]msg[".$this->msg."]result[".$response->result."]\n";
			$log_file = C('LOG_PATH')."SMS_".date('Y-m-d').".log";
			write_log_file($log_file, $log_content);
			if($response->result == '0'){
				return true;
			}else{
				$this->error = '短信发送失败';
				$this->err_code = $response->result;
				return false;				
			}
		}else{
			$this->error = '短信发送失败';
			return false;
		}	
	}
	 */
	public function getError(){
		return $this->error;
	}
	
	public function getErrcode(){
		return $this->err_code;
	}

}