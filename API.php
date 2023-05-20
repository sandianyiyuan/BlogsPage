<?php
include_once "checkSignature.php";
include_once "AI.php";
define("TOKEN", "wxdc13e0862c0eafbf");//自己定义的token 就是个通信的私钥
$wechatObj = new wechatCallbackapiTest();
//$wechatObj->valid();      //在第一次接口鉴权的时候使用
$wechatObj->responseMsg();  //鉴定权通过后，使用该方法
class wechatCallbackapiTest
{
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if(checkSignature()){
            echo $echoStr;
            exit;
        }
    }
    public function responseMsg()
    {
        file_put_contents("./chatBot.log","signature " . $_GET['signature'] . "\n",FILE_APPEND);
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        file_put_contents("./chatBot.log","收到请求 \n" . $postStr . "\n",FILE_APPEND);
        if (!empty($postStr)){
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;         
            $toUsername = $postObj->ToUserName;
            $msgType = $postObj->MsgType;
            $content = trim($postObj->Content);
            $time = time();
            $textTpl = "<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[%s]]></MsgType><Content><![CDATA[%s]]></Content><FuncFlag>0<FuncFlag></xml>";
            if(!empty($content))
            {
                $this->msg = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                // 设置请求数据（应用密钥、接口请求参数）
                $appkey = 'aMRn56I4DFs8McnP';           //APPKEY，每个APP不同，在控制台基本信息中查看
                $params = array(
                'app_id'     => '2155378192',       //应用标识（AppId）
                'time_stamp' => $time,     //请求时间戳（秒级）
                'nonce_str'  => '20e3408a79',       //随机字符串
                'sign'       => '',                 //签名信息,https://ai.qq.com/doc/auth.shtml
                'session'    => '0000000001',                  //会话标识（应用内唯一）
                'question'   => $content,           //用户输入的内容
                );
                $params['sign'] = getReqSign($params, $appkey);
                // 执行API调用
                $url = 'https://api.ai.qq.com/fcgi-bin/nlp/nlp_textchat';
                $response = doHttpPost($url, $params);
                $responseJson = json_decode($response, true);       //返回值是json。取出需要的那部分内容
                $contentStr = $responseJson['data']['answer'];
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            }else{
                echo '咋不说话呢';
            }
        }else {
            echo '咋不说话呢';
            exit;
        }
    }
}
?>