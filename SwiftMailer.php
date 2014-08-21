<?php
/**
 * Swift Mailer wrapper class.
 *
 * @author Sergii 'b3atb0x' hello@webkadabra.com
 */
class SwiftMailer extends CComponent
{

	/**
	 * smtp, sendmail or mail
	 */
	public $mailer = 'sendmail'; //
	/**
	 * SMTP outgoing mail server host
	 */
	public $host;
	/**
	 * Outgoing SMTP server port
	 */
	public $port = 25;
	/**
	 * SMTP Relay account username
	 */
	public $username;
	/**
	 * SMTP Relay account password
	 */
	public $password;
	/**
	 * SMTP security (ssl or tls)
	 */
	public $security;
	/**
	 * @param mixed Email address messages are going to be sent "from"
	 */
	public $from;
	/**
	 * @param string HTML Message Body
	 */
	public $body;
	/**
	 * @param string Alternative message body (plain text)
	 */
	public $altBody = null;
	/**
	 * sendmailCommand
	 */
	public $sendmailCommand = '/usr/bin/sendmail -t';
	public $logMailerActivity = false;

	/**
	 * logMailerDebug
	 *
	 * @description  outputs additional debug info from mailer
	 * 		only available when logMailerActivity == true
	 */
	public $logMailerDebug = false;

	protected $_subject = null;
	protected $_addresses = array();
	protected $_attachments = array();

	public function init()
	{
		if (!class_exists('Swift', false))
        {
        	$this->registerAutoloader();
        	// include the SwiftMailer Dependencies
        	require_once dirname(__FILE__). '/lib/dependency_maps/cache_deps.php';
        	require_once dirname(__FILE__) . '/lib/dependency_maps/mime_deps.php';
        	require_once dirname(__FILE__) . '/lib/dependency_maps/message_deps.php';
        	require_once dirname(__FILE__) . '/lib/dependency_maps/transport_deps.php';
        	//Load in global library preferences
			require_once dirname(__FILE__) . '/lib/preferences.php';
		}
	}

	protected function registerAutoloader()
	{
		require_once(dirname(__FILE__).'/lib/classes/Swift.php');
		Swift::registerAutoLoad();
        // Register SwiftMailer's autoloader before Yii for correct class loading.
        $autoLoad = array('Swift', 'autoload');
        spl_autoload_unregister($autoLoad);
        Yii::registerAutoloader($autoLoad);
    }

	public function addAddress($address)
	{
		if (!in_array($address, $this->_addresses))
			$this->_addresses[] = $address;
		return $this;
	}
	public function subject($subject)
	{
		$this->_subject = $subject;
		return $this;
	}
	public function setSubject($subject)
	{
		$this->_subject = $subject;
	}
	public function addFile($address)
	{
		if (!in_array($address, $this->_attachments))
			$this->_attachments[] = $address;
		return $this;
	}

	public function msgHTML($body)
	{
		$this->body = $body;
		if ($this->altBody == null) {
			$this->altBody = strip_tags($this->body);
		}
		return $this;
	}

	/**
	 * Helper function to send emails like this:
	 * <code>
	 *        Yii::app()->mailer->addAddress($email);
	 *        Yii::app()->mailer->subject($newslettersOne['name']);
	 *        Yii::app()->mailer->msgHTML($template['content']);
	 *        Yii::app()->mailer->send();
	 * </code>
	 * or
	 * <code>
	 *        Yii::app()->mailer->addAddress($email)
	 *        	->subject($newslettersOne['name'])
	 *        	->msgHTML($template['content'])
	 *        	->send();
	 * </code>
	 * @return boolean Whether email has been sent or not
	 */
	public function send()
	{
	 	$logger = null;
		//Create the Transport
		$transport = $this->loadTransport();

		//Create the Mailer using your created Transport
		$mailer = Swift_Mailer::newInstance($transport);

		if ($this->logMailerActivity && $this->logMailerDebug) {

			$logger = new Swift_Plugins_Loggers_ArrayLogger();
			// pass false to give plain text output for console in EchoLogger
			//$logger = new Swift_Plugins_Loggers_EchoLogger(false);
			$mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($logger));
		}

		//Create a message
		$message = Swift_Message::newInstance($this->_subject)
			->setFrom($this->from)
			->setTo($this->_addresses);

		if ($this->body) {
			$message->addPart($this->body, 'text/html');
		}
		if ($this->altBody) {
			$message->setBody($this->altBody);
		}

		if ($this->_attachments) {
			foreach($this->_attachments as $path)
				$message->attach(Swift_Attachment::fromPath($path));
		}

		$result = $mailer->send($message);
		if ($this->logMailerActivity === true) {
			if (!$result) {
				$logMessage = 'Failed to send "' . $this->_subject . '" email to [' . implode(', ', $this->addressesFlat()) . ']'
					. "\nMessage:\n"
					. ($this->altBody ? $this->altBody : $this->body);
				Yii::log($logMessage, 'error', 'appMailer');
				if ($this->logMailerDebug)
				{
					$output = $logger->dump();
					Yii::log($output, 'error', 'appMailer');
				}
				goto COMPLETE;
			}

			$logMessage = 'Sent email "' . $this->_subject . '" to [' . implode(', ', $this->addressesFlat()) . ']'
				. "\nMessage:\n"
				. ($this->altBody ? $this->altBody : $this->body);
			Yii::log($logMessage, 'info','appMailer');
			if ($this->logMailerDebug)
			{
				$output = $logger->dump();
				Yii::log($output, 'info', 'appMailer');
			}

		}

	COMPLETE:
		$this->clearAddresses();

		return $result;
	}

	public function clearAddresses()
	{
		$this->_addresses = array();
	}

	public function addressesFlat()
	{
		$return = array();
		if (!empty($this->_addresses)) {
			foreach ($this->_addresses as $address) {
				if (is_array($address)) {
					$return[] = $address[0];
				} else
					$return[] = $address;
			}
		}

		return $return;
	}

	/* Helpers */
	public function preferences()
	{
		return Swift_Preferences;
	}

	public function attachment()
	{
		return Swift_Attachment;
	}

	public function newMessage($subject)
	{
		return Swift_Message::newInstance($subject);
	}

	public function mailer($transport = null)
	{
		return Swift_Mailer::newInstance($transport);
	}

	public function image()
	{
		return Swift_Image;
	}

	public function smtpTransport($host = null, $port = null, $security = null)
	{
		return Swift_SmtpTransport::newInstance($host, $port, $security);
	}

	public function sendmailTransport($command = null)
	{
		return Swift_SendmailTransport::newInstance($command);
	}

	public function mailTransport()
	{
		return Swift_MailTransport::newInstance();
	}

	protected function loadTransport()
	{
		if ($this->mailer == 'smtp') {
			$transport = self::smtpTransport($this->host, $this->port, $this->security);

			if ($this->username)
				$transport->setUsername($this->username);
			if ($this->password)
				$transport->setPassword($this->password);
		} elseif ($this->mailer == 'mail') {
			$transport = self::mailTransport();
		} elseif ($this->mailer == 'sendmail') {
			$transport = self::sendmailTransport($this->sendmailCommand);
		}

		return $transport;
	}
}
