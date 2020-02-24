<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (defined('ROOT')) {
	require ROOT . 'packages/PHPMailer/src/Exception.php';
	require ROOT . 'packages/PHPMailer/src/PHPMailer.php';
	require ROOT . 'packages/PHPMailer/src/SMTP.php';
}

define('SMTP_HOST', '');
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_PORT', '');

class SMTP {
	public static function SendMail($email, $head, $subject, $message) {
		$mail = new PHPMailer(true);

		try {
		    $mail->isSMTP();
		    $mail->Host       = SMTP_HOST;
		    $mail->SMTPAuth   = true;
		    $mail->Username   = SMTP_USERNAME;
		    $mail->Password   = SMTP_PASSWORD;
		    $mail->SMTPSecure = 'ssl';
		    $mail->Port       = SMTP_PORT;

		    $mail->setFrom(SMTP_USERNAME, SERVER_NAME);
		    $mail->addAddress($email, SERVER_NAME . ' | ' . $head);

		    $mail->isHTML(true);
		    $mail->Subject = SERVER_NAME . ' | ' . $subject;
		    $mail->Body    = $message;

		    $mail->send();
		} catch (Exception $e) {
		    unset($e);
		}
	}
}
