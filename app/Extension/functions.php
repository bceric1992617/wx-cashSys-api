<?php
use TencentCloud\Sms\V20190711\SmsClient;
use TencentCloud\Sms\V20190711\Models\SendSmsRequest;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use Illuminate\Support\Facades\Session;

function checkSqlIsSuccess($isSuccess,$msg,$failInfo,$successInfo) {
    if($isSuccess) {
        $msg ['status'] = true;
        $msg ['info'] = $successInfo;
        return $msg;
    } else {
        $msg ['status'] = false;
        $msg ['info'] = $failInfo;
        return $msg;
    }
}



function sendCode($phone) {
    $msg = [];
    $randCode = rand(1000,9999);
    $msg['code'] = $randCode;
    $config = config('sms');

    // return $msg;die;
    try {
        /* 必要步骤：
         * 实例化一个认证对象，入参需要传入腾讯云账户密钥对 secretId 和 secretKey
         * 本示例采用从环境变量读取的方式，需要预先在环境变量中设置这两个值
         * 您也可以直接在代码中写入密钥对，但需谨防泄露，不要将代码复制、上传或者分享给他人
         * CAM 密钥查询：https://console.cloud.tencent.com/cam/capi
         */
    
        $cred = new Credential($config['secretId'], $config['secretKey']);
        //$cred = new Credential(getenv("TENCENTCLOUD_SECRET_ID"), getenv("TENCENTCLOUD_SECRET_KEY"));
    
        // 实例化一个 http 选项，可选，无特殊需求时可以跳过
        $httpProfile = new HttpProfile();
        $httpProfile->setReqMethod("GET");  // POST 请求（默认为 POST 请求）
        $httpProfile->setReqTimeout(30);    // 请求超时时间，单位为秒（默认60秒）
        $httpProfile->setEndpoint("sms.tencentcloudapi.com");  // 指定接入地域域名（默认就近接入）
    
        // 实例化一个 client 选项，可选，无特殊需求时可以跳过
        $clientProfile = new ClientProfile();
        $clientProfile->setSignMethod("TC3-HMAC-SHA256");  // 指定签名算法（默认为 HmacSHA256）
        $clientProfile->setHttpProfile($httpProfile);
    
        // 实例化 SMS 的 client 对象，clientProfile 是可选的
        $client = new SmsClient($cred, "ap-shanghai", $clientProfile);
    
        // 实例化一个 sms 发送短信请求对象，每个接口都会对应一个 request 对象。
        $req = new SendSmsRequest();
    
        /* 填充请求参数，这里 request 对象的成员变量即对应接口的入参
         * 您可以通过官网接口文档或跳转到 request 对象的定义处查看请求参数的定义
         * 基本类型的设置:
           * 帮助链接：
           * 短信控制台：https://console.cloud.tencent.com/smsv2
           * sms helper：https://cloud.tencent.com/document/product/382/3773 */
    
        /* 短信应用 ID: 在 [短信控制台] 添加应用后生成的实际 SDKAppID，例如1400006666 */
        $req->SmsSdkAppid = $config['sdk_id'];
        /* 短信签名内容: 使用 UTF-8 编码，必须填写已审核通过的签名，可登录 [短信控制台] 查看签名信息 */
        $req->Sign = "菜菜收银";
        /* 短信码号扩展号: 默认未开通，如需开通请联系 [sms helper] */
        // $req->ExtendCode = "0";
        /* 下发手机号码，采用 e.164 标准，+[国家或地区码][手机号]
           * 例如+8613711112222， 其中前面有一个+号 ，86为国家码，13711112222为手机号，最多不要超过200个手机号*/
        $req->PhoneNumberSet = array("+86" . $phone);
        /* 国际/港澳台短信 senderid: 国内短信填空，默认未开通，如需开通请联系 [sms helper] */
        // $req->SenderId = "xxx";
        /* 用户的 session 内容: 可以携带用户侧 ID 等上下文信息，server 会原样返回 */
        $req->SessionContext = $randCode;
        /* 模板 ID: 必须填写已审核通过的模板 ID。可登录 [短信控制台] 查看模板 ID */
        $req->TemplateID = $config['template_id'];
        /* 模板参数: 若无模板参数，则设置为空*/
        $req->TemplateParamSet = array($randCode,'1');
    
        // 通过 client 对象调用 SendSms 方法发起请求。注意请求方法名与请求对象是对应的
        $resp = $client->SendSms($req);
        
        // 输出 JSON 格式的字符串回包
        print_r($resp->toJsonString());
    
        // 可以取出单个值，您可以通过官网接口文档或跳转到 response 对象的定义处查看返回字段的定义
        // print_r($resp->TotalCount);
    }
    catch(TencentCloudSDKException $e) {
        echo $e;
    }
    return $msg;
}

