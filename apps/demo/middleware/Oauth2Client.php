<?php

/**
 * Description of Oauth2Client
 * @author goddy
 *
 * Created on: Mar 13, 2026 at 4:31:15 PM
 */

namespace apps\demo\middleware {

    use lib\crypto\KeyGen;
    use lib\oauth2\dto\AccessTokenDto;
    use lib\oauth2\dto\AuthorizationDetailsDto;
    use lib\oauth2\dto\ClientDetailsDto;
    use lib\oauth2\dto\DeviceDetailsDto;
    use lib\oauth2\dto\RefreshTokenDto;
    use lib\oauth2\dto\UserDetailsDto;
    use lib\oauth2\Oauth2Repository;

    final class Oauth2Client implements Oauth2Repository
    {

        public function generateAccessToken(string $clientId, ?string $scope, ?string $userId, int $expiresIn = 3600): AccessTokenDto
        {
            return new AccessTokenDto($clientId, bin2hex(base64_decode(KeyGen::signature(32))), $userId, $scope, $expiresIn);
        }

        public function generateRefreshToken(string $clientId, ?string $scope, ?string $userId, int $expiresIn = 2592000): RefreshTokenDto
        {
            return new RefreshTokenDto($clientId, bin2hex(base64_decode(KeyGen::signature(32))), $userId, $scope, $expiresIn);
        }

        public function getActiveAuthorizationDetails(string $clientId, string $code, string $redirectUri, ?string $codeVerifier = null): ?AuthorizationDetailsDto
        {
            return new AuthorizationDetailsDto($clientId, $code, '123', $redirectUri, 'read write', null, 'S256', 3600);
        }

        public function getActiveDeviceDetails(string $clientId, string $deviceCode): ?DeviceDetailsDto
        {
            return new DeviceDetailsDto($clientId, $deviceCode, 'usercode123', 'user123', 'read write', 3600);
        }

        public function getActiveRefreshToken(string $clientId, string $refreshToken): ?RefreshTokenDto
        {
            return new RefreshTokenDto($clientId, $refreshToken, 'user123', 'read write', 3600);
        }

        public function getClientDetails(string $clientId, ?string $clientSecret = null, ?string $redirectUri = null, bool $requireSecret = true): ?ClientDetailsDto
        {
            return new ClientDetailsDto($clientId, $clientSecret, $redirectUri);
        }

        public function scopeAllowed(?string $scope): bool
        {
            return true;
        }

        public function authenticate(string $username, string $password): ?UserDetailsDto
        {
            return new UserDetailsDto('123', $username, $password);
        }
    }

}
