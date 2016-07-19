<?PHP
$appId = "2882303761517489490";
$ApiKey = 'Mf5KCPQDptxr81amSe2eliwz';
$SecretKey = 'h4RnbaVulUixyLjP5LxhNwai95QGqPrp';
$redirect_uri = 'http://mtjo.net/router/index.php';
if (isset($_REQUEST['refresh_token'])) {
    $url = 'https://openapi.baidu.com/oauth/2.0/token';
    $postFields = array(
        'grant_type' => 'refresh_token',
        'refresh_token' => $_REQUEST['refresh_token'],
        'client_id' => $ApiKey,
        'client_secret' => $SecretKey
    );
    
    $response_r = curl('https://openapi.baidu.com/oauth/2.0/token', $postFields);
    echo $response_r;
    exit();
} elseif (isset($_REQUEST['bindaccount']) && $_REQUEST['bindaccount'] = 'mtjo') {
    $location = 'https://openapi.baidu.com/oauth/2.0/authorize?response_type=code&' . 'client_id=' . $ApiKey . '&redirect_uri=' . $redirect_uri . '&scope=basic,netdisk' . '&display=dialog';
    header('Location:' . $location, true, 302);
} elseif (isset($_REQUEST['code']) && $_REQUEST['code'] != '') {
    $url = 'https://openapi.baidu.com/oauth/2.0/token';    
    $postFields = array(
        'grant_type' => 'authorization_code',
        'code' => $_REQUEST['code'],
        'refresh_token' => $_REQUEST['refresh_token'],
        'client_id' => $ApiKey,
        'client_secret' => $SecretKey,
        'redirect_uri' => $redirect_uri,
        'display' => 'dialog'
    );
    $userconfig = curl($url, $postFields);
}elseif ($_POST){
    $filesdata =array();
    foreach ($_POST as $k=>$v){
        foreach ($v as $key=>$val){
            $filesdata[$key][$k]=$val;
        }
    }
}

// HTTP POST请求函数
function curl($url, $postFields = null)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FAILONERROR, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-type: application/x-www-form-urlencoded;charset=UTF-8'
    ));
    
    if (is_array($postFields) && 0 < count($postFields)) {
        /*
         * foreach ($postFields as $k => $v) {
         * // $postBodyString .= "$k=" . urlencode($v) . "&";
         * $postBodyString .= "$k=" . $v . "&";
         * }
         */
        $postBodyString = http_build_query($postFields);
        
        // die(print_r($postBodyString));
        
        unset($k, $v);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postBodyString);
    }
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        throw new Exception(curl_error($ch), 0);
    } else {
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (200 !== $httpStatusCode) {
            throw new Exception($response, $httpStatusCode);
        }
    }
    curl_close($ch);
    return $response;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport"
	content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, minimal-ui">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<link rel="stylesheet" href="css/framework7.ios.min.css">
<link rel="stylesheet" href="css/framework7.ios.colors.min.css">
<link rel="stylesheet" href="css/framework7.material.min.css">
<link rel="stylesheet" href="css/framework7.material.colors.min.css">
<link rel="stylesheet" href="css/upscroller.css">
<link rel="stylesheet" href="css/my-app.css">
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<script
	src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.js"></script>
<script src="http://app.miwifi.com/js/router_request.js"></script>
<title>百度云同步设置</title>
<meta name="viewport"
	content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
<link href="css/reset.css" rel="stylesheet" type="text/css">
<link href="css/mui-switch.css" rel="stylesheet" type="text/css">

