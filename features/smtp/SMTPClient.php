<?php

/**
 * Description of SMTPClient
 * @author coder
 *
 * Created on: Apr 2, 2024 at 12:59:02 PM
 */

namespace features\smtp {

    use features\exceptions\client\BadRequestException;
    use features\smtp\values\Email;
    use features\utils\Duration;
    use features\utils\File;

    final class SMTPClient
    {

        private ?string $body = null, $subject = null;
        private ?string $password = null, $token = null;
        private readonly Email $from;
        private readonly string $boundary, $host;
        private readonly int $port, $retries;
        private readonly Duration $timeout;
        private ?Email $replyTo = null;
        private SMTPSecurity $security;
        private array $files = [], $headers = [];
        private array $toList = [], $ccList = [], $bccList = [];
        private ?string $username = null;

        public function __construct(string $host, int $port, Duration $timeout = null, int $retries = 3)
        {
            $this->host = $host;
            $this->port = $port;
            $this->retries = $retries;
            $this->timeout = $timeout ?? Duration::ofSeconds(15);
            $this->security = new SMTPSecurity(SMTPSecurityType::NONE);
            $this->headers = ['MIME-Version' => '1.0', 'Date' => gmdate('r')];
            $this->boundary = hrtime(true) . substr(md5(random_bytes(9)), 0, 12);
        }

        /**
         * Choose security mechanism to use during email transportation
         * @param SMTPSecurity $security Security mechanism
         * @return self
         */
        public function security(SMTPSecurity $security): self
        {
            $this->security = $security;
            return $this;
        }

        /**
         * Set authorization token if you are using bearer token authorization
         * @param string $token Authorization token
         * @return self
         */
        public function authToken(string $token): self
        {
            $this->token = $token;
            return $this;
        }

        /**
         * Set e-mail to reply to
         * @param Email $email Reply-to e-mail
         * @return self
         */
        public function replyTo(Email $email): self
        {
            $this->replyTo = $email;
            return $this;
        }

        /**
         * Set authentication credentials if you are authenticate using username and password
         * @param string $username Username to be used in authentication
         * @param string $password Password to be used in authentication
         * @return self
         */
        public function login(string $username, string $password): self
        {
            $this->username = $username;
            $this->password = $password;
            return $this;
        }

        /**
         * Set e-mail sender
         * @param Email $email sender e-mail
         * @return self
         */
        public function from(Email $email): self
        {
            $this->from = $email;
            return $this;
        }

        /**
         * Set e-mail recipient
         * @param Email $emails Recipient e-mail
         * @return self
         */
        public function to(Email ...$emails): self
        {
            foreach ($emails as $email) {
                if (!isset($this->toList[$email->value])) {
                    $this->toList[$email->value] = $email;
                }
            }
            return $this;
        }

        /**
         * Set CC (Carbon Copy) to e-mail
         * @param Email $emails Recipient e-mail
         * @return self
         */
        public function cc(Email ...$emails): self
        {
            foreach ($emails as $email) {
                if (!isset($this->ccList[$email->value])) {
                    $this->ccList[$email->value] = $email;
                }
            }
            return $this;
        }

        /**
         * Set BCC (Blind Carbon Copy) to e-mail
         * @param Email $emails Recipient e-mail
         * @return self
         */
        public function bcc(Email ...$emails): self
        {
            foreach ($emails as $email) {
                if (!isset($this->bccList[$email->value])) {
                    $this->bccList[$email->value] = $email;
                }
            }
            return $this;
        }

        /**
         * Set e-mail subject
         * @param string $content E-mail subject
         * @return self
         */
        public function subject(string $content): self
        {
            $this->subject = $content;
            return $this;
        }

        /**
         * Add a file as attachment to e-mail
         * @param File $file A file object to be send as attachment
         * @return self
         */
        public function attachments(File ...$files): self
        {
            foreach ($files as $file) {
                $this->files[md5($file->path)] = $file;
            }
            return $this;
        }

