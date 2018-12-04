# hymailer
Asynchronous mail delivery queue by HY
# Installation
The preferred way to install this extension is through composer.

Either run

php composer.phar require --prefer-dist yiisoft/yii2-redis:"~2.0.0"

or add

"yiisoft/yii2-redis": "~2.0.0" 

to the require section of your composer.json.

# Configuration
To use this extension, you have to configure the Connection class in your application configuration:

return [
    //....
    'components' => [
        'mailer' => [
            // 'class' => 'yii\swiftmailer\Mailer',
            // 指向自定义的扩展类名(继承yii\swiftmailer\Mailer) 加载时无法识别自定义的目录 需要在前面alias声明
            'class' => 'hymailer\mailqueue\QueueMailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'db'  => '1', //redis默认有16个库，这里选择入第2个库
            'key' => 'mails',  //redis键名
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.163.com',
                'username' => 'imooc_shop@163.com',
                'password' => 'imooc123',
                'port' => '465',
                'encryption' => 'ssl',
            ],
        ],
    ]
];
