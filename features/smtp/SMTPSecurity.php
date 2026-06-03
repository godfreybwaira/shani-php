<?php

namespace features\smtp {

    /**
     * Class SMTPSecurity
     * Manages cryptographic configurations and peer validation policies
     * for secure SMTP stream connections.
     *
     * @author goddy
     * Created on: Jun 3, 2026 at 11:51:04 AM
     */
    final class SMTPSecurity
    {

        /**
         * @var SMTPSecurityType The type of security mechanism chosen (SSL, TLS, NONE).
         */
        public readonly SMTPSecurityType $type;

        /**
         * @var string|null Path to a local Certificate Authority (CA) bundle file.
         */
        private ?string $certificateFile = null;

        /**
         * @var bool Require verification of SSL/TLS peer certificate.
         */
        private bool $verifyPeer = false;

        /**
         * @var bool Require verification of peer name against the certificate common name.
         */
        private bool $verifyPeerName = false;

        /**
         * @var bool Allow self-signed certificates in the security chain.
         */
        private bool $allowSelfSigned = true;

        /**
         * SMTPSecurity constructor.
         * @param SMTPSecurityType $type Cryptographic security mechanism type.
         */
        public function __construct(SMTPSecurityType $type)
        {
            $this->type = $type;
        }

        /**
         * Resolves the corresponding PHP stream socket transport wrapper prefix string.
         * @return string Stream protocol transport schema (e.g., 'ssl://', 'tls://', 'tcp://').
         */
        public function getProtocol(): string
        {
            return match ($this->type) {
                SMTPSecurityType::SSL => 'ssl://',
                SMTPSecurityType::TLS => 'tls://',
                default => 'tcp://'
            };
        }

        /**
         * Gets the path to the configured Certificate Authority bundle file.
         * @return string|null Path string if defined, or null.
         */
        public function getCertificateFile(): ?string
        {
            return $this->certificateFile;
        }

        /**
         * Checks whether peer certificate verification is strictly enforced.
         * @return bool True if peer verification is active.
         */
        public function getVerifyPeer(): bool
        {
            return $this->verifyPeer;
        }

        /**
         * Checks whether hostname verification against the peer certificate is enforced.
         * @return bool True if peer name verification is active.
         */
        public function getVerifyPeerName(): bool
        {
            return $this->verifyPeerName;
        }

        /**
         * Checks whether self-signed certificates are explicitly permitted.
         * @return bool True if self-signed certificates are allowed.
         */
        public function getAllowSelfSigned(): bool
        {
            return $this->allowSelfSigned;
        }

        /**
         * Sets the path to a local custom Certificate Authority (CA) bundle file.
         * @param string $certificateFile Absolute file path to the CA bundle.
         * @return self Fluent interface pattern.
         */
        public function setCertificateFile(string $certificateFile): self
        {
            $this->certificateFile = $certificateFile;
            return $this;
        }

        /**
         * Sets whether to require verification of SSL/TLS certificate peer chains.
         * @param bool $verifyPeer Enforce strict certificate verification state.
         * @return self Fluent interface pattern.
         */
        public function setVerifyPeer(bool $verifyPeer): self
        {
            $this->verifyPeer = $verifyPeer;
            return $this;
        }

        /**
         * Sets whether to match the remote socket host string against the certificate CN/SAN.
         * @param bool $verifyPeerName Enforce strict peer hostname matching state.
         * @return self Fluent interface pattern.
         */
        public function setVerifyPeerName(bool $verifyPeerName): self
        {
            $this->verifyPeerName = $verifyPeerName;
            return $this;
        }

        /**
         * Configures fallback tolerance behavior regarding self-signed certificate entities.
         * @param bool $allowSelfSigned True to permit self-signed certificates.
         * @return self Fluent interface pattern.
         */
        public function setAllowSelfSigned(bool $allowSelfSigned): self
        {
            $this->allowSelfSigned = $allowSelfSigned;
            return $this;
        }

        /**
         * Generates a configured PHP stream context resource for secure OpenSSL handshakes.
         * @return resource|null A stream context resource if security is active, or null.
         */
        public function getContext()
        {
            if ($this->type === SMTPSecurityType::NONE) {
                return null;
            }
            $options = [
                'verify_peer' => $this->verifyPeer,
                'verify_peer_name' => $this->verifyPeerName,
                'allow_self_signed' => $this->allowSelfSigned
            ];
            if ($this->certificateFile !== null) {
                $options['cafile'] = $this->certificateFile;
            }

            return stream_context_create(['ssl' => $options]);
        }
    }

}