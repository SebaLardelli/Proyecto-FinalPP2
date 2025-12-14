<?php

namespace App\Services;

// EmailService - Envía emails usando EmailJS API
class EmailService {
    
    // Enviar email de verificación de cuenta
    public function enviarVerificacion($email, $nombre, $urlVerificacion) {
        return $this->enviar($_ENV['EMAILJS_TEMPLATE_ID'], [
            'to_email' => $email,
            'to_name' => $nombre,
            'confirm_url' => $urlVerificacion
        ]);
    }

    // Enviar email con código OTP (15 minutos)
    public function enviarOTP($email, $codigo) {
        $expira = new \DateTime();
        $expira->modify('+15 minutes');
        $tiempo = $expira->format('d/m/Y H:i');
        
        return $this->enviar($_ENV['EMAILJS_TEMPLATE_ID_OTP'], [
            'passcode' => $codigo,
            'time' => $tiempo,
            'to_email' => $email
        ]);
    }

    // Enviar email via EmailJS API
    private function enviar($templateId, $params) {
        $data = [
            'service_id' => $_ENV['EMAILJS_SERVICE_ID'],
            'template_id' => $templateId,
            'user_id' => $_ENV['EMAILJS_PUBLIC_KEY'],
            'accessToken' => $_ENV['EMAILJS_PRIVATE_KEY'],
            'template_params' => $params
        ];

        $ch = curl_init('https://api.emailjs.com/api/v1.0/email/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("Error enviando email: HTTP $httpCode - $response");
            return false;
        }

        return true;
    }
}


