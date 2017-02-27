<?php

require('config.php');
require_once('recaptchalib.php');

$error = false;
$captchaError = false;
$captchaErrorMessage = '';

if (isset($_POST["recaptcha_response_field"]) && isset($_POST["recaptcha_challenge_field"])) {
        $resp = recaptcha_check_answer ($reCaptchaPrivateKey,
		$_SERVER["REMOTE_ADDR"],
		$_POST["recaptcha_challenge_field"],
		$_POST["recaptcha_response_field"]);

	if (!$resp->is_valid) {
		$error = true;
		$captchaError = true;
		$captchaErrorMessage = $resp->error;
	}
}


if ((isset($_POST['name'])) && (strlen(trim($_POST['name'])) > 0)) {
	$name = stripslashes(strip_tags($_POST['name']));
} else {
	$error = true;
}

if ((isset($_POST['email'])) && (strlen(trim($_POST['email'])) > 0)) {
	$email = stripslashes(strip_tags($_POST['email']));
} else {
	$error = true;
}

if ((isset($_POST['message'])) && (strlen(trim($_POST['message'])) > 0)) {
	$message = stripslashes(strip_tags($_POST['message']));
} else {
	$error = true;
}

if(!$error) {
				$messageContainer = "
					font-size:13px;
					line-height:26px;
					float:left;
					width:100%;
					padding:5px 5px 0 5px;
					background:#e0e0e0;
					border:1px solid #d1d1d1;
					";

				$leftContainer = "
					color:#565656;
					padding:5px;
					margin:0 3% 5px 0;
					width:97%;
					background:#fff;
					border:1px solid #d1d1d1;
					";
				
				$strongStyle = "
					color:#646464;
					font-size:15px;
				";
				
				$body = '<html><body><div style="'.$messageContainer.'"><div style="'.$leftContainer.'"><strong style="'.$strongStyle.'">'.$nameHeader.':</strong> '.$name.'</div><div style="'.$leftContainer.'"><strong style="'.$strongStyle.'">'.$emailHeader.':</strong> '.$email.'</div>';
				$body.= '<div style="'.$leftContainer.'"><strong style="'.$strongStyle.'">'.$messageHeader.':</strong><p>'.$message.'</p></div></div></body></html>';

				if(file_exists("class-phpmailer.php")) {
					ob_start();
					require_once("class-phpmailer.php");

					$mail = new PHPMailer();

					$mail->From = $email;
					$mail->FromName = $name;
					$mail->AddAddress($adminEmail);

					$mail->WordWrap = 50;
					$mail->IsHTML(true);

					$mail->Subject = $subject;
					$mail->Body = $body;
					$mail->AltBody = $message;

					if (!$mail->Send()) {
						$error = true;
					}
					$phpErr = ob_get_clean();
				}
				else $phpErr = true;

				
				if($phpErr) {
					$responseMessage = $errorResponseMessage;
					$phpErr = true;
				}
				else{
					$responseMessage = $succesResponseMessage;
					$phpErr = false;
				}
}
else{
	$responseMessage = $errorResponseMessage;
	$phpErr = true;
}
					
header("Content-Type: application/json");
echo json_encode(array('message' => $responseMessage, 'error' => $phpErr, 'captchaerror'=> $captchaError, 'captchaerrormessage' => $captchaErrorMessage));