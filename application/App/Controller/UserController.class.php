<?php

namespace App\Controller;

use Common\Controller\HomebaseController;

class UserController extends HomebaseController {
	function index() {
		echo 123;
	}
	// 登录验证
	function dologin() {
		header ( 'Content-type: application/json' );
		$return = array (
				'code' => 0,
				'message' => '' 
		);

	    if(sp_is_user_login()){ //已经登录时直接跳到首页
                $return['code']=999;
	        $return['message']="已经登录";
			exit ( json_encode ( $return ) );
	    }	
		if (! sp_check_verify_code ()) {
			$return ['code'] = 1;
			$return ['message'] = "验证码错误！";
			exit ( json_encode ( $return ) );
		}
		
		$users_model = M ( "Users" );
		$rules = array (
				// array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
				array (
						'username',
						'require',
						'手机号/邮箱/用户名不能为空！',
						1 
				),
				array (
						'password',
						'require',
						'密码不能为空！',
						1 
				) 
		)
		;
		if ($users_model->validate ( $rules )->create () === false) {
			$return ['code'] = 2;
			$return ['message'] = $users_model->getError ();
			exit ( json_encode ( $return ) );
		}
		
		$username = $_POST ['username'];
		
		if (preg_match ( '/^\d+$/', $username )) { // 手机号登录
			$return  = $this->_do_mobile_login ();
		} else {
			$return  = $this->_do_email_login (); // 用户名或者邮箱登录
		}
        $userinfo = $_SESSION['user'];
        // 处理用户头像
        if ($userinfo['avatar']) {
            $userinfo['avatar'] = "http://" . $_SERVER["SERVER_NAME"] . "/data/upload/avatar/" . $userinfo['avatar'];
        }
        
        $return['data'] = $userinfo;
		exit ( json_encode ( $return ) );
	}
	private function _do_mobile_login() {
		$users_model = M ( 'Users' );
		$where ['mobile'] = $_POST ['username'];
		$password = $_POST ['password'];
		$result = $users_model->where ( $where )->find ();
		
		if (! empty ( $result )) {
			if (sp_compare_password ( $password, $result ['user_pass'] )) {
				$_SESSION ["user"] = $result;
				// 写入此次登录信息
				$data = array (
						'last_login_time' => date ( "Y-m-d H:i:s" ),
						'last_login_ip' => get_client_ip ( 0, true ) 
				);
				$users_model->where ( array (
						'id' => $result ["id"] 
				) )->save ( $data );
				$redirect = empty ( $_SESSION ['login_http_referer'] ) ? __ROOT__ . "/" : $_SESSION ['login_http_referer'];
				$_SESSION ['login_http_referer'] = "";
				$return ['code'] = 0;
				$return ['message'] = "登录验证成功！";
				return $return;
			} else {
				$return ['code'] = 3;
				$return ['message'] = "密码错误！";
				return $return;
			}
		} else {
			$return ['code'] = 4;
			$return ['message'] = "用户名不存在！";
		}
		
		return $result;
	}
	private function _do_email_login() {
		$username = $_POST ['username'];
		$password = $_POST ['password'];
		
		if (strpos ( $username, "@" ) > 0) { // 邮箱登陆
			$where ['user_email'] = $username;
		} else {
			$where ['user_login'] = $username;
		}
		$users_model = M ( 'Users' );
		$result = $users_model->where ( $where )->find ();
		$ucenter_syn = C ( "UCENTER_ENABLED" );
		
		$ucenter_old_user_login = false;
		
		$ucenter_login_ok = false;
		if ($ucenter_syn) {
			setcookie ( "thinkcmf_auth", "" );
			include UC_CLIENT_ROOT . "client.php";
			list ( $uc_uid, $username, $password, $email ) = uc_user_login ( $username, $password );
			
			if ($uc_uid > 0) {
				if (! $result) {
					$data = array (
							'user_login' => $username,
							'user_email' => $email,
							'user_pass' => sp_password ( $password ),
							'last_login_ip' => get_client_ip ( 0, true ),
							'create_time' => time (),
							'last_login_time' => time (),
							'user_status' => '1',
							'user_type' => 2 
					);
					$id = $users_model->add ( $data );
					$data ['id'] = $id;
					$result = $data;
				}
			} else {
				
				switch ($uc_uid) {
					case "-1" : // 用户不存在，或者被删除
						if ($result) { // 本应用已经有这个用户
							if (sp_compare_password ( $password, $result ['user_pass'] )) { // 本应用已经有这个用户,且密码正确，同步用户
								$uc_uid2 = uc_user_register ( $username, $password, $result ['user_email'] );
								if ($uc_uid2 < 0) {
									$uc_register_errors = array (
											"-1" => "用户名不合法",
											"-2" => "包含不允许注册的词语",
											"-3" => "用户名已经存在",
											"-4" => "Email格式有误",
											"-5" => "Email不允许注册",
											"-6" => "该Email已经被注册" 
									);
									$return ['code'] = 5;
									$return ['message'] = "同步用户失败--" . $uc_register_errors [$uc_uid2];
									return $return;
								}
								$uc_uid = $uc_uid2;
							} else {
								$return ['code'] = 3;
								$return ['message'] = "密码错误！";
								return $return;
							}
						}
						
						break;
					case - 2 : // 密码错
						if ($result) { // 本应用已经有这个用户
							if (sp_compare_password ( $password, $result ['user_pass'] )) { // 本应用已经有这个用户,且密码正确，同步用户
								$uc_user_edit_status = uc_user_edit ( $username, "", $password, "", 1 );
								if ($uc_user_edit_status <= 0) {
									$return ['code'] = 6;
									$return ['message'] = "登陆错误！";
									return $return;
								}
								list ( $uc_uid2 ) = uc_get_user ( $username );
								$uc_uid = $uc_uid2;
								$ucenter_old_user_login = true;
							} else {
								$return ['code'] = 3;
								$return ['message'] = "密码错误！";
								return $return;
							}
						} else {
							$return ['code'] = 3;
							$return ['message'] = "密码错误！";
							return $return;
						}
						
						break;
				}
			}
			$ucenter_login_ok = true;
			echo uc_user_synlogin ( $uc_uid );
		}
		// exit();
		if (! empty ( $result )) {
			if (sp_compare_password ( $password, $result ['user_pass'] ) || $ucenter_login_ok) {
				$_SESSION ["user"] = $result;
				// 写入此次登录信息
				$data = array (
						'last_login_time' => date ( "Y-m-d H:i:s" ),
						'last_login_ip' => get_client_ip ( 0, true ) 
				);
				$users_model->where ( "id=" . $result ["id"] )->save ( $data );
				$redirect = empty ( $_SESSION ['login_http_referer'] ) ? __ROOT__ . "/" : $_SESSION ['login_http_referer'];
				$_SESSION ['login_http_referer'] = "";
				$ucenter_old_user_login_msg = "";
				
				if ($ucenter_old_user_login) {
					// $ucenter_old_user_login_msg="老用户请在跳转后，再次登陆";
				}
				
				$return ['code'] = 0;
				$return ['message'] = "登录验证成功！";
				return $return;
			} else {
				$return ['code'] = 3;
				$return ['message'] = "密码错误！";
				return $return;
			}
		} else {
			$return ['code'] = 4;
			$return ['message'] = "用户名不存在！";
			return $return;
		}
		
		return $return;
	}
	
	
	//退出
	public function logout(){
	    
	    header ( 'Content-type: application/json' );
	    $return = array (
	        'code' => 0,
	        'message' => '退出登陆成功！'
	    );

	    $ucenter_syn=C("UCENTER_ENABLED");
	    $login_success=false;
	    if($ucenter_syn){
	        include UC_CLIENT_ROOT."client.php";
	        echo uc_user_synlogout();
	    }
	    session("user",null);//只有前台用户退出
	    
	    exit ( json_encode ( $return ) );
	    
	}

