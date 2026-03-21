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
         * Unique client identifier (publicly known)
         *  @var string
         */
        public readonly string $clientId;

        /**
         * Client IP Address
         * @var string
         */
        public readonly string $clientIpAddress;

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
         * @param string $clientIpAddress       Client IP Address
         * @param string $clientId              Unique client identifier registered in the database.
         * @param string|null $clientSecret     Hashed client secret. For public clients (PKCE), this can be an empty string.
         * @param string|null $redirectUri       Exact redirect URI.
         */
        public function __construct(string $clientIpAddress, string $clientId, ?string $clientSecret, ?string $redirectUri)
        {
            $this->clientId = $clientId;
            $this->clientSecret = $clientSecret;
            $this->redirectUri = $redirectUri;
            $this->clientIpAddress = $clientIpAddress;
        }
    }

}
