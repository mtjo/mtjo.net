<?php
namespace App\Controller;
use Common\Controller\HomebaseController;

class IndexController extends HomebaseController{

	function index(){
		
	}
	public function nav_list(){
		header('Content-type: application/json');
		$return = array ('code'=>0,'message'=>'');
		$keyword = $_GET['keyword'];
		$return['data']=sp_get_terms("field:term_id,name");
		exit(json_encode($return));
	}
	
	public function home_slides(){
	    $home_slides=sp_getslide("portal_index");
	    foreach ($home_slides as &$v){
	        $v['slide_pic']="http://".$_SERVER['SERVER_NAME'].$v['slide_pic'];
	    }
    

	    
	    $home_slides=empty($home_slides)?$default_home_slides:$home_slides;
	    
	    $return = array ('code'=>0,'message'=>'');
	    $keyword = $_GET['keyword'];
            foreach ($home_slides as &$val) {
                $val ["slide_url"] = $val ["slide_url"]. "?app=1";
             }

	    $return['data']=$home_slides;
	    
	    exit(json_encode($return));
	    
	}
	public function article_list(){
		$return = array ('code'=>0,'message'=>'');
		$term=sp_get_term($_GET['cid']);
		$catid = $_REQUEST['cid'];
		$page=$_REQUEST['page']?$_REQUEST['page']:1;
		$pageSize=$_REQUEST['pageSize']?$_REQUEST['pageSize']:10;
		
		$_str='';
		if ($catid>0)
		{
			$_str .= "cid:$catid;";
		}
		$_str .= ' order:post_date DESC;';
		$_str .= " field:tid,term_id,post_title,smeta,post_keywords,post_date,post_excerpt;";
		$posts= sp_sql_posts_paged($_str,$page,$pageSize);
		$term=sp_get_terms("field:term_id,name");
		$terms =array();
		foreach ($term as $v){
		    $terms[$v['term_id']]=$v['name'];
		}
		//print_r($posts);
		$data= array ();
		foreach ($posts as $v){
		    $thumb = json_decode(stripslashes($v['smeta']),1);
		    $data[] = array(
		        'id'=>$v['tid'],
		        'title'=>$v['post_title'],		        
		        'keywords'=>$v['post_keywords'],
		        'thumb'=>$thumb['thumb']!=""?"http://".$_SERVER["SERVER_NAME"]."/data/upload/".$thumb['thumb']:null,
		        'cid'=>$v['term_id'],
		        'updatetime'=>strtotime($v['post_date']),
		        'url'=>"http://".$_SERVER["SERVER_NAME"]."/index.php/article/index/id/{$v['tid']}.html?app=1",
		        'description'=>$v['post_excerpt'],
		        'catname'=>$terms[$v['term_id']],
		    );
		}
		
		
		$return['data'] = $data;
		exit(json_encode($return));

	}
	public function article_detail(){
		$id=intval($_GET['id']);
		$article=sp_sql_post($id,'');
		$this->ajaxReturn($article);
	}
	public  function search(){
		$return = array ('code'=>0,'message'=>'');
		$keyword = $_REQUEST['keyword'];
		
		$page=$_REQUEST['page']?$_REQUEST['page']:1;
		$pageSize=$_REQUEST['pageSize']?$_REQUEST['pageSize']:10;
		
		$term=sp_get_terms("field:term_id,name");
		$terms =array();
		foreach ($term as $v){
		    $terms[$v['term_id']]=$v['name'];
		}
		//print_r($posts);
		$data= array ();
		$_str = " field:tid,term_id,post_title,smeta,post_keywords,post_date,post_excerpt;";
		$posts=sp_sql_posts_paged_bykeyword($keyword,$_str,$page,$pageSize);
		foreach ($posts as $v){
		    $thumb = json_decode(stripslashes($v['smeta']),1);
		    $data[] = array(
		        'id'=>$v['tid'],
		        'title'=>$v['post_title'],
		        'keywords'=>$v['post_keywords'],
		        'thumb'=>$thumb['thumb']!=""?"http://".$_SERVER["SERVER_NAME"]."/data/upload/".$thumb['thumb']:null,
		        'cid'=>$v['term_id'],
		        'updatetime'=>strtotime($v['post_date']),
		        'url'=>"http://".$_SERVER["SERVER_NAME"]."/index.php/article/index/id/{$v['tid']}.html?app=1",
		        'description'=>$v['post_excerpt'],
		        'catname'=>$terms[$v['term_id']],
		    );
		}
		
		$return['data']=$data;
		
		exit(json_encode($return));
	}



   public function getcode() {
    	
    	
    	$config = array(
	        'codeSet'   =>  "2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY",             // 验证码字符集合
	        'expire'    =>  1800,            // 验证码过期时间（s）
	        'useImgBg'  =>  false,           // 使用背景图片 
	        'fontSize'  =>  25,              // 验证码字体大小(px)
	        'useCurve'  =>  true,           // 是否画混淆曲线
	        'useNoise'  => true,            // 是否添加杂点	
	        'imageH'    =>  50,               // 验证码图片高度
	        'imageW'    =>  200,               // 验证码图片宽度
	        'length'    =>  4,               // 验证码位数
	        'bg'        =>  array(243, 251, 254),  // 背景颜色
	        'reset'     =>  true,           // 验证成功后是否重置
    	);
    	$Verify = new \Think\Verify($config);
    	$code = array ();
    	$code = $Verify->get_verify_code();
    	
    	header('Content-type: application/json');
    	$return = array ('code'=>0,'message'=>'');
    	$keyword = $_GET['keyword'];
    	$return['data']=$code;
    	exit(json_encode($return));

    }

	
}
