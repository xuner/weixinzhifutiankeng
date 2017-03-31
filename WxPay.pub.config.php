<?php
class WxPayConf_pub {
	//=======【基本信息设置】=====================================
	//微信公众号身份的唯一标识。审核通过后，在微信发送的邮件中查看
	const APPID = "wx123456845585550";
	//受理商ID，身份标识
	const MCHID = '1155125852540';
	//商户支付密钥Key。审核通过后，在微信发送的邮件中查看
	const KEY = 'aidnfasfiaslfai5485sfnlsda5665';
	//JSAPI接口中获取openid，审核后在公众平台开启开发模式后可查看
	const APPSECRET = "3515asdfe1336548ds55485dasf5";  

	//=======【JSAPI路径设置】===================================
	//获取access_token过程中的跳转uri，通过跳转将code传入jsapi支付页面
	//const JS_API_CALL_URL = 'http://t.95885ku.cc/index.php/pay/bcpay/wxpay';

	//=======【curl超时设置】===================================
	//本例程通过curl使用HTTP POST方法，此处可修改其超时时间，默认为30秒
	const CURL_TIMEOUT = 30;
}
	
?>
