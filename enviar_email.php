<?php
// Cargar clases PHPMailer manualmente
// Ajusta la ruta correctamente a tu carpeta 'src'
require 'PHPMailer-master/PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data['action'] === 'enviar_email') {
        $to = $data['to'];
        $subject = $data['subject'];
        $message = $data['message'];

        $mail = new PHPMailer(true);

        try {
            // Configuración SMTP de Gmail de donde sale el correo
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'yarelirios86@gmail.com';
            $mail->Password = 'jhum doxc jjgz npmc'; 
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Remitente y destinatario
            $mail->setFrom('ri399580@uaeh.edu.mx', 'KASCOM');
            $mail->addAddress($to);
            $mail->addReplyTo('yarelirios86@gmail.com', 'Respuesta');

            // Contenido del correo
            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body    = $message;

            $mail->send();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $mail->ErrorInfo]);
        }
    }
}