</head>
<body>
	<div class="views">

		<div class="view view-main">
			<div class="navbar">
				<div class="navbar-inner">
					<table width="100%">
						<tr>
							<td><font colour="#FFFFFF">功能开启</font></td>

							<td align="right"><input id="status" width="150"
								class="mui-switch mui-switch-anim" type="checkbox"></td>
						</tr>
					</table>

				</div>
			</div>
			<a href="javascript:" style="display: none;"
				class="floating-button addbtn color-pink"><i class="icon icon-plus"></i></a>
			<div id="configlist" style="display: none;"
				class="pages navbar-through toolbar-through">
				<div data-page="index" class="page">

					<!--内容开始-->

					<div class="page-content">

						<div class="list-block">
						<form action="" method="post">
							<ul>

							</ul>
							</form>
							<div class="row">
								<div class="col-100 ">
									<button id = "save" class="button button-fill color-red"
										style="width: 100%">保存</button>
								</div>
							</div>

						</div>

					</div>
					<!--内容结束-->
				</div>
			</div>

			<!--绑定-->
			<div id="intisync" 
				style="display:none; 100%; height: 100%; background-color: #ffffff;"
				class="pages navbar-through toolbar-through">
				<div class="page-content">

					<div class="list-block" style="padding: 20px;">
						<center>
							<input id="bind_pcs" type="button" value="绑定百度云" />
						</center>
						<center>
							<input id="refresh_token" type="button" value="test" />
						</center>

						<hr />
						<center>
						  <label>最容量：<font id="quota" color="#FF0000"></font></label>
						  </center>
						  <center>
						　　<laber>已使用:<font id="used" color="#FF0000"></font></laber>
						</center>
						<hr />

						<center>
							<strong>帮助</strong>
						</center>
						[上传]:<br /> 只检查本地文件并上传修改过的文件，忽略远端的所有修改或删除，远端删除的也不再上传<br /> [上传+]:<br />
						远端是本地的完全镜像，忽略远端的修改，远端删除的文件在下一次同步时将上传，远端新增的文件如果本地不存在，将不做任何变化<br />
						[下载]:<br /> 只检查远端文件是否修改，如有修改下载到本地，忽略本地的修改；如本地文件被删除，将不再下载<br />
						[下载+]:<br /> 检查远端和本地文件，如远端有修改，下载到本地，忽略本地的修改；如本地有文件被删除，将重新下载<br />
						[同步]:<br />
						同时检查远端和本地文件，如只有远端被修改，则下载到本地；如只有本地修改，则上传到远端；如本地和远端都被修改，则以冲突设置方式为准。<br />
					</div>
				</div>
			</div>
			<!--绑定-->

		</div>
	</div>

	<script type="text/javascript" src="js/jquery.min.js"></script>
	<script type="text/javascript" src="js/framework7.min.js"></script>
	<!-- Path to your app js-->
	<script type="text/javascript" src="js/upscroller.js"></script>
	<script type="text/javascript" src="js/my-app.js"></script>
	<script>
    $(".addbtn").click(function(){

        $("ul").append(
                '<li class="swipeout" >'+
                '<div class="item-content">'+
                '<div class="item-inner">'+
                '<div class="item-title label">' +
                '<textarea id = "local_files" name="local_files[]" id="" placeholder="请办输入路由器文件路径" >'+
                '</textarea>'+
                '</div>'+
                '<div class="item-input">'+
                '<select name ="sync_type[]"><option value=0>上传</option><option value=1>上传+</option><option value=2>下载</option><option value=3>下载+</option><option value=4>同步</option></select> '+
                '</div>'+
                '<div class="item-title label">' +
                '<textarea name="remote_files[]" id="" placeholder="请办输入百度云文件路径" >'+
                '</textarea>'+
                '</div>'+
                '<div class="item-input">'+
                '<input name="status[]" type="hidden" value="0">'+
                '<input style = "float:right;" width="150" onChange="if($(this).prev().val()==1){$(this).prev().val(0)} else {$(this).prev().val(1)} ;" class="mui-switch mui-switch-anim" type="checkbox">'+
                '</div>'+
                '</div>'+
                '<div class="swipeout-actions-right">'+
                '<a href="#" class="swipeout-delete">Delete</a>'+
                '</div>'+
                '</div>'+
                '</li>'

        );

        $('.item-input').each(function(i){
            console.log($(this).find('img:first').attr('id','zoom'+i));
            console.log($(this).find('img:first').next().attr('id','add'+i));
        })

    });

</script>


</body>

<script type="text/javascript">

