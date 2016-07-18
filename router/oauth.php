<?PHP





if (isset($_REQUEST['method']) && $_REQUEST['method'] == 'refresh_access_token')
{
    if (!isset($_REQUEST['refresh_token']) || $_REQUEST['refresh_token'] == '')
    {
        echo 'Error:Incorrect parameters.';
        saveInvalidLog('Invalid request: refresh_access_token. IP: '.$ip);
    }
    else
    {
       $postFields= array(
        'grant_type'=>refresh_token;
        'refresh_token'=>$_REQUEST['refresh_token'].'&client_id='.$ApiKey;
        'client_secret'=>$SecretKey;
        )
        $response_r = post('https://openapi.baidu.com/oauth/2.0/token',$postFields);
        echo $response_r;
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
            /*foreach ($postFields as $k => $v) {
                // $postBodyString .= "$k=" . urlencode($v) . "&";
                $postBodyString .= "$k=" . $v . "&";
            }
            */
            $postBodyString =http_build_query($postFields);

            //die(print_r($postBodyString));

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
