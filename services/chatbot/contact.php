<?php
$to = 'info@liens-web.com';

$type_labels = [
  'trial' => '無料トライアル',
  'document' => '資料請求',
  'partner' => '紹介パートナー',
  'other' => 'その他',
];

$type = isset($_POST['type']) ? $_POST['type'] : '';
$company = isset($_POST['company']) ? $_POST['company'] : '';
$name = isset($_POST['name']) ? $_POST['name'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$phone = isset($_POST['phone']) ? $_POST['phone'] : '';
$message = isset($_POST['message']) ? $_POST['message'] : '';

$type_label = isset($type_labels[$type]) ? $type_labels[$type] : $type;

$subject = "LIENS BOT {$type_label} - {$name}";

$body = "LIENS BOT お問い合わせ通知\n\n";
$body .= "種別: {$type_label}\n";
$body .= "会社名: {$company}\n";
$body .= "お名前: {$name}\n";
$body .= "メール: {$email}\n";
$body .= "電話番号: {$phone}\n";
$body .= "お問い合わせ内容:\n{$message}\n\n";
$body .= "送信日時: " . date('Y-m-d H:i:s') . "\n";

// SMTP認証で直接Gmailに送信
$smtp_host = 'smtp.gmail.com';
$smtp_port = 587;
$smtp_user = 'info@liens-web.com';
$smtp_pass = 'yhrtsgawmrhzlddr';

$socket = @fsockopen($smtp_host, $smtp_port, $errno, $errstr, 10);
if ($socket) {
    fgets($socket, 512); // greeting

    fwrite($socket, "EHLO liens-web.com\r\n");
    while ($line = fgets($socket, 512)) { if (substr($line, 3, 1) === ' ') break; }

    fwrite($socket, "STARTTLS\r\n");
    fgets($socket, 512);
    stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT);

    fwrite($socket, "EHLO liens-web.com\r\n");
    while ($line = fgets($socket, 512)) { if (substr($line, 3, 1) === ' ') break; }

    fwrite($socket, "AUTH LOGIN\r\n");
    fgets($socket, 512);
    fwrite($socket, base64_encode($smtp_user) . "\r\n");
    fgets($socket, 512);
    fwrite($socket, base64_encode($smtp_pass) . "\r\n");
    $auth = fgets($socket, 512);

    if (substr($auth, 0, 3) === '235') {
        fwrite($socket, "MAIL FROM:<info@liens-web.com>\r\n");
        fgets($socket, 512);
        fwrite($socket, "RCPT TO:<{$to}>\r\n");
        fgets($socket, 512);
        fwrite($socket, "DATA\r\n");
        fgets($socket, 512);

        $msg = "From: LIENS BOT <info@liens-web.com>\r\n";
        $msg .= "To: {$to}\r\n";
        $msg .= "Subject: {$subject}\r\n";
        $msg .= "Reply-To: {$email}\r\n";
        $msg .= "MIME-Version: 1.0\r\n";
        $msg .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $msg .= "Content-Transfer-Encoding: 8bit\r\n";
        $msg .= "\r\n";
        $msg .= $body . "\r\n";
        $msg .= ".\r\n";

        fwrite($socket, $msg);
        $result = fgets($socket, 512);

        // 送信成功
    } else {
        // 認証失敗
    }

    fwrite($socket, "QUIT\r\n");
    fclose($socket);
} else {
    // SMTP接続失敗
}

header('Location: thanks.html');
exit;
