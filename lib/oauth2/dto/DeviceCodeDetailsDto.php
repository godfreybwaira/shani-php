<?php

/**
 * Used for the Device Authorization Grant (smart TVs, CLI tools, etc.).
 * @author goddy
 *
 * Created on: Mar 11, 2026 at 12:22:18 PM
 */

namespace lib\oauth2\dto {

    final class DeviceCodeDetailsDto
    {

        /**
         * Long device code used for polling
         * @var string
         */
        public readonly string $deviceCode;

        /**
         * @var string Short user-friendly code displayed to the user
         */
        public readonly string $userCode;

        /**
         * @var string Client that requested the device code
         */
        public readonly string $clientId;

        /**
         * @var string|null Requested scopes
         */
        public readonly ?string $scope;

        /**
         * Number of seconds before expiration
         * @var int
         */
        public readonly int $expiresIn;

        /**
         * @var int Recommended polling interval in seconds
         */
        public readonly int $pollingInterval;

        /**
         * @var int|null User ID after authorization (null = still pending)
         */
        public readonly ?string $userId;

        /**
         * @var string Authorization status (PENDING or OK)
         */
        public readonly string $status;

        /**
         * Check if the code is expires. This is true if <code>$expiresIn</code>
         * is less or equals to zero
         * @var bool
         */
        public readonly bool $expired;

        /**
         * The end-user verification URI on the authorization server
         * @var string
         */
        public readonly string $verificationUri;

        /**
         *
         * @param string    $clientId           Requesting client.
         * @param string    $deviceCode         Long opaque device code for polling.
         * @param string    $userCode           Short user code shown on TV/CLI.
         * @param string    $verificationUri    The end-user verification URI on the authorization server
         * @param int|null  $userId             User ID once authorized (null = pending).
         * @param string|null   $scope          Requested scopes.
         * @param int   $expiresIn              Number of seconds before expiration.
         * @param int   $pollingInterval        Recommended polling interval.
         */
        public function __construct(string $clientId, string $deviceCode, string $userCode, string $verificationUri, ?string $userId, ?string $scope, int $expiresIn, int $pollingInterval)
        {
            $this->deviceCode = $deviceCode;
            $this->userCode = $userCode;
            $this->clientId = $clientId;
            $this->scope = $scope;
            $this->expiresIn = $expiresIn;
            $this->pollingInterval = $pollingInterval;
            $this->verificationUri = $verificationUri;
            $this->userId = $userId;
            $this->status = $userId === null ? 'PENDING' : 'OK';
            $this->expired = $expiresIn <= 0;
        }
    }

}
