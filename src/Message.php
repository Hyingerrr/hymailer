<?php
namespace hymailer\mailqueue;
use Yii;
/**
 * 对\yii\swiftmailer\Message进行扩展  redis邮件队列
 * 将邮件存储到redis队列中
 */
class Message extends \yii\swiftmailer\Message
{
	public function queue(){
		$redis = Yii::$app->redis;
		if(empty($redis)){
			throw new \yii\base\InvalidConfigException("redis not found in config.");
		}
		$mailer = Yii::$app->mailer;
		// if(empty($mailer) || !$redis->select($mailer->db)){
		// 	throw new \yii\base\InvalidConfigException('db not defined.');
		// }

		$message = [];
		$message['from'] = $this->from; //sender  or方法 getFrom()
		$message['to'] = $this->getTo();	//收件人
        
		$message['cc'] = $this->getCc();  // 抄送者
		$message['bcc'] = $this->getBcc();  // 隐藏接收者
		$message['reply_to'] = $this->getReplyTo();  // 回复地址
		$message['charset'] = $this->getCharset();
        $message['subject'] = $this->getSubject();  // 主题

        // 拿到邮件信息的子信息的对象  array[obj] html_body和text_body
        $items = $this->getSwiftMessage()->getChildren();  
        if(!is_array($items) || !sizeof($items)){
        	$items = [$this->getSwiftMessage()];
        }

        foreach ($items as $k => $vv) {
        	if(!$vv instanceof \Swift_Mime_Attachment){
        		switch ($vv->getContentType()) {  //获取邮件内容类型
        			case 'text/html':
        				$message['html_body'] = $vv->getBody();
                        break;
                    case 'text/plain':
        				$message['text_body'] = $vv->getBody();
                        break;
        		}
        		if (!$message['charset']) {
                    $message['charset'] = $vv->getCharset();
                }
        	}
        }
        return  $redis->rpush($mailer->key,json_encode($message,JSON_UNESCAPED_SLASHES)); 
	}
}