function strFilter($str){
    $str = str_replace('`', '', $str);
    $str = str_replace('·', '', $str);
    $str = str_replace('~', '', $str);
    $str = str_replace('!', '', $str);
    $str = str_replace('！', '', $str);
    $str = str_replace('(', '', $str);
    $str = str_replace(')', '', $str);
    $str = str_replace('（', '', $str);
    $str = str_replace('）', '', $str);
    $str = str_replace('——', '', $str);
    $str = str_replace('|', '', $str);
    $str = str_replace('\\', '', $str);
    $str = str_replace(';', '', $str);
    $str = str_replace('；', '', $str);
    $str = str_replace(':', '', $str);
    $str = str_replace('：', '', $str);
    $str = str_replace('\'', '', $str);
    $str = str_replace('"', '', $str);
    $str = str_replace('“', '', $str);
    $str = str_replace('”', '', $str);
    $str = str_replace(',', '', $str);
    $str = str_replace('，', '', $str);
    $str = str_replace('。', '', $str);
    $str = str_replace('/', '', $str);
    $str = str_replace('、', '', $str);
    $str = str_replace(' ', '', $str);
    return trim($str);
}

function checkImgType($imgType) {
    if(in_array($imgType, ['png','jpg','jpeg','gif'])) {
        return true;
    } else {
        return false;
    }
}





//把请求发送到微信服务器换取二维码
function httpRequest($url,$data='',$method='GET'){
    $curl = curl_init(); 
    curl_setopt($curl, CURLOPT_URL,$url); 
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); 
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); 
    curl_setopt($curl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']); 
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); 
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1); 
    if($method=='POST')
    {
        curl_setopt($curl, CURLOPT_POST, 1);
        if ($data !='')
        {
            curl_setopt($curl, CURLOPT_POSTFIELDS,$data); 
        }
    }
 
    curl_setopt($curl, CURLOPT_TIMEOUT, 30); 
    curl_setopt($curl, CURLOPT_HEADER, 0); 
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
    $result = curl_exec($curl); 
    curl_close($curl); 
    return $result;
  }


//获取点餐二维码配置
function getOrderCodeSetting() {
    header('content-type:text/html;charset=utf-8');
    //配置APPID、APPSECRET
    $APPID ="wx4880b929b2192d09";
    $APPSECRET = "27782ef5da912f61055191db7775c6ef";
    //获取access_token
    $access_token ="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$APPID&secret=$APPSECRET";
     
    //缓存access_token
     Session::put('access_token','');
     Session::put('expires_in',0);
     session()->save();
     $ACCESS_TOKEN ="";
     if(empty(Session::get('access_token')) || (!empty(Session::get('access_token')) && time() > Session::get('access_token')))
     {
         $json = httpRequest($access_token);
         $json = json_decode($json,true);
         // var_dump($json);
         Session::put('access_token',$json['access_token']);
         Session::put('expires_in',time()+7200);
         $ACCESS_TOKEN =$json["access_token"];
     }
     else{
        Session::put('expires_in',time()+7200);
         $ACCESS_TOKEN = $_SESSION["access_token"];
     }
     session()->save();
    //构建请求二维码参数
    //path是扫描二维码跳转的小程序路径，可以带参数?id=xxx
    //width是二维码宽度
    return  "https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=$ACCESS_TOKEN";
    
}

//生成点餐二维码
function createOrderCode($qcode,$imgPath,$tableNum,$userId) {
    $param = json_encode(array("path"=>"pages/orderCode/orderCode?tableNum=".$tableNum."&userId=".$userId,"width"=> 100));
     
    //POST参数
    $result = httpRequest($qcode,$param,"POST");
    //生成二维码
    file_put_contents($imgPath,$result);

}


//去除字符串的第一个和最后一个
function removeFirstAndLast($str) {
    return substr($str,1,strlen($str)-2);
}

