<?php

/**
 * Description of ClientDetailsDto
 * @author goddy
 *
 * Created on: Mar 11, 2026 at 12:22:18 PM
 */

namespace features\oauth2\dto {

    final class OauthClientDetailsDto
    {

        /**
         * Unique client identifier (publicly known)
         *  @var string
         */
        public readonly string $clientId;

        /**
         * Hashed client secret (never store plain text)
         *  @var string|null
         */
        public readonly ?string $clientSecret;

        /**
         * Exact redirect URI registered for this client (OAuth 2.1 requires exact match)
         *  @var string|null
         */
        public readonly ?string $redirectUri;

        /**
         * Tells whether the client is disabled or not
         * @var bool
         */
        public readonly bool $isDisabled;

        /**
         * @param string $clientId              Unique client identifier registered in the database.
         * @param string|null $clientSecret     Hashed client secret. For public clients (PKCE), this can be an empty string.
         * @param string|null $redirectUri       Exact redirect URI.
         * @param bool $isDisabled Tells whether the client is disabled or not
         */
        public function __construct(string $clientId, ?string $clientSecret, ?string $redirectUri, bool $isDisabled)
        {
            $this->clientId = $clientId;
            $this->clientSecret = $clientSecret;
            $this->redirectUri = $redirectUri;
            $this->isDisabled = $isDisabled;
        }
    }

}