    function favorite()
    {
        header('Content-type: application/json');
        $return = array(
            'code' => 0,
            'message' => ''
        );
        
        $uid = $_REQUEST['uid'];
        $user_favorites_model = M("UserFavorites");
        $favorites = $user_favorites_model->where("uid=$uid")->select();
        foreach ($favorites as &$v) {
            $v["url"] = "http://".$_SERVER['SERVER_NAME']. $v["url"]."?app=1";
        }
        $return['data'] = $favorites;
        
        exit(json_encode($return));
    }
    
    function doregister(){
        header('Content-type: application/json');
        $return = array(
            'code' => 0,
            'message' => ''
        );
         
        if(isset($_POST['email'])){
            	
            //邮箱注册
           exit(json_encode($this->_do_email_register()));
            	
        }elseif(isset($_POST['mobile'])){
            	
            //手机号注册
            exit(json_encode($this->_do_mobile_register()));
            	
        }else{
            $return["code"]=22;
            $return["message"]="注册方式不存在！";
            exit(json_encode($return));
        }
         
    }
    
    private function _do_mobile_register(){
    
        if(!sp_check_mobile_verify_code()){
            
            $return["message"]="手机验证码错误！";
        }
        $rules = array(
            //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
            array('mobile', 'require', '手机号不能为空！', 1 ),
            array('password','require','密码不能为空！',1),
        );
         
        $users_model=M("Users");
    
        if($users_model->validate($rules)->create()===false){
            $return["code"] = 11;
            $return["message"]=$users_model->getError();
            return $return;
        }
    
        $password=$_POST['password'];
        $mobile=$_POST['mobile'];
    
        if(strlen($password) < 5 || strlen($password) > 20){
            $return["code"] = 12;
            $return["message"]="密码长度至少5位，最多20位！";
            return $return;
        }
    
         
        $where['mobile']=$mobile;
    
        $users_model=M("Users");
        $result = $users_model->where($where)->count();
        if($result){
            $return["code"] = 13;
            $return["message"]="手机号已被注册！";
            return $return;
        }else{
    
            $data=array(
                'user_login' => '',
                'user_email' => '',
                'mobile' =>$_POST['mobile'],
                'user_nicename' =>'',
                'user_pass' => sp_password($password),
                'last_login_ip' => get_client_ip(0,true),
                'create_time' => date("Y-m-d H:i:s"),
                'last_login_time' => date("Y-m-d H:i:s"),
                'user_status' => 1,
                "user_type"=>2,//会员
            );
            $rst = $users_model->add($data);
            if($rst){
                //登入成功页面跳转
                $data['id']=$rst;
                $_SESSION['user']=$data;
                $return["code"] = 0;
                $return["message"]="注册成功！";
                $userinfo = $_SESSION['user'];
                    // 处理用户头像
                if ($userinfo['avatar']) {
                    $userinfo['avatar'] = "http://" . $_SERVER["SERVER_NAME"] . "/data/upload/avatar/" . $userinfo['avatar'];
                }
                $return ['data'] = $userinfo;
                return $return;
                 
            }else{
                $return["code"] = 14;
                $return["message"]="注册失败！";
                return $return;
            }
    
        }
    }
    
