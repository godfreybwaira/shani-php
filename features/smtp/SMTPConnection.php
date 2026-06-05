<?php

/**
 * SMTPConnection class
 * @author coder
 *
 * @since Apr 2, 2024 at 12:59:02 PM
 */

namespace features\smtp {

    use features\utils\Duration;
    use features\utils\URI;

    final class SMTPConnection
    {

        private $socket;
        private ?int $errorCode;
        private readonly URI $host;
        private readonly SMTPSecurity $security;
        private ?string $lastReply = null, $errorMsg;

        private const STATUS_CODE_LENGTH = 4;

        /**
         * End of line
         */
        public const EOL = "\r\n";

        /**
         * Creating SMTP connection to remote host
         * @param string $host Remote host address
         * @param string $port Remote host port
         * @param SMTPSecurity $security SMTP security
         * @param Duration $timeout Timeout before failing
         * @param int $retries Number of retries before failing
         */
        public function __construct(string $host, int $port, SMTPSecurity $security, Duration $timeout, int $retries)
        {
            $count = 0;
            $socket = null;
            $this->security = $security;
            $this->host = new URI($security->getProtocol() . $host . ':' . $port);
            $flags = STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT;
            $context = $security->getContext();
            while ($count < $retries) {
                $socket = stream_socket_client($this->host->asString(), $this->errorCode, $this->errorMsg, $timeout->fromNow(), $flags, $context);
                if (is_resource($socket)) {
                    $this->socket = $socket;
                    stream_set_timeout($this->socket, $timeout->fromNow());
                    break;
                }
                $count++;
                usleep($count * 90_000);
            }
            if ($this->socket === null) {
                throw new \RuntimeException('Could not connect to ' . $this->host . ', reason: ' . $this->errorMsg ?? 'unknown');
            }
        }

        private function enableTLS(): bool
        {
            if ($this->sendCommand('STARTTLS', 220)) {
                $cryptoMethod = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
                $result = stream_socket_enable_crypto($this->socket, true, $cryptoMethod);
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
            if ($this->sendCommand('EHLO ' . $this->host->hostname(), 250)) {
                return true;
            }
            return $this->sendCommand('HELO ' . $this->host->hostname(), 250);
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
            // Read initial connection greeting banner (e.g., 220 Ready)
            if ($this->getReply() === null) {
                return false;
            }

            if (!$this->sayHello() || $this->security->type === SMTPSecurityType::TLS && !$this->enableTLS()) {
                return false;
            }
            return $this->login($uname, $password, $token);
        }

        /**
         * Initiates a clean mail transaction envelope.
         * @param string $from Sender email address.
         * @return bool
         */
        public function startEnvelope(string $from): bool
        {
            return $this->sendCommand('MAIL FROM:<' . $from . '>', 250);
        }

        /**
         * Set e-mail recipient(s)
         * @param string $recipients Emails of recipient(s)
         * @return self
         */
        public function setRecipients(string ...$recipients): self
        {
            foreach ($recipients as $email) {
                $this->sendCommand('RCPT TO:<' . $email . '>', 250);
            }
            return $this;
        }

        /**
         * Puts the server into intermediate stream DATA transmission mode.
         * @return bool
         */
        public function startData(): bool
        {
            return $this->sendCommand('DATA', 354);
        }

        /**
         * Close SMTP session
         * @param bool $commit Whether to end raw body transmission segment.
         * @return void
         */
        public function quit(bool $commit): void
        {
            if ($commit) {
                $this->commit();
            }
            $this->sendCommand('QUIT', 221);
        }

        /**
         * Ends the raw body payload data transmission segment.
         * * @return bool
         */
        public function commit(): bool
        {
            return $this->sendCommand('.', 250);
        }

        /**
         * Resets the current SMTP server session buffer without dropping the TCP connection.
         * This clears out the previous MAIL, RCPT, and DATA states.
         * * @return bool True if the server accepted the reset command.
         */
        public function reset(): bool
        {
            return $this->sendCommand('RSET', 250);
        }

        /**
         * Sends an SMTP raw command string, captures response, and enforces valid return checks
         * @return bool True if command executed successfully, false otherwise.
         */
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
            return false;
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

        /**
         * Consumes the network stream completely, managing multi-line SMTP responses safely
         *
         * @return string|null Last reply or null of none
         */
        private function getReply(): ?string
        {
            while (($line = fgets($this->socket)) !== false) {
                $str = trim($line);
                if (empty($str)) {
                    continue;
                }
                $this->lastReply = $str;
                // If the 4th character is a space (e.g. "250 "), it means it's the final line
                if (isset($line[3]) && $line[3] === ' ') {
                    break;
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
