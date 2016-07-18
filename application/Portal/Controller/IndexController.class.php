<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace Portal\Controller;
use Common\Controller\HomebaseController; 
/**
 * 首页
 */
class IndexController extends HomebaseController {
	
    //首页
	public function index() {
		if ($_GET['DEBUG']==1){
			echo "<pre>";
			print_r($_SESSION);
			exit;
		}
    	$this->display(":index");
    }

    public function baidusync()
    {
        if (isset($_REQUEST['method']) && $_REQUEST['method'] == 'refresh_access_token') {
            if (! isset($_REQUEST['refresh_token']) || $_REQUEST['refresh_token'] == '') {
                echo 'Error:Incorrect parameters.';
                //saveInvalidLog('Invalid request: refresh_access_token. IP: ' . $ip);
            } else {
                $response_r = curlRequest('https://openapi.baidu.com/oauth/2.0/token', 'grant_type=refresh_token&refresh_token=' . $_REQUEST['refresh_token'] . '&client_id=' . $ApiKey . '&client_secret=' . $SecretKey, 'POST');
                echo $response_r;
            }
        }
    }
function curlRequest($url,$cookie='', $returnCookie=0,$post=''){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_REFERER, "http://XXX");
        if($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        if($cookie) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);
        if($returnCookie){
            list($header, $body) = explode("\r\n\r\n", $data, 2);
            preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
            $info['cookie']  = substr($matches[1][0], 1);
            $info['content'] = $body;
            return $info;
        }else{
            return $data;
        }
}
}


