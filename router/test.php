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
        $response_r = curlRequest('https://openapi.baidu.com/oauth/2.0/token', 'grant_type=refresh_token&refresh_token='.
            $_REQUEST['refresh_token'].'&client_id='.$ApiKey.'&client_secret='.$SecretKey, 'POST');
        echo $response_r;
    }
}

function curlRequest





?>
