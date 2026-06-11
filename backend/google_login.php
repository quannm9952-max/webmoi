<?php require_once __DIR__.'/includes/bootstrap.php';
if(GOOGLE_CLIENT_ID===''||GOOGLE_CLIENT_SECRET==='') die('Chưa cấu hình GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET trong config/oauth.php');
$state=bin2hex(random_bytes(16));$_SESSION['google_oauth_state']=$state;
$params=['client_id'=>GOOGLE_CLIENT_ID,'redirect_uri'=>GOOGLE_REDIRECT_URI,'response_type'=>'code','scope'=>'openid email profile','state'=>$state,'prompt'=>'select_account'];
header('Location: https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query($params));exit;
