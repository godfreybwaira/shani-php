<?php

/**
 * Description of AuthorizationDetailsDto
 * @author goddy
 *
 * Created on: Mar 11, 2026 at 12:22:18 PM
 */

namespace lib\oauth2\dto {

    final class AuthorizationCodeDetailsDto
    {

        /**
         * One-time authorization code
         * @var string
         */
        public readonly string $code;

        /**
         * Client that requested this code
         *  @var string
         */
        public readonly string $clientId;

        /**
         * User who authorized the request
         * @var int
         */
        public readonly string $userId;

        /**
         * Requested scopes (space-separated)
         *  @var string|null
         */
        public readonly ?string $scope;

        /**
         * PKCE code challenge (SHA-256 hash) – null = classic flow
         *  @var string|null
         */
        public readonly ?string $codeChallenge;

        /**
         * Code challenge method must be 'S256'
         *  @var string|null
         */
        public readonly ?string $codeChallengeMethod;

        /**
         * Expiration in seconds
         * @var int
         */
        public readonly int $expiresIn;

        /**
         * Check if the code is expires. This is true if <code>$expiresIn</code>
         * is less or equals to zero
         * @var bool
         */
        public readonly bool $expired;

        /**
         * @param string      $clientId              Client that requested the code.
         * @param string      $code                  One-time authorization code.
         * @param int         $userId                User who authorized the request.
         * @param string|null $scope                 Space-separated scopes.
         * @param string|null $codeChallenge         PKCE challenge (SHA-256 hash). Null = no PKCE.
         * @param string|null $codeChallengeMethod   Must be 'S256' only (plain is forbidden).
         * @param string      $expiresIn               Expiration timestamp.
         */
        public function __construct(string $clientId, string $code, string $userId, ?string $scope, ?string $codeChallenge, ?string $codeChallengeMethod, int $expiresIn)
        {
            $this->code = $code;
            $this->clientId = $clientId;
            $this->userId = $userId;
            $this->scope = $scope;
            $this->codeChallenge = $codeChallenge;
            $this->codeChallengeMethod = $codeChallengeMethod;
            $this->expiresIn = $expiresIn;
            $this->expired = $expiresIn <= 0;
        }
    }

}
