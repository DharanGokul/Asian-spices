<?php
// Contact form handler for Asian Spices with SMTP support

// Set headers for CORS and JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get and sanitize form data
$name = isset($_POST['name']) ? trim(strip_tags($_POST['name'])) : '';
$email = isset($_POST['email']) ? trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)) : '';
$companyName = isset($_POST['companyName']) ? trim(strip_tags($_POST['companyName'])) : '';
$phone = isset($_POST['phone']) ? trim(strip_tags($_POST['phone'])) : '';
$country = isset($_POST['country']) ? trim(strip_tags($_POST['country'])) : '';
$inquiryType = isset($_POST['inquiry-type']) ? trim(strip_tags($_POST['inquiry-type'])) : '';
$message = isset($_POST['message']) ? trim(strip_tags($_POST['message'])) : '';

// Validate required fields
$errors = [];
if (empty($name)) $errors[] = 'Full Name is required';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid Email is required';
if (empty($companyName)) $errors[] = 'Company Name is required';
if (empty($country)) $errors[] = 'Country is required';
if (empty($inquiryType)) $errors[] = 'Inquiry Type is required';

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
    exit;
}

// SMTP Configuration
// Include the SMTP configuration file
$config = include 'smtp-config.php';
$smtpConfig = $config['smtp'];
$adminEmail = $config['email']['admin_email'];
$siteName = $config['email']['site_name'];
$siteUrl = $config['email']['site_url'];

// Include PHPMailer (you'll need to download and include PHPMailer)
// For now, we'll use a basic SMTP implementation
// To use PHPMailer, uncomment the following lines and download PHPMailer:
// require 'vendor/autoload.php'; // If using Composer
// Or include the PHPMailer files manually:
// require 'PHPMailer/src/PHPMailer.php';
// require 'PHPMailer/src/SMTP.php';
// require 'PHPMailer/src/Exception.php';

