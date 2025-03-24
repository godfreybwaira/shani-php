<?php

/**
 * SMTPConnection class
 * @author coder
 *
 * Created on: Apr 2, 2024 at 12:59:02 PM
 */

namespace lib\client {

    final class SMTPConnection
    {

        private $socket;
        private bool $secure;
        private string $host;
        private ?int $errorCode;
        private ?string $lastReply = null, $errorMsg;

        private const FLAGS = STREAM_CLIENT_ASYNC_CONNECT | STREAM_CLIENT_PERSISTENT;
        private const STATUS_CODE_LENGTH = 4, EOL = "\r\n";

        /**
         * Creating SMTP connection to remote host
         * @param string $host Remote host address
         * @param string|null $security SSL or TLS security
         * @param int $retries Number of retries before failing
         * @param int $timeout Timeout before failing
         */
        public function __construct(string $host, ?string $security, int $retries, int $timeout)
        {
            $count = 0;
            $socket = $errorCode = $errorMsg = null;
            while ($count < $retries) {
                $socket = stream_socket_client($host, $errorCode, $errorMsg, $timeout, self::FLAGS);
                if (is_resource($socket)) {
                    $this->errorCode = $errorCode;
                    $this->errorMsg = $errorMsg;
                    $this->socket = $socket;
                    $this->host = $host;
                    $this->secure = $security !== null;
                    break;
                }
                \lib\Concurrency::sleep(++$count);
            }
        }

        private function enableTLS(): bool
        {
            if ($this->sendCommand('STARTTLS', 220)) {
                $result = stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                return $result === true && $this->sayHello();
            }
            return false;
        }

        /**
         * Get SMTP socket
         * @return type An SMTP resource
         */
        public function getSocket()
        {
            return $this->socket;
        }

        private function sayHello(): bool
        {
            if ($this->sendCommand('EHLO ' . $this->host, 250)) {
                return true;
            }
            return $this->sendCommand('HELO ' . $this->host, 250);
        }

        private function login(string $uname, ?string $password, ?string $token): bool
        {
            if ($token !== null) {
                $oauth = base64_encode("user=$uname\001auth=Bearer $token\001\001");
                return $this->sendCommand('AUTH XOAUTH2 ' . $oauth, 235);
            }
            if ($password === null) {
                return true;
            }
            if ($this->sendCommand('AUTH CRAM-MD5', 334)) {
                $challenge = base64_decode(substr($this->lastReply, self::STATUS_CODE_LENGTH));
                $result = $uname . ' ' . hash_hmac('md5', $challenge, $password);
                return $this->sendCommand(base64_encode($result), 235);
            }
            if ($this->sendCommand('AUTH LOGIN', 334)) {
                return $this->sendCommand(base64_encode($uname), 334) && $this->sendCommand(base64_encode($password), 235);
            }
            return $this->sendCommand('AUTH PLAIN ' . base64_encode("\0" . $uname . "\0" . $password), 235);
        }

        /**
         * Initialize SMTP session
         * @param string $uname Sender username
         * @param string|null $password Sender password
         * @param string|null $token Authorization token
         * @return bool True on success, false otherwise.
         */
        public function initialize(string $uname, ?string $password, ?string $token): bool
        {
            if (!$this->sayHello() || $this->secure && !$this->enableTLS()) {
                return false;
            }
            if ($this->login($uname, $password, $token)) {
                return $this->sendCommand('MAIL FROM:<' . $uname . '>', 250);
            }
            return false;
        }

        /**
         * Set e-mail receipient(s)
         * @param array $receipients Emails of receipient(s)
         * @return self
         */
        public function setReceipients(array $receipients): self
        {
            foreach ($receipients as $email) {
                $this->sendCommand('RCPT TO:<' . $email . '>', 250);
            }
            $this->sendCommand('DATA', 354);
            return $this;
        }

        /**
         * Close SMTP session
         * @return void
         */
        public function quit(): void
        {
            $this->sendCommand('.', 250);
            $this->sendCommand('QUIT', 221);
        }

        private function sendCommand(string $command, int $expectedCodes): bool
        {
            if (!$this->connected()) {
                return false;
            }
            if (fwrite($this->socket, $command . self::EOL) !== false) {
                $line = $this->getReply();
                if ($line === null) {
                    return false;
                }
                $len = strlen((string) $expectedCodes);
                $code = (int) substr($line, 0, $len);
                if ($code === $expectedCodes) {
                    return true;
                }
                $this->errorCode = $code;
                $this->errorMsg = substr($line, $len + 1);
            }
            return true;
        }

        public function __destruct()
        {
            if (is_resource($this->socket)) {
                fclose($this->socket);
            }
        }

        private function connected(): bool
        {
            if (is_resource($this->socket)) {
                $status = stream_get_meta_data($this->socket);
                if (!$status['eof'] && !$status['timed_out']) {
                    return true;
                }
                fclose($this->socket);
            }
            return false;
        }

        private function getReply(): ?string
        {
            while ($line = fgets($this->socket)) {
                $str = trim($line);
                if (!empty($str)) {
                    $this->lastReply = $str;
                }
            }
            return $this->lastReply;
        }

        /**
         * Get last error code
         * @return int|null
         */
        public function errorCode(): ?int
        {
            return $this->errorCode;
        }

        /**
         * Get last error message
         * @return string|null
         */
        public function errorMessage(): ?string
        {
            return $this->errorMsg;
        }
    }

}
