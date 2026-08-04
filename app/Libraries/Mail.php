<?php
/**
 * AI Banking GRC Platform - Mail Library
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Libraries
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This library provides enterprise email functionality:
 * - SMTP support
 * - HTML emails
 * - Attachments
 * - OTP emails
 * - Welcome emails
 * - Reset password emails
 * - Notification emails
 */

declare(strict_types=1);

namespace App\Libraries;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mail
{
    /**
     * @var PHPMailer PHPMailer instance
     */
    private PHPMailer $mailer;

    /**
     * @var Logger Logger instance
     */
    private Logger $logger;

    /**
     * @var bool Whether mail is configured
     */
    private bool $configured = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->logger = new Logger();

        try {
            $this->mailer = new PHPMailer(true);
            $this->configure();
        } catch (Exception $e) {
            $this->logger->error('Mail configuration error: ' . $e->getMessage());
        }
    }

    /**
     * Configure mailer
     * 
     * @return void
     */
    private function configure(): void
    {
        // Server settings
        if (getenv('MAIL_DRIVER') === 'smtp') {
            $this->mailer->isSMTP();
            $this->mailer->Host = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = getenv('MAIL_USERNAME') ?: '';
            $this->mailer->Password = getenv('MAIL_PASSWORD') ?: '';
            $this->mailer->SMTPSecure = getenv('MAIL_ENCRYPTION') ?: 'tls';
            $this->mailer->Port = getenv('MAIL_PORT') ?: 587;
        } else {
            $this->mailer->isMail();
        }

        // Default from
        $fromAddress = getenv('MAIL_FROM_ADDRESS') ?: 'noreply@grc-platform.com';
        $fromName = getenv('MAIL_FROM_NAME') ?: 'AI Banking GRC Platform';
        $this->mailer->setFrom($fromAddress, $fromName);

        // Default charset
        $this->mailer->CharSet = 'UTF-8';

        $this->configured = true;
        $this->logger->info('Mail configured successfully');
    }

    /**
     * Send email
     * 
     * @param string $to
     * @param string $subject
     * @param string $body
     * @param array $options
     * @return bool
     */
    public function send(string $to, string $subject, string $body, array $options = []): bool
    {
        if (!$this->configured) {
            $this->logger->error('Mail not configured');
            return false;
        }

        try {
            // Clear previous recipients
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            // Recipients
            $this->mailer->addAddress($to);

            // CC
            if (!empty($options['cc'])) {
                $cc = is_array($options['cc']) ? $options['cc'] : [$options['cc']];
                foreach ($cc as $address) {
                    $this->mailer->addCC($address);
                }
            }

            // BCC
            if (!empty($options['bcc'])) {
                $bcc = is_array($options['bcc']) ? $options['bcc'] : [$options['bcc']];
                foreach ($bcc as $address) {
                    $this->mailer->addBCC($address);
                }
            }

            // Reply to
            if (!empty($options['reply_to'])) {
                $this->mailer->addReplyTo($options['reply_to']);
            }

            // Attachments
            if (!empty($options['attachments'])) {
                $attachments = is_array($options['attachments']) ? $options['attachments'] : [$options['attachments']];
                foreach ($attachments as $attachment) {
                    $this->mailer->addAttachment($attachment);
                }
            }

            // Content
            $this->mailer->Subject = $subject;
            $this->mailer->isHTML(true);
            $this->mailer->Body = $body;

            // Plain text alternative
            if (!empty($options['text'])) {
                $this->mailer->AltBody = $options['text'];
            } else {
                $this->mailer->AltBody = strip_tags($body);
            }

            // Send
            $this->mailer->send();

            $this->logger->info('Email sent', [
                'to' => $to,
                'subject' => $subject
            ]);

            return true;

        } catch (Exception $e) {
            $this->logger->error('Email sending failed: ' . $e->getMessage(), [
                'to' => $to,
                'subject' => $subject
            ]);
            return false;
        }
    }

    /**
     * Send OTP email
     * 
     * @param string $to
     * @param string $otp
     * @param string $name
     * @return bool
     */
    public function sendOTP(string $to, string $otp, string $name = ''): bool
    {
        $subject = 'Your OTP Code - ' . APP_NAME;
        
        $body = $this->renderTemplate('otp', [
            'name' => $name,
            'otp' => $otp,
            'app_name' => APP_NAME
        ]);

        return $this->send($to, $subject, $body);
    }

    /**
     * Send password reset email
     * 
     * @param string $to
     * @param string $token
     * @param string $name
     * @return bool
     */
    public function sendResetPassword(string $to, string $token, string $name = ''): bool
    {
        $subject = 'Reset Your Password - ' . APP_NAME;
        $resetLink = BASE_URL . '/reset-password/' . $token;

        $body = $this->renderTemplate('reset-password', [
            'name' => $name,
            'reset_link' => $resetLink,
            'app_name' => APP_NAME
        ]);

        return $this->send($to, $subject, $body);
    }

    /**
     * Send welcome email
     * 
     * @param string $to
     * @param string $name
     * @param string $username
     * @return bool
     */
    public function sendWelcome(string $to, string $name, string $username): bool
    {
        $subject = 'Welcome to ' . APP_NAME;

        $body = $this->renderTemplate('welcome', [
            'name' => $name,
            'username' => $username,
            'app_name' => APP_NAME,
            'login_url' => BASE_URL . '/login'
        ]);

        return $this->send($to, $subject, $body);
    }

    /**
     * Send notification email
     * 
     * @param string $to
     * @param string $subject
     * @param string $message
     * @param array $data
     * @return bool
     */
    public function sendNotification(string $to, string $subject, string $message, array $data = []): bool
    {
        $body = $this->renderTemplate('notification', [
            'subject' => $subject,
            'message' => $message,
            'data' => $data,
            'app_name' => APP_NAME
        ]);

        return $this->send($to, $subject, $body);
    }

    /**
     * Render email template
     * 
     * @param string $template
     * @param array $data
     * @return string
     */
    private function renderTemplate(string $template, array $data = []): string
    {
        $templatePath = VIEW_PATH . '/emails/' . $template . '.php';

        if (!file_exists($templatePath)) {
            // Fallback template
            return $this->renderFallbackTemplate($data);
        }

        extract($data);
        ob_start();
        require $templatePath;
        return ob_get_clean();
    }

    /**
     * Render fallback template
     * 
     * @param array $data
     * @return string
     */
    private function renderFallbackTemplate(array $data): string
    {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
        $html .= '<style>body{font-family: Arial, sans-serif; padding: 20px;}</style>';
        $html .= '</head><body>';
        $html .= '<h2>' . ($data['app_name'] ?? APP_NAME) . '</h2>';
        
        if (!empty($data['name'])) {
            $html .= '<p>Dear ' . $data['name'] . ',</p>';
        }

        if (!empty($data['message'])) {
            $html .= '<p>' . $data['message'] . '</p>';
        }

        if (!empty($data['otp'])) {
            $html .= '<h3>Your OTP: <strong>' . $data['otp'] . '</strong></h3>';
        }

        if (!empty($data['reset_link'])) {
            $html .= '<p><a href="' . $data['reset_link'] . '">Reset Password</a></p>';
        }

        if (!empty($data['login_url'])) {
            $html .= '<p><a href="' . $data['login_url'] . '">Login to your account</a></p>';
        }

        $html .= '<p>Thank you,<br>' . ($data['app_name'] ?? APP_NAME) . ' Team</p>';
        $html .= '</body></html>';

        return $html;
    }

    /**
     * Set from address
     * 
     * @param string $address
     * @param string $name
     * @return void
     */
    public function setFrom(string $address, string $name = ''): void
    {
        $this->mailer->setFrom($address, $name);
    }

    /**
     * Check if mail is configured
     * 
     * @return bool
     */
    public function isConfigured(): bool
    {
        return $this->configured;
    }
}