// Function to send email via SMTP
function sendSMTPEmail($to, $subject, $htmlMessage, $smtpConfig) {
    // Create a boundary for multipart email
    $boundary = md5(time());

    // Email headers
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        'From: ' . $smtpConfig['from_name'] . ' <' . $smtpConfig['from_email'] . '>',
        'Reply-To: ' . $smtpConfig['from_email'],
        'X-Mailer: PHP/' . phpversion()
    ];

    // Plain text version (strip HTML tags)
    $plainMessage = strip_tags($htmlMessage);
    $plainMessage = html_entity_decode($plainMessage, ENT_QUOTES, 'UTF-8');

    // Build the email body
    $body = '--' . $boundary . "\r\n";
    $body .= 'Content-Type: text/plain; charset=UTF-8' . "\r\n";
    $body .= 'Content-Transfer-Encoding: 7bit' . "\r\n\r\n";
    $body .= $plainMessage . "\r\n\r\n";
    $body .= '--' . $boundary . "\r\n";
    $body .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";
    $body .= 'Content-Transfer-Encoding: 7bit' . "\r\n\r\n";
    $body .= $htmlMessage . "\r\n\r\n";
    $body .= '--' . $boundary . '--';

    // SMTP connection
    $smtpHost = $smtpConfig['host'];
    $smtpPort = $smtpConfig['port'];
    $timeout = isset($smtpConfig['timeout']) ? $smtpConfig['timeout'] : 30;

    // Debug logging
    $debug = isset($smtpConfig['debug']) && $smtpConfig['debug'];
    if ($debug) {
        error_log("SMTP Debug: Connecting to $smtpHost:$smtpPort");
    }

    // Create socket connection
    $socket = fsockopen(
        ($smtpConfig['encryption'] === 'ssl' ? 'ssl://' : '') . $smtpHost,
        $smtpPort,
        $errno,
        $errstr,
        $timeout
    );

    if (!$socket) {
        if ($debug) {
            error_log("SMTP Debug: Connection failed - $errstr ($errno)");
        }
        return false;
    }

    // Set timeout for socket operations
    stream_set_timeout($socket, $timeout);

    // SMTP conversation
    $responses = [];

    // Read server greeting
    $responses[] = fgets($socket, 515);
    if ($debug) {
        error_log("SMTP Debug: Server greeting - " . trim(end($responses)));
    }

    // Send EHLO
    fwrite($socket, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
    do {
        $responses[] = fgets($socket, 515);
        if ($debug) {
            error_log("SMTP Debug: EHLO response - " . trim(end($responses)));
        }
    } while (!preg_match('/^250 /', end($responses)));

    // Start TLS if required
    if ($smtpConfig['encryption'] === 'tls') {
        fwrite($socket, "STARTTLS\r\n");
        $responses[] = fgets($socket, 515);
        if ($debug) {
            error_log("SMTP Debug: STARTTLS response - " . trim(end($responses)));
        }

        // Re-establish SSL connection with certificate verification disabled for local development
        // For production, you should verify certificates
        $cryptoMethod = defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT') 
            ? STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT 
            : STREAM_CRYPTO_METHOD_TLS_CLIENT;
        
        // Disable certificate verification for Gmail (local development only)
        stream_context_set_option($socket, 'ssl', 'verify_peer', false);
        stream_context_set_option($socket, 'ssl', 'verify_peer_name', false);
        stream_context_set_option($socket, 'ssl', 'allow_self_signed', true);
        
        if (!stream_socket_enable_crypto($socket, true, $cryptoMethod)) {
            if ($debug) {
                error_log("SMTP Debug: TLS handshake failed");
            }
            fclose($socket);
            return false;
        }

        // Send EHLO again after TLS
        fwrite($socket, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
        do {
            $responses[] = fgets($socket, 515);
            if ($debug) {
                error_log("SMTP Debug: EHLO after TLS - " . trim(end($responses)));
            }
        } while (!preg_match('/^250 /', end($responses)));
    }

    // Authenticate
    fwrite($socket, "AUTH LOGIN\r\n");
    $responses[] = fgets($socket, 515);
    if ($debug) {
        error_log("SMTP Debug: AUTH LOGIN - " . trim(end($responses)));
    }

    fwrite($socket, base64_encode($smtpConfig['username']) . "\r\n");
    $responses[] = fgets($socket, 515);
    if ($debug) {
        error_log("SMTP Debug: Username auth - " . trim(end($responses)));
    }

    fwrite($socket, base64_encode($smtpConfig['password']) . "\r\n");
    $responses[] = fgets($socket, 515);
    if ($debug) {
        error_log("SMTP Debug: Password auth - " . trim(end($responses)));
    }

    // Send MAIL FROM
    fwrite($socket, "MAIL FROM:<" . $smtpConfig['from_email'] . ">\r\n");
    $responses[] = fgets($socket, 515);
    if ($debug) {
        error_log("SMTP Debug: MAIL FROM - " . trim(end($responses)));
    }

    // Send RCPT TO
    fwrite($socket, "RCPT TO:<" . $to . ">\r\n");
    $responses[] = fgets($socket, 515);
    if ($debug) {
        error_log("SMTP Debug: RCPT TO - " . trim(end($responses)));
    }

    // Send DATA
    fwrite($socket, "DATA\r\n");
    $responses[] = fgets($socket, 515);
    if ($debug) {
        error_log("SMTP Debug: DATA command - " . trim(end($responses)));
    }

    // Send email content
    fwrite($socket, "Subject: " . $subject . "\r\n");
    foreach ($headers as $header) {
        fwrite($socket, $header . "\r\n");
    }
    fwrite($socket, "\r\n");
    fwrite($socket, $body . "\r\n");
    fwrite($socket, ".\r\n");
    $responses[] = fgets($socket, 515);
    if ($debug) {
        error_log("SMTP Debug: Email sent - " . trim(end($responses)));
    }

    // Send QUIT
    fwrite($socket, "QUIT\r\n");
    $responses[] = fgets($socket, 515);
    if ($debug) {
        error_log("SMTP Debug: QUIT - " . trim(end($responses)));
    }

    // Close connection
    fclose($socket);

    // Check if email was sent successfully by looking at the email sent response
    // The email sent response should contain "250 2.0.0 OK" after we send the final dot
    $success = false;
    foreach ($responses as $response) {
        if (strpos($response, '250 2.0.0 OK') !== false) {
            $success = true;
            break;
        }
    }
    
    if ($debug) {
        error_log("SMTP Debug: Email sending " . ($success ? 'successful' : 'failed'));
    }

    return $success;
}

// Create admin notification email
$adminSubject = "New Contact Form Submission - $siteName";
$adminMessage = "
<html>
<head>
    <title>New Contact Form Submission</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #D4A017; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .field { margin-bottom: 15px; }
        .label { font-weight: bold; color: #D4A017; }
        .footer { background: #333; color: white; padding: 10px; text-align: center; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>New Contact Form Submission</h2>
            <p>$siteName</p>
        </div>
        <div class='content'>
            <div class='field'>
                <span class='label'>Full Name:</span> $name
            </div>
            <div class='field'>
                <span class='label'>Email:</span> $email
            </div>
            <div class='field'>
                <span class='label'>Company Name:</span> $companyName
            </div>
            <div class='field'>
                <span class='label'>Phone:</span> " . (!empty($phone) ? $phone : 'Not provided') . "
            </div>
            <div class='field'>
                <span class='label'>Country:</span> $country
            </div>
            <div class='field'>
                <span class='label'>Inquiry Type:</span> $inquiryType
            </div>
            <div class='field'>
                <span class='label'>Message:</span><br>
                " . nl2br($message) . "
            </div>
            <div class='field'>
                <span class='label'>Submitted on:</span> " . date('Y-m-d H:i:s') . "
            </div>
        </div>
        <div class='footer'>
            This email was sent from the contact form on $siteName website.
        </div>
    </div>
</body>
</html>";

// Create user confirmation email
$userSubject = "Thank you for contacting $siteName";
$userMessage = "
<html>
<head>
    <title>Thank you for contacting $siteName</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #D4A017; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .footer { background: #333; color: white; padding: 10px; text-align: center; font-size: 12px; }
        .highlight { color: #D4A017; font-weight: bold; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>Thank you for contacting $siteName</h2>
        </div>
        <div class='content'>
            <p>Dear $name,</p>

            <p>Thank you for reaching out to us! We have received your inquiry and appreciate your interest in our products and services.</p>

            <p><strong>Your submission details:</strong></p>
            <ul>
                <li><strong>Company:</strong> $companyName</li>
                <li><strong>Inquiry Type:</strong> $inquiryType</li>
                <li><strong>Country:</strong> $country</li>
            </ul>

            <p>Our dedicated team will review your message and respond within <span class='highlight'>24 hours</span> during business days.</p>

            <p>If you have any urgent questions, please feel free to contact us directly:</p>
            <ul>
                <li><strong>Email:</strong> info@asianspices.co.in</li>
                <li><strong>Sales:</strong> sales@asianspices.co.in</li>
                <li><strong>Phone:</strong> +91-XXXXXXXXXX (Business Hours: Mon-Fri, 9 AM - 6 PM IST)</li>
            </ul>

            <p>We look forward to serving your business needs!</p>

            <p>Best regards,<br>
            <strong>The $siteName Team</strong></p>
        </div>
        <div class='footer'>
            $siteName - Quality and Quantity<br>
            ISO 9001 | FSSC 22000 | BRC | HALAL Certified<br>
            Visit us at: <a href='$siteUrl' style='color: #D4A017;'>$siteUrl</a>
        </div>
    </div>
</body>
</html>";

// Email headers
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=UTF-8\r\n";
$headers .= "From: $siteName <noreply@asianspices.co.in>\r\n";
$headers .= "Reply-To: $email\r\n";

// Send admin notification email
$adminEmailSent = sendSMTPEmail($adminEmail, $adminSubject, $adminMessage, $smtpConfig);

// Send user confirmation email
$userEmailSent = sendSMTPEmail($email, $userSubject, $userMessage, $smtpConfig);

// Check if emails were sent successfully
if ($adminEmailSent && $userEmailSent) {
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for contacting us! We have sent you a confirmation email.'
    ]);
} else {
    // Log the error for debugging (in production, use proper logging)
    error_log("SMTP Email sending failed. Admin email sent: $adminEmailSent, User email sent: $userEmailSent");

    echo json_encode([
        'success' => false,
        'message' => 'There was an error sending your message. Please try again or contact us directly.'
    ]);
}
?>