        /**
         * Set e-mail message headers. This headers must follow the HTTP header
         * standards
         * @param string|array $headers Header(s) to send. If is a string then $val must be set.
         * @param string|null $val Header value if $headres is string.
         * @return self
         */
        public function setHeaders(string|array $headers, ?string $val = null): self
        {
            if (is_array($headers)) {
                foreach ($headers as $key => $value) {
                    $this->headers[strtolower(trim($key))] = $value;
                }
            } else {
                $this->headers[strtolower(trim($headers))] = $val;
            }
            return $this;
        }

        /**
         * Set the message body for an email.
         * @param string|null $template If provided then the body will rendered
         * from template
         * @param mixed $data The data to send. These data will be available to
         * then template as <code>$data</code>
         * @return self
         */
        public function setContent(?string $template, mixed $data = null): self
        {
            if ($template !== null) {
                ob_start();
                require $template;
                $this->body = ob_get_clean();
            } else {
                $this->body = $data;
            }
            return $this;
        }

        /**
         * Send e-mail to destination(s)
         * @return void
         */
        public function send(): void
        {
            $conn = new SMTPConnection($this->host, $this->port, $this->security, $this->timeout, $this->retries);
            $success = $conn->initialize($this->username ?? $this->from->value, $this->password, $this->token);
            if (!$success) {
                return;
            }
            if (empty($this->toList)) {
                throw new BadRequestException('At least one recipient field (To) is required.');
            }
            $recipients = array_merge($this->toList, $this->ccList, $this->bccList);
            $conn->setRecipients(array_keys($recipients));
            $socket = $conn->getSocket();
            $this->sendHeader($socket)->sendBody($socket)->sendAttachments($socket);
            fwrite($socket, '--' . $this->boundary . '--' . SMTPConnection::EOL);
            $conn->quit();
        }

        private function sendHeader(&$socket): self
        {
            $content = 'From: ' . $this->from . SMTPConnection::EOL;
            if (!empty($this->toList)) {
                $content .= 'To: ' . implode(', ', $this->toList) . SMTPConnection::EOL;
            }
            if (!empty($this->replyTo)) {
                $content .= 'Reply-To: ' . $this->replyTo . SMTPConnection::EOL;
            }
            if (!empty($this->ccList)) {
                $content .= 'Cc: ' . implode(', ', $this->ccList) . SMTPConnection::EOL;
            }
            $content .= 'Subject: ' . $this->subject . SMTPConnection::EOL;
            $this->headers['Message-ID'] = '<' . uniqid() . '@' . $this->from->domain . '>';
            foreach ($this->headers as $key => $value) {
                $content .= ucwords($key, '-') . ': ' . $value . SMTPConnection::EOL;
            }
            $content .= 'Content-Type: multipart/mixed; boundary="' . $this->boundary . '"';
            fwrite($socket, $content . SMTPConnection::EOL . SMTPConnection::EOL);
            return $this;
        }

        private function sendBody(&$socket): self
        {
            if ($this->body !== null) {
                $content = '--' . $this->boundary . SMTPConnection::EOL;
                $content .= 'Content-Type: text/html; charset=utf-8' . SMTPConnection::EOL;
                $content .= 'Content-Transfer-Encoding: base64' . SMTPConnection::EOL . SMTPConnection::EOL;
                $content .= chunk_split(base64_encode($this->body));
                fwrite($socket, $content);
            }
            return $this;
        }

        private function sendAttachments(&$socket): self
        {
            foreach ($this->files as $file) {
                $content = '--' . $this->boundary . SMTPConnection::EOL;
                $content .= 'Content-Type: ' . $file->type . '; name="' . $file->name . '"' . SMTPConnection::EOL;
                $content .= 'Content-Transfer-Encoding: base64' . SMTPConnection::EOL;
                $content .= 'Content-Disposition: attachment; filename="' . $file->name . '"' . SMTPConnection::EOL;
                fwrite($socket, $content . SMTPConnection::EOL);
                self::copyFile($file, $socket);
            }
            return $this;
        }

        private static function copyFile(File $file, &$dst): void
        {
            $src = fopen($file->path, 'rb');
            stream_filter_append($src, 'convert.base64-encode', STREAM_FILTER_READ, [
                'line-length' => 76,
                'line-break' => SMTPConnection::EOL
            ]);
            stream_copy_to_stream($src, $dst);
            fwrite($dst, SMTPConnection::EOL);
            fclose($src);
        }
    }

}
