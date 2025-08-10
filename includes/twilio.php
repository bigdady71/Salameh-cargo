<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Twilio\Rest\Client;

/**
 * Send an OTP code via WhatsApp using Twilio
 * 
 * @param string $to The recipient's phone number (with country code)
 * @param string $code The OTP code to send
 * @return bool True if message was sent successfully, false otherwise
 */
function sendWhatsAppOTP(string $to, string $code): bool
{
    global $pdo;

    try {
        // Load Twilio credentials
        $accountSid = getenv('TWILIO_ACCOUNT_SID');
        $authToken = getenv('TWILIO_AUTH_TOKEN');
        $fromNumber = getenv('TWILIO_WHATSAPP_FROM');
        $templateSid = getenv('TWILIO_WHATSAPP_TEMPLATE_SID');

        // Debug logging
        error_log("Twilio Config - SID: " . substr($accountSid, 0, 8) . "..., From: $fromNumber");

        if (!$accountSid || !$authToken || !$fromNumber) {
            throw new Exception('Twilio credentials not configured properly');
        }

        // Initialize Twilio client
        $client = new Client($accountSid, $authToken);

        // Prepare message parameters
        $params = [
            'from' => $fromNumber
        ];

        if ($templateSid) {
            // Use approved template (do NOT send body)
            $params['contentSid'] = $templateSid;
            $params['contentVariables'] = '{"1":"' . $code . '"}';
        } else {
            // Use plain text (sandbox mode)
            $params['body'] = "Your verification code id: {$code} for Salameh Cargo. Please don't share it with anyone.";
        }

        // Format the phone number to ensure it has country code
        if (!preg_match('/^\+\d{1,}/', $to)) {
            $to = '+961' . ltrim($to, '0'); // Add Lebanon country code if not present
        }

        error_log("Attempting to send WhatsApp message to: $to");

        // Send the message

        try {
            $message = $client->messages->create(
                'whatsapp:' . $to,
                $params
            );
            error_log("Message sent successfully! SID: " . $message->sid);
            // Log successful send
            $stmt = $pdo->prepare('
                INSERT INTO logs (action_type, actor_id, details) 
                VALUES (:type, :actor_id, :details)
            ');
            $stmt->execute([
                'type' => 'otp_sent',
                'actor_id' => 0, // System action
                'details' => "WhatsApp OTP sent to $to"
            ]);
            return true;
        } catch (\Exception $e) {
            error_log('Twilio send error: ' . $e->getMessage());
            throw new Exception('Twilio error: ' . $e->getMessage());
        }
    } catch (Exception $e) {
        // Log the error
        $stmt = $pdo->prepare('
            INSERT INTO logs (action_type, actor_id, details) 
            VALUES (:type, :actor_id, :details)
        ');
        $stmt->execute([
            'type' => 'twilio_error',
            'actor_id' => 0, // System action
            'details' => 'WhatsApp OTP error: ' . $e->getMessage()
        ]);
        throw $e;
    }
}
