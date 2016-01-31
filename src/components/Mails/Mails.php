<?php


namespace HylaComponents\Mails;

use Hyla\Config\Conf;
use HylaComponents\Mails\Tpl\MailsTpl;

/**
 * Class Mails
 * @package HylaComponents\Mails
 */
class Mails {

	private $type;

	/**
	 * Mails constructor.
	 * @param string $type
	 */
	public function __construct($type = 'txt') {
		$this->type = $type;
		$conf = Conf::get('emails');
		var_dump($conf);
		ini_set("SMTP", $conf['smtp']);
		ini_set("smtp_port", $conf['smtp_port']);
		ini_set("sendmail_from", $conf['emails']['webmaster']);

	}

	/**
	 * @param string $type
	 * @return Mails
	 */
	public static function init($type = 'txt') {
		$mail = new Mails($type);
		return $mail;
	}

	/**
	 * @param $vars
	 * @param $matrix
	 * @return string
	 */
	private function constructHTMLMessage($vars, $matrix) {
		return MailsTpl::display($vars, "$matrix.twig");
	}

	/**
	 * @param $name
	 * @param $email
	 * @return string
	 */
	private function constructHeaderMail($name, $email) {
		$headers = "From: \"$name\"<$email>\n";
		$headers .= "Reply-To: \"$name\"<$email>\n";
		switch ($this->type) {
			case 'txt':
				$contentType = 'text/plain';
				break;
			
			case 'html':
				$contentType = 'text/html';
				break;
			
			default:
				$contentType = 'text/plain';
				break;
		}

		return $headers . "Content-Type: $contentType;\n Content-Transfer-Encoding: 8bit;\n charset=\"UTF-8\"";
	}


	/**
	 * @param string $destinataire = string
	 * @param string $sujet = string
	 * @param mixed $message = $type->'txt'  => string
	 * 			  	   				->'html' => array ($vars, $matrix)
	 * @param array $headers = array ( $nameExpediteur, $emailExpediteur )
	 * 
	 */
	public function sendMail($destinataire,$sujet,$message,$headers) {
		$headersToSend = $this->constructHeaderMail($headers[0], $headers[1]);
		$messageToSend = ($this->type == 'html' && is_array($message)) ? $this->constructHTMLMessage($message[0], $message[1]) : $message ; 
		if (mail($destinataire,$sujet,$messageToSend,$headersToSend)) {
			return true;
		} else {
			return false;
		}

	}
}
