<?php

require_once '../libs/BaiduPCS.class.php';
//请根据实际情况更新$access_token
$access_token = '26.8df057d37ed41c0a6639843ad5b4c0e6.2592000.1470807548.2738093065-2293434';

$pcs = new BaiduPCS($access_token);
echo $pcs->getQuota();
?>
