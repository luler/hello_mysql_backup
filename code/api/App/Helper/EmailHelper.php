<?php

namespace App\Helper;

use PHPMailer\PHPMailer\PHPMailer;

class EmailHelper
{
    private static $instance;

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * 发送邮件
     * @param $emails //发送的邮件数组，格式：['1207032539@qq.com']
     * @param $subject //发送标题
     * @param $body //发送的主题内容
     * @param $attachs ,格式[['file_url'=>'','title'=>'']]
     * @author 我只想看看蓝天 <1207032539@qq.com>
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function sendEmail(array $emails, string $subject, string $body, array $attachs = [])
    {
        if (empty($emails)) {
            return;
        }
        $mail = new PHPMailer(true);
        $config = ConfigHelper::getEnv('email');
        $config['smtp_host'] = $config['smtp_host'] ?? '';
        $config['smtp_port'] = $config['smtp_port'] ?? '';
        $config['smtp_email'] = $config['smtp_email'] ?? '';
        $config['smtp_password'] = $config['smtp_password'] ?? '';
        $config['smtp_nickname'] = $config['smtp_nickname'] ?? '';

        $mail->isSMTP();
        $mail->Host = $config['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['smtp_email'];
        $mail->Password = $config['smtp_password'];
        $mail->SMTPSecure = 'ssl';
        $mail->Timeout = 30;
        $mail->Port = $config['smtp_port'];
        //收件人
        $mail->setFrom($config['smtp_email'], $config['smtp_nickname']);
        foreach ($emails as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $mail->addAddress($email);     // Add a recipient，Name is optional
        }
        $mail->addReplyTo($config['smtp_email'], $config['smtp_nickname']);
        //附件
        foreach ($attachs as $attach) {
            $mail->addStringAttachment(@file_get_contents($attach['file_url']), $attach['title']);         // Add attachments
        }
        //内容
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = '为了查看该邮件，请切换到支持 HTML 的邮件客户端';
        //发送
        $mail->send();
    }
}
