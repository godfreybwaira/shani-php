<?php

/**
 * Description of AccessTokenDto
 * @author goddy
 *
 * Created on: Mar 12, 2026 at 1:08:18 PM
 */

namespace features\oauth2\dto {

    final class AccessTokenDto
    {

        /**
         *  @var string The actual bearer token
         */
        public readonly string $token;

        /**
         *  @var string Client that owns this token
         */
        public readonly string $clientId;

        /**
         * @var string|null User ID (null = client_credentials grant)
         */
        public readonly ?string $userId;

        /**
         *  @var string|null Granted scopes
         */
        public readonly ?string $scope;

        /**
         * Number of seconds before expiration
         * @var int
         */
        public readonly int $expiresIn;

        /**
         * @param string      $clientId    Client that received the token.
         * @param string      $accessToken The bearer token string.
         * @param string|null    $userId      User ID or null for machine-to-machine (client_credentials).
         * @param string|null $scope       Space-separated scopes granted.
         * @param int      $expiresIn     Number of seconds before expiration.
         */
        public function __construct(string $clientId, string $accessToken, ?string $userId, ?string $scope, int $expiresIn)
        {
            $this->clientId = $clientId;
            $this->token = $accessToken;
            $this->userId = $userId;
            $this->scope = $scope;
            $this->expiresIn = $expiresIn;
        }
    }

}
