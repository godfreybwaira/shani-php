<?php

/**
 * Used for the Device Authorization Grant (smart TVs, CLI tools, etc.).
 * @author goddy
 *
 * Created on: Mar 11, 2026 at 12:22:18 PM
 */

namespace lib\oauth2\dto {

    final class DeviceDetailsDto
    {

        /**
         * @var string Long device code used for polling
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
         * @var int Expiration duration (in seconds)
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
         *
         * @param string   $clientId          Requesting client.
         * @param string   $deviceCode        Long opaque device code for polling.
         * @param string   $userCode          Short user code shown on TV/CLI.
         * @param int|null $userId  User ID once authorized (null = pending).
         * @param string|null $scope          Requested scopes.
         * @param int   $expiresIn           Expiration in seconds.
         * @param int      $pollingInterval   Recommended polling interval (default 5seconds).
         */
        public function __construct(string $clientId, string $deviceCode, string $userCode, ?string $userId, ?string $scope, int $expiresIn, int $pollingInterval = 5)
        {
            $this->deviceCode = $deviceCode;
            $this->userCode = $userCode;
            $this->clientId = $clientId;
            $this->scope = $scope;
            $this->expiresIn = $expiresIn;
            $this->pollingInterval = $pollingInterval;
            $this->userId = $userId;
            $this->status = $userId === null ? 'PENDING' : 'OK';
        }
    }

}
