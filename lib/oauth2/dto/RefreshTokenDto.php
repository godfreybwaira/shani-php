<?php

/**
 * Description of RefreshTokenDto
 * @author goddy
 *
 * Created on: Mar 12, 2026 at 1:08:18 PM
 */

namespace lib\oauth2\dto {

    final class RefreshTokenDto
    {

        /**
         * @var string The refresh token
         */
        public readonly string $token;

        /**
         * @var string Client that owns this refresh token
         */
        public readonly string $clientId;

        /**
         * @var string|null User ID (null for client_credentials)
         */
        public readonly ?string $userId;

        /**
         * @var string|null Granted scopes
         */
        public readonly ?string $scope;

        /**
         * @var int Expiration duration (in seconds)
         */
        public readonly int $expiresIn;

        /**
         * @param string      $clientId     Owning client.
         * @param string      $refreshToken The refresh token.
         * @param string|null    $userId       User ID or null.
         * @param string|null $scope        Granted scopes.
         * @param int      $expiresIn      Expiration in seconds.
         */
        public function __construct(string $clientId, string $refreshToken, ?string $userId, ?string $scope, int $expiresIn)
        {
            $this->clientId = $clientId;
            $this->token = $refreshToken;
            $this->userId = $userId;
            $this->scope = $scope;
            $this->expiresIn = $expiresIn;
        }
    }

}
