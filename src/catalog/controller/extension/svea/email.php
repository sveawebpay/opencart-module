<?php


class ControllerExtensionSveaEmail extends Controller {

	public function index() 
	{

		if (!array_key_exists('email', $this->session->data['guest']) || !isset($this->session->data['guest']['email'])) {
			return;
		}

		$emailAddress = $this->session->data['guest']['email'];

		if ($this->isEmail($emailAddress) && $this->session->data['payment_address']['iso_code_2'] === 'FI' && $this->session->data['shipping_address']['iso_code_2'] === 'FI') {
			try {
				$this->sendMail($emailAddress);
				http_response_code(201);				
			} catch (Exception $e) {
				http_response_code(401);
			}
		}
	}

	private function sendMail($emailAddress) {

		$mail = new Mail();
		$mail->protocol = $this->config->get('config_mail_protocol');
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
		$mail->smtp_port = $this->config->get('config_mail_smtp_port');
		$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

		

		$mail->setTo($emailAddress);
		
		$subject = 'Terms';
		$message = dirname(__FILE__).'/../../../view/terms/terms.html';
		$messageHtml = file_get_contents($message);
		$this->log->write($messageHtml);
		
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
		$mail->setSubject($subject);
		$mail->setHtml($messageHtml);
		$mail->send();
	}


	private function isEmail($email)
    {
        return !empty($email) && preg_match($this->cleanNonUnicodeSupport('/^[a-z\p{L}0-9!#$%&\'*+\/=?^`{}|~_-]+[.a-z\p{L}0-9!#$%&\'*+\/=?^`{}|~_-]*@[a-z\p{L}0-9]+(?:[.]?[_a-z\p{L}0-9-])*\.[a-z\p{L}0-9]+$/ui'), $email);
    }

    private function cleanNonUnicodeSupport($pattern)
    {
        if (!defined('PREG_BAD_UTF8_OFFSET')) {
            return $pattern;
        }
        return preg_replace('/\\\[px]\{[a-z]{1,2}\}|(\/[a-z]*)u([a-z]*)$/i', '$1$2', $pattern);
    }

}