$(document).ready(function() {
  $("#authorizeButton").click(function(){
    if (!routerRequest.hasAccessToken()) {
      routerRequest.authorize(window.location.href, appId);
    }
  });
  　var userconfig;
   var status=0;
   var appId = '<?php echo $appId?>'

	 routerRequest.request({  
      path: "/api-third-party/service/datacenter/config_info",
      type: "GET",
      data: {
        appId: appId,
		key:"status"
      },
      success: function(data) {
        var response = jQuery.parseJSON(data);
        if (response.code != 0) {
          console.log(data);
          alert("错误：" + response.msg);
          return;
        }
        status = response.value;
        show_hide(status);
		  
      },
      error: function(data) {
        console.log("error:", data);
        alert("网络失败");
      }
    });

   
	 <?php
//token设置
if (isset($userconfig))
    echo 'routerRequest.request({  
		      path: "/api-third-party/service/datacenter/set_config",
		      type: "GET",
		      data: {
		        appId: appId,
				key:"pcs_token",
				value:"' . urlencode($userconfig) . '"
		      },
		      success: function(data) {
		        var response = jQuery.parseJSON(data);
		        if (response.code != 0) {
		          console.log(data);
		          alert("错误：" + response.msg);
		          return;
		        }
       　　　　　　　　 show_hide(status);
		        //alert(data);
		      },
		      error: function(data) {
		        console.log("error:", data);
		        alert("网络失败");
		      }
		    });'
    ?>
//详细列表设置
<?php if (isset($filesdata))
    echo 'routerRequest.request({  
		      path: "/api-third-party/service/datacenter/set_config",
		      type: "GET",
		      data: {
		        appId: appId,
				key:"filesdata",
				value:"' . urlencode(json_encode($filesdata)) . '"
		      },
		      success: function(data) {
		        var response = jQuery.parseJSON(data);
		        if (response.code != 0) {
		          console.log(data);
		          alert("错误：" + response.msg);
		          return;
		        }
		        //alert(data);
		      },
		      error: function(data) {
		        console.log("error:", data);
		        alert("网络失败");
		      }
		    });'
		    ?>


	$("#status").change(function(){
		if(status==1){
			status = 0
		}else{
			status = 1
		}
	routerRequest.request({  
      path: "/api-third-party/service/datacenter/set_config",
      type: "GET",
      data: {
        appId: appId,
		key:"status",
		value:status
      },
      success: function(res) {
        var response = jQuery.parseJSON(res);
        if (response.code != 0) {
          console.log(data);
          alert("错误：" + response.msg);
          return;
        }
        show_hide(status);
      },
      error: function(data) {
        console.log("error:", data);
        alert("网络失败");
      }
    });
});	

function show_hide(show){
	if(show==1){
	    $("#status").attr("checked","checked");
	    $("#syncset").show();
	    $("#configlist").show();
	    $(".addbtn").show();
	    $("#intisync").hide();
	} else {
		$("#status").removeAttr("checked");
	    $("#syncset").hide();	
	    $("#configlist").hide();
	    $(".addbtn").hide();
	    $("#intisync").show();
	}
	
};	

$("#bind_pcs").click(function(){
	location = '/router/index.php?bindaccount=mtjo'
})

$("#refresh_token").click(function(){
	routerRequest.request({  
	      path: "/api-third-party/service/datacenter/config_info",
	      type: "GET",
	      data: {
	        appId: appId,
			key:"pcs_token"
	      },
	      success: function(data) {
	        var response = jQuery.parseJSON(data);
	        if (response.code != 0) {
	          console.log(data);
	          alert("错误：" + response.msg);
	          return;
	        }
	        alert(decodeURIComponent(response.value))
	       // status = response.value;
	        //show_hide(status);
			  
	      },
	      error: function(data) {
	        console.log("error:", data);
	        alert("网络失败");
	      }
	    });
});
$("#save").click(function(){
	$("form").submit();
	return;
});


