This is basically updated wrapper of SwiftMailer which can be used as application component.

##Installation

1. Unzip extension contents to protected/extensions/ folder
2. Set up yii component via config:

~~~
'mailer' => array(
	'class' => 'ext.swiftMailer.SwiftMailer',
	// For SMTP
	'mailer' => 'smtp',
	'host'=>'localhost',
	'From'=>'admin@localhost',
	'username'=>'smptusername',
	'password'=>'123456',
	// For sendmail:
	'mailer'=>'sendmail',
),
~~~

##Usage

~~~
Yii::app()->mailer->AddAddress($email);
Yii::app()->mailer->Subject = "Let's do this!";
Yii::app()->mailer->MsgHTML("<a href='http://site.com'>test</a>");
Yii::app()->mailer->Send();
~~~

Add file:
~~~
app()->mailer
    ->AddAddress($email)
    ->MsgHTML('<p>You received an invoice!</p>')
    ->AddFile($filepath)
    ->Send();
~~~

Please, take a look a SwiftMailer::Send() function

##Resources

 * [Swift Mailer Documentaion](http://swiftmailer.org/)
 * [Original extension]http://www.yiiframework.com/extension/swiftmailer
 * [Webkadabra] http://www.webkadabra.com

