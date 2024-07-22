<?php

/**
 * Description of SMTP
 * @author coder
 *
 * Created on: Apr 2, 2024 at 12:59:02 PM
 */

namespace library\client {

    use shani\engine\core\Definitions;

    final class SMTP
    {

        private ?string $body = null, $subject = null, $from = null, $replyTo = null;
        private ?string $password = null, $headerLine = null, $token = null;
        private array $files = [], $headers = [], $rcpt = [];
        private SMTPConnection $conn;
        private string $boundary, $host;
        private int $retries, $timeout;
        private ?string $security = null;

        private const EOL = "\r\n", SECURITY_TLS = 'tls', SECURITY_SSL = 'ssl';

        public function __construct(string $host, int $retries = 3, int $timeout = 500)
        {
            $this->boundary = hrtime(true) . substr(md5(random_bytes(9)), 0, 12);
            $this->host = $host;
            $this->retries = $retries;
            $this->timeout = $timeout;
            $this->headers = [
                'MIME-Version' => '1.0', 'Date' => date('r'),
                'Message-ID' => '<' . $this->boundary . '>'
            ];
        }

        /**
         * Choose security mechanism to use when transporting e-mail
         * @param string $security Security mechanism, either 'tls' or 'ssl' while
         * 'ssl' being the default.
         * @return self
         * @throws \InvalidArgumentException
         */
        public function security(string $security = self::SECURITY_TLS): self
        {
            if ($security === self::SECURITY_SSL || $security === self::SECURITY_TLS) {
                $this->security = $security;
                return $this;
            }
            throw new \InvalidArgumentException('Invalid security option');
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
         * @param string $email Reply-to e-mail
         * @param string $name Reply-to name
         * @return self
         */
        public function replyTo(string $email, string $name = null): self
        {
            if ($this->replyTo === null && self::validEmail($email)) {
                $this->replyTo = $email;
                $this->headerLine .= 'Reply-To: ' . $name . '<' . $email . '>' . self::EOL;
            }
            return $this;
        }

        /**
         * Set authentication password if you are authenticate using username and password
         * @param string $password Password to be used in authentication
         * @return self
         */
        public function auth(string $password): self
        {
            $this->password = $password;
            return $this;
        }

        private static function validEmail(string $email): bool
        {
            if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
                return true;
            }
            throw new \InvalidArgumentException('Invalid email address ' . $email);
        }

        /**
         * Set e-mail sender
         * @param string $email sender e-mail
         * @param string $name sender name
         * @return self
         */
        public function from(string $email, string $name = null): self
        {
            if ($this->from === null && self::validEmail($email)) {
                $this->from = $email;
                $this->headerLine .= 'From: ' . $name . '<' . $this->from . '>' . self::EOL;
            }
            return $this;
        }

        /**
         * Set e-mail receipient
         * @param string $email Receipient e-mail
         * @param string $name Receipient name
         * @return self
         */
        public function to(string $email, string $name = null): self
        {
            return $this->addRcpt($email, $name, 'To');
        }

        /**
         * Set CC (Carbon Copy) to e-mail
         * @param string $email Receipient e-mail
         * @param string $name Receipient name
         * @return self
         */
        public function cc(string $email, string $name = null): self
        {
            return $this->addRcpt($email, $name, 'Cc');
        }

        /**
         * Set BCC (Blind Carbon Copy) to e-mail
         * @param string $email Receipient e-mail
         * @param string $name Receipient name
         * @return self
         */
        public function bcc(string $email, string $name = null): self
        {
            return $this->addRcpt($email, $name);
        }

