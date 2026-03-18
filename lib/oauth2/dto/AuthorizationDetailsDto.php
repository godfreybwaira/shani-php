<?php

/**
 * Description of AuthorizationDetailsDto
 * @author goddy
 *
 * Created on: Mar 11, 2026 at 12:22:18 PM
 */

namespace lib\oauth2\dto {

    final class AuthorizationDetailsDto
    {

        /**
         * @var string One-time authorization code
         */
        public readonly string $code;

        /**
         *  @var string Client that requested this code
         */
        public readonly string $clientId;

        /**
         * @var int User who authorized the request
         */
        public readonly string $userId;

        /**
         * @var string Redirect URI that must match exactly
         */
        public readonly string $redirectUri;

        /**
         *  @var string|null Requested scopes (space-separated)
         */
        public readonly ?string $scope;

        /**
         *  @var string|null PKCE code challenge (SHA-256 hash) – null = classic flow
         */
        public readonly ?string $codeChallenge;

        /**
         *  @var string|null Must be 'S256'
         */
        public readonly ?string $codeChallengeMethod;

        /**
         * @var string Expiration timestamp
         */
        public readonly string $expiresIn;

        /**
         * @param string      $clientId              Client that requested the code.
         * @param string      $code                  One-time authorization code.
         * @param int         $userId                User who authorized the request.
         * @param string      $redirectUri           Exact redirect URI (OAuth 2.1 requirement).
         * @param string|null $scope                 Space-separated scopes.
         * @param string|null $codeChallenge         PKCE challenge (SHA-256 hash). Null = no PKCE.
         * @param string|null $codeChallengeMethod   Must be 'S256' only (plain is forbidden).
         * @param string      $expiresIn               Expiration timestamp.
         */
        public function __construct(string $clientId, string $code, string $userId, string $redirectUri, ?string $scope, ?string $codeChallenge, ?string $codeChallengeMethod, string $expiresIn)
        {
            $this->code = $code;
            $this->clientId = $clientId;
            $this->userId = $userId;
            $this->redirectUri = $redirectUri;
            $this->scope = $scope;
            $this->codeChallenge = $codeChallenge;
            $this->codeChallengeMethod = $codeChallengeMethod;
            $this->expiresIn = $expiresIn;
        }
    }

}
