<?php

/**
 * Description of Oauth2Repository
 * @author goddy
 *
 * Created on: Mar 11, 2026 at 12:18:46 PM
 */

namespace lib\oauth2 {

    use lib\oauth2\dto\AccessTokenDto;
    use lib\oauth2\dto\AuthorizationDetailsDto;
    use lib\oauth2\dto\ClientDetailsDto;
    use lib\oauth2\dto\DeviceDetailsDto;
    use lib\oauth2\dto\RefreshTokenDto;
    use lib\oauth2\dto\UserDetailsDto;

    interface Oauth2Repository
    {

        /**
         * Check whether a given scope (permission) is granted
         * @param string|null $scope Scope (or permission sometimes)
         * @return bool Return true on success, false otherwise.
         */
        public function scopeAllowed(?string $scope): bool;

        /**
         * Get oauth2 client details by client id and secret.
         *
         * @param string $clientId Client ID.
         * @param string|null $clientSecret Client secret (hashed verification).
         * @param string|null $redirectUri Redirect URI to validate.
         * @param bool $requireSecret Whether secret is required (false for PKCE).
         * @return ClientDetailsDto|null Client data or null if invalid.
         */
        public function getClientDetails(string $clientId, ?string $clientSecret = null, ?string $redirectUri = null, bool $requireSecret = true): ?ClientDetailsDto;

        /**
         * Get Authorization details by supplied code, and client id
         *
         * @param string $clientId Client ID
         * @param string $code Current authorization code
         * @param string $redirectUri Redirect URI to validate.
         * @param string|null $codeVerifier PKCE verifier
         * @return AuthorizationDetailsDto|null Authorization details if exists and not expires, null otherwise.
         */
        public function getActiveAuthorizationDetails(string $clientId, string $code, string $redirectUri, ?string $codeVerifier = null): ?AuthorizationDetailsDto;

        /**
         * Get Active client device details
         *
         * @param string $clientId Client ID
         * @param string $deviceCode Current device code
         * @return DeviceDetailsDto|null Device details if exists and not expires, null otherwise
         */
        public function getActiveDeviceDetails(string $clientId, string $deviceCode): ?DeviceDetailsDto;

        /**
         * Get active client refresh token
         * @param string $clientId Client ID
         * @param string $refreshToken Current refresh token
         * @return RefreshTokenDto|null Returns refresh token details if exists and not expires, null otherwise
         */
        public function getActiveRefreshToken(string $clientId, string $refreshToken): ?RefreshTokenDto;

        /**
         * Generate, store and return client access token
         * @param string $clientId Client ID
         * @param string|null $scope Scope (permissions)
         * @param string|null $userId User ID who's granting permission to an app (null for client credentials)
         * @param int $expiresIn Expiration in seconds.
         * @return AccessTokenDto Access token details
         */
        public function generateAccessToken(string $clientId, ?string $scope, ?string $userId, int $expiresIn = 3600): AccessTokenDto;

        /**
         * Generate, store and return client refresh token
         * @param string $clientId Client ID
         * @param string|null $scope Scope (permissions)
         * @param string|null $userId User ID who's granting permission to an app (null for client credentials)
         * @param int $expiresIn Expiration in seconds.
         * @return RefreshTokenDto Refresh token details
         */
        public function generateRefreshToken(string $clientId, ?string $scope, ?string $userId, int $expiresIn = 2592000): RefreshTokenDto;

        /**
         * Authenticate end-user credentials
         * @param string $username Username
         * @param string $password Password
         * @return UserDetailsDto|null User details if exists, null otherwise
         */
        public function authenticate(string $username, string $password): ?UserDetailsDto;
    }

}