//详细设置
			routerRequest.request({
			      path: "/api-third-party/service/datacenter/config_info",
			      type: "GET",
			      data: {
			        appId: appId,
					key:"pcs_token"
			      },
			      success: function(data) {
			        var response = jQuery.parseJSON(data);
			        if (response.code != 0) {
			          console.log(data);
			          alert("错误：" + response.msg);
			          return;
			        }

			        userconfig=jQuery.parseJSON(decodeURIComponent(response.value)); 
			        alert(userconfig);
			        quota_url="https://pcs.baidu.com/rest/2.0/pcs/quota";
					 $.get(quota_url, {method:"info",access_token:userconfig.access_token},function(data){
						 alert (data);
							 //quota = data.quota/(1024*1024*1024);
							 //used=data.used/(1024*1024*1024);
							 //$("#quota").text(quota );
							 //$("#used").text(used);
							 },"JSON");
  
			      },
			      error: function(data) {
			        console.log("error:", data);
			        alert("网络失败");
			      }
			    });
			routerRequest.request({  
			      path: "/api-third-party/service/datacenter/config_info",
			      type: "GET",
			      data: {
			        appId: appId,
					key:"filesdata"
			      },
			      success: function(data) {
			        var response = jQuery.parseJSON(data);
			        if (response.code != 0) {
			          console.log(data);
			          alert("错误：" + response.msg);
			          return;
			        }
			        filesdata=decodeURIComponent(response.value);
			        //alert(filesdata);

			        jsonobj=jQuery.parseJSON(filesdata);
			        $.each(jsonobj,function(index,item){
				        if(item.status =='1'){
					        ischeck = 'checked=checked'
				        } else {
				        	ischeck=''
					        }

				        switch(item.sync_type)
				        {
				        case "0":
				         type_option='<option value=0 selected="selected">上传</option><option value=1>上传+</option><option value=2>下载</option><option value=3>下载+</option><option value=4>同步</option>';
				          break;
				        case "1":
					         type_option='<option value=0>上传</option><option value=1 selected="selected">上传+</option><option value=2>下载</option><option value=3>下载+</option><option value=4>同步</option>';
					          break;
				        case "2":
					         type_option='<option value=0>上传</option><option value=1>上传+</option><option value=2 selected="selected">下载</option><option value=3>下载+</option><option value=4>同步</option>';
					          break;
				        case "3":
					         type_option='<option value=0 selected="selected">上传</option><option value=1>上传+</option><option value=2>下载</option><option value=3 selected="selected">下载+</option><option value=4>同步</option>';
					          break;
				        case "4":
					         type_option='<option value=0 selected="selected">上传</option><option value=1>上传+</option><option value=2>下载</option><option value=3 >下载+</option><option value=4 selected="selected">同步</option>';
					          break; 
				        }
			        
			            $("ul").append(
			                    '<li class="swipeout" >'+
			                    '<div class="item-content">'+
			                    '<div class="item-inner">'+
			                    '<div class="item-title label">' +
			                    '<textarea id = "local_files" name="local_files[]" id="" placeholder="请办输入路由器文件路径" >'+
			                    item.local_files+
			                    '</textarea>'+
			                    '</div>'+
			                    '<div class="item-input">'+
			                    '<select name ="sync_type[]">'+type_option+'</select> '+
			                    '</div>'+
			                    '<div class="item-title label">' +
			                    '<textarea name="remote_files[]" id="" placeholder="请办输入百度云文件路径" >'+
			                    item.remote_files+
			                    '</textarea>'+
			                    '</div>'+
			                    '<div class="item-input">'+
			                    '<input name="status[]" type="hidden" value="'+item.status+'">'+
			                    '<input style = "float:right;" '+ischeck+' width="150" onChange="if($(this).prev().val()==1){$(this).prev().val(0)} else {$(this).prev().val(1)} ;" class="mui-switch mui-switch-anim" type="checkbox">'+
			                    '</div>'+
			                    '</div>'+
			                    '<div class="swipeout-actions-right">'+
			                    '<a href="#" class="swipeout-delete">Delete</a>'+
			                    '</div>'+
			                    '</div>'+
			                    '</li>'

			            );
				    });
			        //alert(decodeURIComponent(response.value))
			       // status = response.value;
			        //show_hide(status);
					  
			      },
			      error: function(data) {
			        console.log("error:", data);
			        alert("网络失败");
			      }
			    });
			show_hide(status); 
});

</script>
</html>

