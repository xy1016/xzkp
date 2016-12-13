<?php 
namespace Think\Session\Driver;

class Mssqldb {

    /**
     * Session有效时间
     */
   protected $lifeTime      = ''; 

    /**
     * 数据库句柄
     */
   protected $hander; 
   

   public function __construct(){
   		$this->hander = D('Session');
   }

    public function open() { 
    	$this->lifeTime = C('SESSION_EXPIRE')?C('SESSION_EXPIRE'):ini_get('session.gc_maxlifetime');
    	return true;
    } 


   public function close() {
       $this->gc($this->lifeTime); 
   } 


	public function read($sessID) { 
		$data = $this->hander->where("session_id='$sessID' and session_expire>".time())->getField('session_data');
		if($data){
			return $data;
		}else{
			return '';
		}
	} 

	public function write($sessID,$sessData) { 
		if(session('se_admin_id')){
			$this->hander->where("admin_id=".session('se_admin_id')." and session_id<>'$sessID'")->delete();
		}
		$data['session_data'] = $sessData;
		$data['session_expire'] = time() + $this->lifeTime;
		$data['admin_id'] = session('se_admin_id');
		if($this->hander->where("session_id='$sessID'")->count()){
			if($this->hander->where("session_id='$sessID'")->save($data)){
				return true;
			}
		}else{
			$data['session_id'] = $sessID;
			if($this->hander->add($data)){
				return true;
			}
		}
		return false;
	} 


	public function destroy($sessID) { 
		return $this->hander->where("session_id='".$sessID."'")->delete();
	} 

	public function gc($sessMaxLifeTime) { 
		return $this->hander->where("session_expire<".time())->delete();	
	} 

}