        private function addRcpt(string $email, string $name = null, string $type = null): self
        {
            if (!in_array($email, $this->rcpt) && self::validEmail($email)) {
                $this->rcpt[] = $email;
                if ($type !== null) {
                    $this->headerLine .= $type . ': ' . $name . '<' . $email . '>' . self::EOL;
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
            $this->subject = chunk_split('Subject: ' . $content);
            return $this;
        }

        /**
         * Add path to file as attachment to e-mail
         * @param string $path Path to a valid file to be send as attachment
         * @param string $filename Name of a file as it will appear to recepient.
         * @param string $mime File mime type. If not provided file mime will be used instead
         * @return self
         */
        public function attachment(string $path, string $filename = null, string $mime = null): self
        {
            $mime ??= \library\Mime::fromFilename($path) ?? 'application/octet-stream';
            $this->files[md5_file($path)] = ['path' => $path, 'mime' => $mime, 'name' => $filename];
            return $this;
        }

        /**
         * Set e-mail message headers. This headers must follow the HTTP header
         * standards
         * @param type $headers Header(s) to send, can be a string or array. If
         * is a string then $val must be set.
         * @param type $val Header value if $headres is string.
         * @return self
         */
        public function setHeaders($headers, $val = null): self
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
         * @param type $data The data to send. This data will be available to template
         * if the template was set.
         * @return self
         */
        public function setBody(?string $template, $data = null): self
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
         * @param callable $callback A callback for error handling. The first argument
         * of this callback is error code and the second is the error message.
         * i.e $callback(int $errorCode, string $errorMessage)
         * @return void
         */
        public function send(callable $callback = null): void
        {
            \library\Concurrency::async(function () use (&$callback) {
                $this->conn = new SMTPConnection($this->host, $this->security, $this->retries, $this->timeout);
                $success = $this->conn->initialize($this->from, $this->password, $this->token);
                if ($success) {
                    $socket = $this->conn->getSocket();
                    $this->conn->setReceipients($this->rcpt);
                    $this->createHeader($socket)->createBody($socket)->createAttachments($socket);
                    fwrite($socket, '--' . $this->boundary . '--' . self::EOL);
                    $this->conn->quit();
                }
                if ($callback !== null) {
                    $callback($this->conn->errorCode(), $this->conn->errorMessage());
                }
            });
        }

        private function createHeader(&$socket): self
        {
            $content = $this->headerLine . $this->subject;
            foreach ($this->headers as $key => $value) {
                $content .= ucwords($key, '-') . ': ' . $value . self::EOL;
            }
            $content .= 'Content-Type: multipart/mixed; boundary="' . $this->boundary . '"';
            fwrite($socket, $content . self::EOL . self::EOL);
            return $this;
        }

        private function createBody(&$socket): self
        {
            if ($this->body !== null) {
                $content = '--' . $this->boundary . self::EOL;
                $content .= 'Content-Type: text/html; charset=utf-8' . self::EOL;
                $content .= 'Content-Transfer-Encoding: base64' . self::EOL . self::EOL;
                $content .= chunk_split(base64_encode($this->body));
                fwrite($socket, $content);
            }
            return $this;
        }

        private function createAttachments(&$socket): self
        {
            foreach ($this->files as $file) {
                $name = $file['name'] ?? basename($file['path']);
                $src = fopen($file['path'], 'rb');
                $content = '--' . $this->boundary . self::EOL;
                $content .= 'Content-Type: ' . $file['mime'] . '; ' . chunk_split('name="' . $name . '"');
                $content .= 'Content-Transfer-Encoding: base64' . self::EOL;
                $content .= 'Content-Length: ' . fstat($src)['size'] . self::EOL;
                $content .= 'Content-Disposition: attachment; ' . chunk_split('filename="' . $name . '"');
                fwrite($socket, $content . self::EOL);
                self::copyFile($src, $socket);
                fclose($src);
            }
            return $this;
        }

        private static function copyFile(&$src, &$dst): void
        {
            stream_filter_append($src, 'convert.base64-encode');
            while (!feof($src)) {
                fwrite($dst, chunk_split(fread($src, Definitions::BUFFER_SIZE)));
            }
        }
    }

}
