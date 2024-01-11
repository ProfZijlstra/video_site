<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'lib/PHPMailer/src/Exception.php';
require 'lib/PHPMailer/src/PHPMailer.php';
require 'lib/PHPMailer/src/SMTP.php';


/**
 * Email Helper Class
 * @author mzijlstra 01/08/2023
 */

#[Controller]
class MailHlpr
{

    /**
     * $to and $replyTo arguments can either be an array of two elements 
     *      ["email@address", "firstname lastname"]
     * or a single string containing just the email address
     *      "email@address"
     * 
     * replyTo can also be null, in which case it is ignored
     */
    public function mail($to, $subject, $msg, $replyTo = null)
    {
        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
        try {
            //Server settings
            //$mail->SMTPDebug = 2;                                 // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.dreamhost.com';                  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'videos@manalabs.org';             // SMTP username
            $mail->Password = MAIL_PASS;                           // SMTP password
            $mail->SMTPSecure = 'ssl';                            // Enable SSL encryption, TLS also accepted with port 465
            $mail->Port = 465;                                    // TCP port to connect to

            //Recipients
            $mail->setFrom('videos@manalabs.org', 'Manalabs Video System');          //This is the email your form sends From
            if (is_array($to)) {
                $mail->addAddress($to[0], $to[1]);
            } else if (is_string($to)) {
                $mail->addAddress($to);
            }
            if (is_array($replyTo)) {
                $mail->addReplyTo($replyTo[0], $replyTo[1]);
            } else if (is_string($replyTo)) {
                $mail->addReplyTo($replyTo);
            }

            //Content
            $mail->isHTML(false);                                  // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $msg;

            $mail->send();
        } catch (Exception) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        }
    }
}

