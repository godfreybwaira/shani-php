<?php

/**
 * Description of ClientDetailsDto
 * @author goddy
 *
 * Created on: Mar 11, 2026 at 12:22:18 PM
 */

namespace lib\oauth2\dto {

    final class ClientDetailsDto
    {

        /**
         *  @var string Unique client identifier (publicly known)
         */
        public readonly string $clientId;

        /**
         *  @var string Hashed client secret (never store plain text)
         */
        public readonly string $clientSecret;

        /**
         *  @var string|null Exact redirect URI registered for this client (OAuth 2.1 requires exact match)
         */
        public readonly ?string $redirectUri;

        /**
         * @param string $clientId      Unique client identifier registered in the database.
         * @param string $clientSecret  Hashed client secret. For public clients (PKCE), this can be an empty string.
         * @param string|null $redirectUri   Exact redirect URI.
         */
        public function __construct(string $clientId, string $clientSecret, ?string $redirectUri)
        {
            $this->clientId = $clientId;
            $this->clientSecret = $clientSecret;
            $this->redirectUri = $redirectUri;
        }
    }

}
