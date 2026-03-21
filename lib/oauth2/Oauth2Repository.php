<?php

/**
 * Description of Oauth2Repository
 * @author goddy
 *
 * Created on: Mar 11, 2026 at 12:18:46 PM
 */

namespace lib\oauth2 {

    use lib\oauth2\dto\AccessTokenDto;
    use lib\oauth2\dto\AuthorizationCodeDetailsDto;
    use lib\oauth2\dto\ClientDetailsDto;
    use lib\oauth2\dto\DeviceCodeDetailsDto;
    use lib\oauth2\dto\RefreshTokenDto;
    use lib\oauth2\dto\UserDetailsDto;

    interface Oauth2Repository
    {

        /**
         * Delete (revoke) device code
         * @param string $clientId Client ID
         * @param string $deviceCode Device code
         * @return bool True on success, false otherwise.
         */
        public function revokeDeviceCode(string $clientId, string $deviceCode): bool;

        /**
         * Delete (revoke) refresh token.
         * @param string $clientId Client ID
         * @param string $refreshToken Refresh token
         * @return bool True on success, false otherwise.
         */
        public function revokeRefreshToken(string $clientId, string $refreshToken): bool;

        /**
         * Delete (revoke) authorization code.
         * @param string $clientId Client ID
         * @param string $authorizationCode Authorization code
         * @return bool True on success, false otherwise.
         */
        public function revokeAuthorizationCode(string $clientId, string $authorizationCode): bool;

        /**
         * Get oauth2 client details by client id and secret.
         *
         * @param string $clientIpAddress Client IP Address
         * @param string $clientId Client ID.
         * @param string|null $clientSecret Client secret (hashed verification).
         * @return ClientDetailsDto|null Client data or null if invalid.
         */
        public function getClientDetails(string $clientIpAddress, string $clientId, ?string $clientSecret = null): ?ClientDetailsDto;

        /**
         * Get Authorization details by supplied code, and client id
         *
         * @param string $clientId Client ID
         * @param string $authorizationCode Current authorization code
         * @return AuthorizationCodeDetailsDto|null Authorization code details if exists and not expires, null otherwise.
         */
        public function getAuthorizationCodeDetails(string $clientId, string $authorizationCode): ?AuthorizationCodeDetailsDto;

        /**
         * Get Active client device details
         *
         * @param string $clientId Client ID
         * @param string $deviceCode Current device code
         * @return DeviceCodeDetailsDto|null Device details if exists and not expires, null otherwise
         */
        public function getDeviceCodeDetails(string $clientId, string $deviceCode): ?DeviceCodeDetailsDto;

        /**
         * Get active client refresh token
         * @param string $clientId Client ID
         * @param string $refreshToken Current refresh token
         * @return RefreshTokenDto|null Returns refresh token details if exists and not expires, null otherwise
         */
        public function getRefreshToken(string $clientId, string $refreshToken): ?RefreshTokenDto;

        /**
         * Generate, store and return client access token
         * @param string $clientId Client ID
         * @param string|null $scope Scope (permissions)
         * @param string|null $userId User ID who's granting permission to an app (null for client credentials)
         * @return AccessTokenDto Access token details
         */
        public function generateAccessToken(string $clientId, ?string $scope, ?string $userId): AccessTokenDto;

        /**
         * Generate, store and return client authorization token
         *
         * @param string $clientId Client ID
         * @param string|null $scope Scope (permissions)
         * @param string $userId User ID who's granting permission to an app (null for client credentials)
         * @param string $redirectUri Redirect URL
         * @param string|null $codeChallenge PKCE challenge.
         * @param string|null $codeChallengeMethod PKCE method (S256).
         * @param int $expiresIn Number of seconds before expiration.
         * @return AccessTokenDto Access token details
         */
        public function generateAuthorizationCode(string $clientId, ?string $scope, string $userId, string $redirectUri, ?string $codeChallenge = null, ?string $codeChallengeMethod = null, int $expiresIn = 600): AccessTokenDto;

        /**
         * Generate, store and return client refresh token
         * @param string $clientId Client ID
         * @param string|null $scope Scope (permissions)
         * @param string|null $userId User ID who's granting permission to an app (null for client credentials)
         * @param int $expiresIn Number of seconds before expiration.
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

        /**
         * Validates an access token. If token exists and expired, delete it.
         *
         * @param string|null $token Access token to verify.
         * @return AccessTokenDto Access token details if the token is valid, null otherwise
         */
        public function validateAccessToken(?string $token): ?AccessTokenDto;
    }

}
