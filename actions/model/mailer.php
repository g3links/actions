<?php

namespace model;

class mailer {

    public function sendMail($mailsettigs, $membername, $memberemail, $subject, $mailbodyArray) {

        $mail = new \PHPMailer\PHPMailer\PHPMailer();                            // Passing `true` enables exceptions

//        $mail->SMTPDebug = 3;                               // Enable verbose debug output
        $mail->isSMTP();                                    // default: Set mailer to use SMTP
        $mail->SMTPAuth = true;                             // default: Enable SMTP authentication
        $mail->isHTML(true);                                  // default: Set email format to HTML 
        $mail->Host = $mailsettigs->host;                 // Specify main and backup SMTP servers (; separation)
        $mail->Username = $mailsettigs->username;         // SMTP username
        $mail->Password = $mailsettigs->password;         // SMTP password
        $mail->SMTPSecure = $mailsettigs->SMTPSecure;     //'tls'; Enable TLS encryption, `ssl` also accepted
        $mail->Port = $mailsettigs->Port;                 // TCP port to connect to
        $mail->setFrom($mailsettigs->sendfrom, $mailsettigs->sender);  // header name
//$mail->addAddress('ellen@example.com');                   // Name is optional
//$mail->addReplyTo('info@example.com', 'Information');
//$mail->addCC('cc@example.com');
//$mail->addBCC('bcc@example.com');
//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

        // as many addAddress()
        if (filter_input(INPUT_SERVER, 'SERVER_NAME') === 'localhost') {
            $mail->addAddress(\trim($mailsettigs->testemail), \trim($membername)); 
        } else {
            $mail->addAddress(\trim($memberemail), \trim($membername));     // Add a recipient
        }
//        $mail->addReplyTo('info@example.com', 'Information');
//        $mail->addCC('cc@example.com');
//        $mail->addBCC('bcc@example.com');

        //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

        $mail->Subject = $subject;

        $mailbodystring = '';
        foreach ($mailbodyArray as $row) 
            $mailbodystring .= $row;

        $mail->Body = $mailbodystring;
//        $mail->AltBody = '';    //This is the body in plain text for non-HTML mail clients

        $mssgError = null;
        if (!$mail->send()) {
            $mssgError = $mail->ErrorInfo;
        }

        return $mssgError;
    }

}
