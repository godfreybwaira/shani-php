<?php

/**
 * Description of SMTP
 * @author coder
 *
 * Created on: Apr 2, 2024 at 12:59:02 PM
 */

namespace library\client {

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
            $this->boundary = time() . substr(md5(random_bytes(9)), 0, 12);
            $this->host = $host;
            $this->retries = $retries;
            $this->timeout = $timeout;
            $this->headers = [
                'MIME-Version' => '1.0', 'Date' => date('r'),
                'Message-ID' => '<' . $this->boundary . '>'
            ];
        }

        public function security(string $security = self::SECURITY_TLS): self
        {
            if ($security === self::SECURITY_SSL || $security === self::SECURITY_TLS) {
                $this->security = $security;
                return $this;
            }
            throw new \InvalidArgumentException('Invalid security option');
        }

        public function accessToken(string $token): self
        {
            $this->token = $token;
            return $this;
        }

        public function replyTo(string $email, string $name = null): self
        {
            if ($this->replyTo === null && self::validEmail($email)) {
                $this->replyTo = $email;
                $this->headerLine .= 'Reply-To: ' . $name . '<' . $email . '>' . self::EOL;
            }
            return $this;
        }

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

        public function from(string $email, string $name = null): self
        {
            if ($this->from === null && self::validEmail($email)) {
                $this->from = $email;
                $this->headerLine .= 'From: ' . $name . '<' . $this->from . '>' . self::EOL;
            }
            return $this;
        }

        public function uri(): \library\URI
        {
            return new \library\URI($this->host);
        }

        public function to(string $email, string $name = null): self
        {
            return $this->addRcpt($email, $name, 'To');
        }

        public function cc(string $email, string $name = null): self
        {
            return $this->addRcpt($email, $name, 'Cc');
        }

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

        public function subject(string $content): self
        {
            $this->subject = chunk_split('Subject: ' . $content);
            return $this;
        }

        public function attachment(string $path, string $filename = null, string $mime = null): self
        {
            $mime ??= \library\Mime::fromFilename($path) ?? 'application/octet-stream';
            $this->files[md5_file($path)] = ['path' => $path, 'mime' => $mime, 'name' => $filename];
            return $this;
        }

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

        public function send(callable $cb = null): void
        {
            \library\Concurrency::async(function () use (&$cb) {
                $this->conn = SMTPConnection::connect($this->host, $this->security, $this->retries, $this->timeout);
                $success = $this->conn->initialize($this->from, $this->password, $this->token);
                if ($success) {
                    $socket = $this->conn->getSocket();
                    $this->conn->setReceipients($this->rcpt);
                    $this->createHeader($socket)->createBody($socket)->createAttachments($socket);
                    fwrite($socket, '--' . $this->boundary . '--' . self::EOL);
                    $this->conn->quit();
                }
                if ($cb !== null) {
                    $cb($this->conn->errorCode(), $this->conn->errorMessage());
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
                fwrite($dst, chunk_split(fread($src, \library\Utils::BUFFER_SIZE)));
            }
        }
    }

}
