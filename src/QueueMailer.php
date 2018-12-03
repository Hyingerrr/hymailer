<?php
namespace hymailer\mailqueue;
use Yii;
/**
 * 继承\yii\swiftmailer\Mailer类
 * 发送邮件扩展  将邮件从redis队列中取出 进行设置  然后发送
 */
class QueueMailer extends \yii\swiftmailer\Mailer
{
	public $messageClass = 'hymailer\mailqueue\Message'; //声明Message类是自定义的哪个类
	public $db = '1';  // 指定redis库  web.php配置的
	public $key = 'mails';  // 指定redis键名

	public function process(){
		$redis = Yii::$app->redis;
		if(empty($redis)){
			throw new \yii\base\InvalidConfigException("redis not found in config.");
		}
		$items = $redis->lrange($this->key,0,-1);  // 获取redis中所有元素

		if($redis->select($this->db) && $items){
			$messageObj = new Message;
			foreach ($items as $news) {
				$news = json_decode($news,true);

				if(empty($news) || !$this->setMessage($messageObj,$news)){
					throw new \yii\web\ServerErrorHttpException("found error");
				}
				if($messageObj->send()){
					//发送成功之后 从队列中删除
					//$redis->rpop($this->key,json_encode($news));
					$redis->lrem($this->key,-1,json_encode($news));
				}else{
					echo "\n -----------send error -------------\n";
				}
			}
		}
		return true;
	}

	//设置邮件属性
	public function setMessage($messageObj,$news){
		if(empty($messageObj)){
			return false;
		}
		if(!empty($news['from']) && !empty($news['to'])){
			$messageObj->setFrom($news['from'])->setTo($news['to']);
			if(!empty($news['cc'])){
				$messageObj->setCc($news['cc']);
			}
			if(!empty($news['bcc'])){
				$messageObj->setBcc($news['bcc']);
			}
			if(!empty($news['reply_to'])){
				$messageObj->setReplyTo($news['reply_to']);
			}
			if(!empty($news['charset'])){
				$messageObj->setCharset($news['charset']);
			}
			if(!empty($news['subject'])){
				$messageObj->setSubject($news['subject']);
			}
			if(!empty($news['html_body'])){
				$messageObj->setHtmlBody($news['html_body']);
			}
			if(!empty($news['text_body'])){
				$messageObj->setTextBody($news['text_body']);
			}
			return $messageObj;
		}
		return false;
	}
}