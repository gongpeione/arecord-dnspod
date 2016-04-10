<?php

/*
	Config

	Dnspod API文档：https://www.dnspod.cn/docs/index.html
	PHPMailer：https://github.com/PHPMailer/PHPMailer
 */


//dnspod token
$token      = '';
//域名id 获取方式：curl -X POST https://dnsapi.cn/Domain.List -d 'login_token=LOGIN_TOKEN&format=json'
$domain_id  = '12234';
//记录id 获取方式：curl -X POST https://dnsapi.cn/Record.List -d 'login_token=LOGIN_TOKEN&format=json&domain_id=2317346'
$record_id  = '12234';
//主机记录
$sub_domain = 'www';


//PHPMailer配置

require './PHPMailer/PHPMailerAutoload.php';
$mail = new PHPMailer;

//$mail->SMTPDebug = 3;                               
$mail->isSMTP();    
//邮箱smtp地址 如smtp.qq.com                                  
$mail->Host = 'smtp.qq.com'; 
$mail->SMTPAuth = true; 
//用户名             
$mail->Username = '';   
//密码         
$mail->Password = ''; 
//是否使用SSL                        
$mail->SMTPSecure = 'tls';  
//端口号                        
$mail->Port = 25;                                  
//发信人
$mail->setFrom('yoo@yoo.com', 'yoo');
//收信人
$mail->addAddress('yoo@yoo.com', 'yoo'); 



/*
	Start
 */

//获取当前IP
$ip_content = file_get_contents('http://ip.chinaz.com/');

preg_match_all('/<dd class="fz24">(.*?)<\/dd>/', $ip_content, $match);

$ip = $match[1][0];

//获取之前更改成功的IP
$current_ip = file_get_contents('ip');

//两个IP相比较，如果相同则无需更新
if(trim($current_ip) == trim($ip)) {
	exit();
}


//更新A记录
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL,"https://dnsapi.cn/Record.Modify");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,
            "login_token=$token&format=json&domain_id=$domain_id&record_id=$record_id&sub_domain=$sub_domain&value=$ip&record_type=A&record_line=默认");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$server_output = curl_exec ($ch);

curl_close ($ch);


//获取DNSPOD返回的信息
$result = json_decode($server_output);
$msg    = '';

if ($result->status->code == "1") {

	$msg = '[Date] ' . date('Y-m-d H:i:s', time()) . " [MSG] Update A Record Successful! [New IP] $ip\n";
	
	echo $msg;

	//更新IP
	file_put_contents('ip', $ip);

} else { 

	$msg = '[Date] ' . date('Y-m-d H:i:s', time()) . " [MSG] Update A Record Failed! $result->status->message\n";

	echo $msg;

}


    
/*
	Send Email
 */
$mail->Subject = 'Update A Record Event';
$mail->Body    = $msg;
$mail->AltBody = $msg;
@$mail->send();