    private function _do_email_register(){
    
        if(!sp_check_verify_code()){
            $return["code"] = 15;
            $return["message"]="验证码错误！";
            return $return;
        }
    
        $rules = array(
            //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
            array('email', 'require', '邮箱不能为空！', 1 ),
            array('password','require','密码不能为空！',1),
            array('repassword', 'require', '重复密码不能为空！', 1 ),
            array('repassword','password','确认密码不正确',0,'confirm'),
            array('email','email','邮箱格式不正确！',1), // 验证email字段格式是否正确
             
        );
         
    
        $users_model=M("Users");
    
        if($users_model->validate($rules)->create()===false){
            $return["code"] = 16;
            $return["message"]=$users_model->getError();
            return $return;
        }
    
        $password=$_POST['password'];
        $email=$_POST['email'];
        $username=str_replace(array(".","@"), "_",$email);
        //用户名需过滤的字符的正则
        $stripChar = '?<*.>\'"';
        if(preg_match('/['.$stripChar.']/is', $username)==1){
            $return["code"] = 17;
            $return["message"]='用户名中包含'.$stripChar.'等非法字符！';
            return $return;
        }
    
        // 	    $banned_usernames=explode(",", sp_get_cmf_settings("banned_usernames"));
    
        // 	    if(in_array($username, $banned_usernames)){
        // 	        $return["message"]="此用户名禁止使用！");
        // 	    }
    
        if(strlen($password) < 5 || strlen($password) > 20){
            $return["code"] = 18;
            $return["message"]="密码长度至少5位，最多20位！";
            return $return;
        }
         
        $where['user_login']=$username;
        $where['user_email']=$email;
        $where['_logic'] = 'OR';
         
        $ucenter_syn=C("UCENTER_ENABLED");
        $uc_checkemail=1;
        $uc_checkusername=1;
        if($ucenter_syn){
            include UC_CLIENT_ROOT."client.php";
            $uc_checkemail=uc_user_checkemail($email);
            $uc_checkusername=uc_user_checkname($username);
        }
    
        $users_model=M("Users");
        $result = $users_model->where($where)->count();
        if($result || $uc_checkemail<0 || $uc_checkusername<0){
            $return["code"] = 19;
            $return["message"]="用户名或者该邮箱已经存在！";
            return $return;
        }else{
            $uc_register=true;
            if($ucenter_syn){
    
                $uc_uid=uc_user_register($username,$password,$email);
                //exit($uc_uid);
                if($uc_uid<0){
                    $uc_register=false;
                }
            }
            if($uc_register){
                $need_email_active=C("SP_MEMBER_EMAIL_ACTIVE");
                $data=array(
                    'user_login' => $username,
                    'user_email' => $email,
                    'user_nicename' =>$username,
                    'user_pass' => sp_password($password),
                    'last_login_ip' => get_client_ip(0,true),
                    'create_time' => date("Y-m-d H:i:s"),
                    'last_login_time' => date("Y-m-d H:i:s"),
                    'user_status' => $need_email_active?2:1,
                    "user_type"=>2,//会员
                );
                $rst = $users_model->add($data);
                if($rst){
                    //登入成功页面跳转
                    $data['id']=$rst;
                    $_SESSION['user']=$data;
    
                    //发送激活邮件
                    if($need_email_active){
                        $this->_send_to_active();
                        unset($_SESSION['user']);
                        $return["code"] = 20;
                        $return["message"]="注册成功,激活后才能使用！";
                        return $return;
                    }else {
                        $return ['code'] = 0;
                        $userinfo = $_SESSION['user'];
                        // 处理用户头像
                        if ($userinfo['avatar']) {
                            $userinfo['avatar'] = "http://" . $_SERVER["SERVER_NAME"] . "/data/upload/avatar/" . $userinfo['avatar'];
                        }
                        $return['data'] = $userinfo;
                        return $return;
                    }
                } else {
                    $return["code"] = 14;
                    $return["message"] = "注册失败！";
                    return $return;
                }
            } else {
                $return["code"] = 14;
                $return["message"] = "注册失败！";
                return $return;
            }
    
        }
    }	
}