This is basically updated wrapper of SwiftMailer which can be used as application component.

##Installation

1. Unzip extension contents to protected/extensions/ folder
2. Set up yii component via config:

~~~
'mailer' => array(
	'class' => 'path.to.swiftMailer.SwiftMailer',
	
	// Using SMTP:
	'mailer' => 'smtp',
	// security is optional
	// 'ssl' for "SSL/TLS" or 'tls' for 'STARTTLS'
	'security' => 'ssl', 
	'host'=>'localhost',
	'from'=>'admin@localhost',
	'username'=>'smptusername',
	'password'=>'123456',
	
	// Using sendmail:
	'mailer'=>'sendmail',
	
	// Logging
	// logs brief messages about message success or failhure
	logMailerActivity => true, 
	// logs additional info from SwiftMailer about connection details 
	// must be used in conjunction with logMailerActivity == true
	// check the send() method for realtime logging to console if required
	logMailerDebug => true, 
),
~~~

##Usage

~~~
Yii::app()->mailer->addAddress($email);
Yii::app()->mailer->subject("Let's do this!");
Yii::app()->mailer->msgHTML("<a href='http://site.com'>test</a>");
Yii::app()->mailer->send();

or

Yii::app()->mailer->addAddress($email)
	->subject("Let's do this!")
	->msgHTML("<a href='http://site.com'>test</a>")
	->send();
~~~

Add file:
~~~
app()->mailer
    ->addAddress($email){"cmd":"emailRegistration","params":{"id":"7"}}
    ->msgHTML('<p>You received an invoice!</p>')
    ->addFile($filepath)
    ->send();
~~~

Please, take a look a SwiftMailer::send() function

##Resources

 * [Swift Mailer Documentaion](http://swiftmailer.org/)
 * [Original extension]http://www.yiiframework.com/extension/swiftmailer
 * [Webkadabra] http://www.webkadabra.com

