<?php

namespace App\Services;

class MailService
{
    public static function send(string $to, string $subject, string $htmlBody, string $textBody = ''): bool
    {
        $config = require dirname(__DIR__, 2) . '/config/mail.php';
        $host = trim($config['host'] ?? '');
        $username = trim($config['username'] ?? '');
        $password = trim($config['password'] ?? '');

        // Check if SMTP is configured
        if (empty($host) || empty($username)) {
            // Section 9: "If SMTP is not configured, do NOT pretend an email was sent. Log the failure safely."
            $logDir = dirname(__DIR__, 2) . '/storage/logs';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0775, true);
            }
            $logEntry = sprintf(
                "[%s] [SMTP_NOT_CONFIGURED] To: %s | Subject: %s | Message Content: %s\n",
                date('Y-m-d H:i:s'),
                $to,
                $subject,
                strip_tags($textBody ?: $htmlBody)
            );
            file_put_contents($logDir . '/mail.log', $logEntry, FILE_APPEND);
            return false;
        }

        // If host and credentials exist, attempt socket SMTP
        try {
            $port = (int) ($config['port'] ?? 587);
            $timeout = 10;
            $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
            if (!$socket) {
                throw new \Exception("Could not connect to SMTP host {$host}:{$port} - {$errstr} ({$errno})");
            }

            $response = fgets($socket, 515);
            fputs($socket, "EHLO " . gethostname() . "\r\n");
            $response = fgets($socket, 515);

            if ($config['encryption'] === 'tls') {
                fputs($socket, "STARTTLS\r\n");
                $response = fgets($socket, 515);
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                fputs($socket, "EHLO " . gethostname() . "\r\n");
                $response = fgets($socket, 515);
            }

            fputs($socket, "AUTH LOGIN\r\n");
            fgets($socket, 515);
            fputs($socket, base64_encode($username) . "\r\n");
            fgets($socket, 515);
            fputs($socket, base64_encode($password) . "\r\n");
            $authRes = fgets($socket, 515);

            if (!str_starts_with($authRes, '235')) {
                throw new \Exception("SMTP Authentication failed: {$authRes}");
            }

            $from = $config['from_address'] ?: 'noreply@smmpanel.local';
            fputs($socket, "MAIL FROM: <{$from}>\r\n");
            fgets($socket, 515);
            fputs($socket, "RCPT TO: <{$to}>\r\n");
            fgets($socket, 515);
            fputs($socket, "DATA\r\n");
            fgets($socket, 515);

            $headers = "From: " . ($config['from_name'] ?: 'SMM Panel') . " <{$from}>\r\n";
            $headers .= "To: <{$to}>\r\n";
            $headers .= "Subject: {$subject}\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";

            fputs($socket, $headers . $htmlBody . "\r\n.\r\n");
            fgets($socket, 515);
            fputs($socket, "QUIT\r\n");
            fclose($socket);

            return true;
        } catch (\Throwable $e) {
            $logDir = dirname(__DIR__, 2) . '/storage/logs';
            file_put_contents(
                $logDir . '/mail.log',
                sprintf("[%s] [SMTP_ERROR] %s\n", date('Y-m-d H:i:s'), $e->getMessage()),
                FILE_APPEND
            );
            return false;
        }
    }
}
