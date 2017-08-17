<?php
/**
 * Created by PhpStorm.
 * User: daan
 * Date: 8/17/17
 * Time: 1:25 PM
 */

namespace StudioSeptember\Sendgrid;


use SendGrid;
use SendGrid\Email;
use SendGrid\Mail;
use SendGrid\Content;
use SendGrid\Response;

use Swift_Mime_Message;

class Transport extends \Illuminate\Mail\Transport\Transport {

	protected $sendgrid;

	public function __construct($key) {
		$this->sendgrid = new SendGrid($key);
	}

	public function send(Swift_Mime_Message $message, &$failedRecipients = null)
	{
		$this->beforeSendPerformed($message);

		$addressees = [];
		if($message->getTo()){
			foreach($message->getTo() as $email => $name){
				$addressees[] = ['email' => $email, 'name' => $name];
			}
		}
		if($message->getCc()){
			foreach(@$message->getCc() as $email => $name){
				$addressees[] = ['email' => $email, 'name' => $name];
			}
		}
		if($message->getBcc()){
			foreach(@$message->getBcc() as $email => $name){
				$addressees[] = ['email' => $email, 'name' => $name];
			}
		}

		$success = 0;

		foreach($addressees as $addressee){
			try {
				$messageFrom = $message->getFrom();

				$from = new Email(array_first($messageFrom), array_first(array_keys($messageFrom)));


				$to = new Email($addressee['name'], $addressee['email']);
				$content = new Content('text/html', $message->getBody());
				$email = new Mail($from, $message->getSubject(), $to, $content);

				/** @var Response $res */
				$res = $this->sendgrid->client->mail()->send()->post($email);

				if(!in_array($res->statusCode(), [200, 201, 202])){
//					dd($email, $res);
					throw new \Exception("Invalid response code: " . $res->statusCode());
				}

				$this->sendPerformed($message);
				$success++;

			}catch( \Exception $e){
				$failedRecipients[] = $addressee['email'];
			}
		}

		return $success;

	}

}