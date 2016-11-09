# weixinzhifutiankeng

关于变色龙APP打包时微信支付的注意事项
调用微信payType方法, payType（data,'WEIXIN','payResult'）
其中data 为json字符串，不是json对象（被这玩意折腾好几天）
'WEIXIN'表示调用的是微信支付
payResult表示返回方法
例子：
function payResult(r) {
　　　　if(r == 1){
　　　　setTimeout(function(){
  　　　　alert('支付成功！');
          location.reload(); 
　　　　}, 5000);
　　　　}else{
　　　　　　setTimeout(function(){
　　　　　　alert('支付失败请刷新后再试！');
　　　　}, 5000);
　　}
}

服务器端获取prepay_id的方法
//20161031	
	public function weixinapp()
	{		
	         $token=$this->input->post('token', TRUE);
			 if(!get_token('token',1,$token))
			 {
	 $data['message']="非法提交";			 
	 echo json_encode($data);exit;			
			}	 
		
			 $rmb=intval($this->input->post('rmb'));  //充值金额
			 $times=time();
			$add['dingdan'] = date('Ymd').$times.random_string('numeric',5);
			
			$add['type'] = "wxapp";
			$add['rmb'] = $rmb;
			$add['uid'] = $_SESSION['kucms__id'];
			$add['ip'] = getip();
			$add['addtime'] = time();
			$ids=$this->kudb->get_insert('pay',$add);
			get_token('token',2);
			$rmb=$rmb.'00';
			$rmb=(int)$rmb;
			
	        include_once('wx/lib/WXPayPubHelper.php');
            $UN=new UnifiedOrder_pub();
            $UN->setParameter("out_trade_no",$add['dingdan']);
            $UN->setParameter('body','充值');
            $UN->setParameter('total_fee',$rmb);			
			$UN->setParameter('trade_type','APP');
	        $UN->setParameter('notify_url',site_url("pay/apppay/notify"));				
			$prepayid=$UN->getall();
			if($prepayid['return_code']=='SUCCESS' && $prepayid['result_code'] == 'SUCCESS'){
			$data['appid'] = $prepayid['appid'];
			$data["partnerid"]=$prepayid['mch_id'];
			$data["package"]="Sign=WXPay";
			$data["noncestr"]=$UN->parameters["nonce_str"];
			$data["timestamp"]=$times;
			$data["prepayid"]=$prepayid['prepay_id'];
			$sign=$UN->getSign($data);
			$data['appid'] = $prepayid['appid'];
			$data["partnerid"]=$prepayid['mch_id'];
			$data["package"]="Sign=WXPay";
			$data["noncestr"]=$data['noncestr'];
			$data["timestamp"]=$data["timestamp"];
			$data["prepayid"]=$prepayid['prepay_id'];
          	$data["sign"]=$sign;			
		
			}else{
			$data['msg']=$prepayid['return_msg'];	
				                                                            
			}	
            echo json_encode($data);	 	 
	}
  通过 include_once('wx/lib/WXPayPubHelper.php');
  来实例化 UnifiedOrder_pub 类
      $UN=new UnifiedOrder_pub();
  接着就是添加各种需要的参数
       $UN->setParameter("out_trade_no",$add['dingdan']);
            $UN->setParameter('body','充值');
            $UN->setParameter('total_fee',$rmb);			
			$UN->setParameter('trade_type','APP');
	        $UN->setParameter('notify_url',site_url("pay/apppay/notify"));				
 注意:因为appid appscript 之类写在 WxPay.pub.config.php
 而WxPayPubHelper.php 里面已经include_once(' WxPay.pub.config.php');
 所以不用添加appid 这些必须参数
 接着通WxPayPubHelper.php 里面的类的方法 getPrepayId来获取prepayid.(getAll方法是自己写在WxPayPubHelper.php里面，不是里面本身有的)
 
 $prepayid=$UN->getall();（用来获取从微信服务端传来的全部参数,自己写的方法）。
 标准方法如下：
 $prepayid=$UN->getPrepayId();（用来获取预支付订单，prepayid）;
 下面通过
 
 $prepayid['return_code']和$prepayid['result_code']是否等于SUCCESS （$prepayid是由$prepayid=$UN-getall(),获取到的数组）
 if($prepayid['return_code']=='SUCCESS' && $prepayid['result_code'] == 'SUCCESS'){
			$data['appid'] = $prepayid['appid'];
			$data["partnerid"]=$prepayid['mch_id'];
			$data["package"]="Sign=WXPay";
			$data["noncestr"]=$UN->parameters["nonce_str"];
			$data["timestamp"]=$times;
			$data["prepayid"]=$prepayid['prepay_id'];
			$sign=$UN->getSign($data);
			$data['appid'] = $prepayid['appid'];
			$data["partnerid"]=$prepayid['mch_id'];
			$data["package"]="Sign=WXPay";
			$data["noncestr"]=$data['noncestr'];
			$data["timestamp"]=$data["timestamp"];
			$data["prepayid"]=$prepayid['prepay_id'];
          	$data["sign"]=$sign;			
 
 把返回的appid,partnerid(其实就是mch_id)，package="Sign=WXPay"(必须这么写)，noncestr(这是一开始发送到微信获取预支付订单时的随机数:$UN->parameter["nonce_str"],直接套用过来。),timestamp（这个最坑，如果你写成$data['timestamp']=time(),支付时会提示你的订单过时之类的，这个timestamp由一开始 $times=time(); $add['dingdan'] = date('Ymd').$times.random_string('numeric',5);的里面$times进去），prepayid为
 微信服务器返回的prepay_id,然后$sign就是要把前面的appid,partnerid,package,noncestr,timestamp,prepayid全部反正一起进行前面，注意！！这些前面的参数名一定要小写，不可以像Appid,Partnerid之类的。
 最后把appid,partnerid,package,noncestr,timestamp,prepayid,sign组成数组再通过echo json_encode($data);	
 返回给前台
 前台接收到data后，一定要先把data转化为json字符串
 var jsonText = JSON.stringify(data);
 不然一定会调不起，（变色龙APP坑爹）
 
 前端调用方法
 function wxpayap(){
      var url="[user:payweixinapp]";
	  var rmb=$("input[name='rmb']").val();
      var token= $("input[name='token']").val();    
//按钮出发的交易事件
　　　　　　$.ajax({//异步发送请求至后台拼接order串
　　　　　　　　type: 'post',
　　　　　　　　url: url, //(method path)
　　　　　　　　data:{
                  rmb:rmb,
				  token:token
				  },
　　　　　　　　dataType:'json',
　　　　　　　　async: true,
　　　　　　　　timeout: 200000,
　　　　　　　　success: function(data){
　　　　　　　　　　　//成功回访　           
			var jsonText = JSON.stringify(data);
         //   alert(jsonText);	   
			// document.getElementById('cs').innerHTML=jsonText;
  　　　　　　　payType(jsonText,'WEIXIN','payResult');
　　　　　　　　}
　　　　　　});
　　　
      }
 当点击wxapp方法时，会把自己定义的参数发送到后台，再从后台返回data交个payType来调用。
 
 
 微信notify_url 片段
 
 public function notify()
	{	
		 $postdata=file_get_contents("php://input");		
		$postobj=simplexml_load_string($postdata,"SimpleXMLElement",LIBXML_NOCDATA);	
		file_put_contents('apppay.txt',$postdata, FILE_APPEND);
		file_put_contents('apppay1.txt',$postobj->out_trade_no, FILE_APPEND);
		$out_trade_no = $postobj->out_trade_no;
		$rmb=$postobj->total_fee/100;	
		echo "success";
		exit;
 }
  $postdata是微信服务器端返回的支付成功的订单参数，为XML格式，所以才需要
  	$postobj=simplexml_load_string($postdata,"SimpleXMLElement",LIBXML_NOCDATA);	
    来转化为对象$postobj，$postobj->out_trade_no表示订单号,$postobj->total_fee/100 表示总金额，除以100是因为他以分为单位，一元=一百分
    最后一定要返回个success给微信服务器。
    不然它会回调8次来确认你是否收到。
    整个过程就是这样，因为是用变色龙APP来打包成APP，所以没涉及到java语言。
