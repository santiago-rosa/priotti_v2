<?php
require 'phpMailer/src/Exception.php';
require 'phpMailer/src/PHPMailer.php';
require 'phpMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;


class Email
{


    public static function selector($opc)
    {
        switch ($opc) {
            case 1:
                return self::sendEmail2();
                break;
            case 2:
                return self::enviarMsj();
                break;
            default:
                return "Sin selección";
                break;
        }
    }

    private static function esUsuario()
    {
        if (isset($_SESSION["usuario"]))
            return true;
        return false;
    }

    public static function enviarMsj()
    {
        if (isset($_REQUEST['email'])) {
            $email = $_REQUEST['email'];
            //$admin_email = "santiagorrosa@hotmail.com";       
            $admin_email = "fpriotti@felipepriotti.com.ar";
            $email_exp = '/^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/';
            if (!preg_match($email_exp, $email)) {
                return 'La dirección de correo proporcionada no es válida.';
            }
            $subject = 'Consulta de ' . $_REQUEST['nombre'];
            $comment = $_REQUEST['mensaje'] . "\r\n" . 'Telefono: ' . $_REQUEST['telefono']
                . "\r\n" . 'Email: ' . $email;
            //send email
            mail($admin_email, $subject, $comment, "From:" . $email);
            //Email response
            return "Gracias por contactarnos! <br> En breve nos comunicaremos";
        }
    }

    private static function enviarPedido()
    {
        include_once("../Modelo/DAOPedidos.php");
        $admin_email_dos = "contacto@felipepriotti.com.ar";
        $admin_email = "fpriotti@felipepriotti.com.ar";
        $subject = 'Pedido de Cliente numero: ' . $_SESSION['usuario'] . ' - ' . $_SESSION['nombre'];
        $message = DAOPedidos::selector(5); //Obtengo el pedido en formato html
        $message = '<strong>Comentarios:</strong> ' . utf8_decode($_POST['comentario']) . '<br><br>' . $message;
        if ($message != false) {
            $headers = "From: Carrito de Compras \r\n";
            //$headers .= "Reply-To: ". strip_tags($_POST['req-email']) . "\r\n";
            //$headers .= "CC: susan@example.com\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            //Send email
            mail($admin_email, $subject, $message, $headers);
            mail($admin_email_dos, $subject, $message, $headers);
            return "Su pedido ha sido enviado!";
        }
        return "El carrito está vacío!";
    }

    //$password = 'L9fA2@J=x4Sl';

    public static function sendEmail()
    {
        include_once("../Modelo/DAOPedidos.php");
        $mail = new PHPMailer(true); // Enable exceptions
        $host = 'mail.felipepriotti.com.ar';
        $username = 'contacto@felipepriotti.com.ar';
        $password = 'L9fA2@J=x4Sl';
        $port = 465;
        //$to = "contacto@felipepriotti.com.ar";
        $to = "santiagorrosa@gmail.com";
        $subject = 'Pedido de Cliente numero: ' . $_SESSION['usuario'] . ' - ' . $_SESSION['nombre'];
        //$body = DAOPedidos::selector(5); //Obtengo el pedido en formato html

        //$body = '<strong>Comentarios:</strong> ' . utf8_decode($_POST['comentario']) . '<br><br>' . $body;
        try {
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->SMTPAuth = true;
            $mail->Username = $username;
            $mail->Password = $password;
            $mail->SMTPSecure = 'tls';
            $mail->Port = $port;

            $mail->setFrom($username, 'Mailer');
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body = "<i>Mail body in HTML</i>"; //$body;
            $mail->send();
            return 'Message has been sent';
        } catch (Exception $e) {
            error_log('Error al enviar pedido con nuevo metodo: ' . $mail->ErrorInfo);
            return 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
        }
    }

    public static function sendEmail2()
    {
        include_once("../Modelo/DAOPedidos.php");
        $mail = new PHPMailer(true); // Enable exceptions
        $body = DAOPedidos::selector(5); //Obtengo el pedido en formato html

        $body = '<strong>Comentarios:</strong> ' . utf8_decode($_POST['comentario']) . '<br><br>' . $body;
        try {
            $mail->SMTPOptions = array(
                'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
                )
                );
            // Server settings
            //$mail->SMTPDebug = 2; // Enable verbose debug output
            $mail->isSMTP(); // Send using SMTP
            $mail->Host = 'mail.felipepriotti.com.ar'; // Set the SMTP server to send through
            $mail->SMTPAuth = true; // Enable SMTP authentication
            $mail->Username = 'contacto@felipepriotti.com.ar'; // SMTP username
            $mail->Password = 'L9fA2@J=x4Sl'; // SMTP password
            $mail->SMTPSecure = 'ssl'; // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->Port = 465; // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
            // Recipients
            $mail->setFrom('contacto@felipepriotti.com.ar', 'Pedidos Priotti');
            $mail->addAddress('fpriotti@felipepriotti.com.ar'); // Add a recipient
            $mail->addAddress('contacto@felipepriotti.com.ar'); // Add a recipient
            // Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = 'Pedido de Cliente numero: ' . $_SESSION['usuario'] . ' - ' . $_SESSION['nombre'];
            $mail->Body = $body;//'This is the HTML message body <b>in bold!</b>';
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
            $mail->send();
            return 'Message has been sent';
        } catch (Exception $e) {
            error_log('Error al enviar pedido con nuevo metodo: ' . $mail->ErrorInfo);
            return 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
        }
    }
}

if (isset($_POST["opcemail"]))
    echo Email::selector($_POST["opcemail"]);