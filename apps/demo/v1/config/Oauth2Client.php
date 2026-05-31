<?php

/**
 * Description of Oauth2Client
 * @author goddy
 *
 * Created on: Mar 13, 2026 at 4:31:15 PM
 */

namespace apps\demo\v1\config {

    use features\authentication\UserDetailsDto;
    use features\jwt\JWTAlgorithm;
    use features\jwt\JWTClaim;
    use features\oauth2\dto\AccessTokenDto;
    use features\oauth2\dto\AuthorizationCodeDetailsDto;
    use features\oauth2\dto\DeviceCodeDetailsDto;
    use features\oauth2\dto\OauthClientDetailsDto;
    use features\oauth2\dto\RefreshTokenDto;
    use features\oauth2\Oauth2GrantType;
    use features\oauth2\Oauth2Repository;
    use features\utils\Duration;

    final class Oauth2Client implements Oauth2Repository
    {

        private const REDIRECT_URI = 'https://dev.shani.v2.local/oauth2/0';

        public function generateAccessToken(string $clientId, ?string $scope, ?string $userId, int $expiresIn = 9): AccessTokenDto
        {
            $ttl = Duration::ofMinutes(5);
            $jwt = new JWTClaim(JWTAlgorithm::EdDSA);
            $jwt->setClaim('access', '24354fed,5ca2536e');
            $key = '-----BEGIN PRIVATE KEY-----' . PHP_EOL . 'MC4CAQAwBQYDK2VwBCIEIJSxh/iy0iGxirtYZOEQiwFQx3R3WJXg1PPxY/0xypBK';
            $key .= PHP_EOL . '-----END PRIVATE KEY-----';
            return new AccessTokenDto($clientId, $jwt->getToken($key), $userId, $scope, $ttl->fromNow());
        }

        public function generateRefreshToken(string $clientId, ?string $scope, ?string $userId, int $expiresIn = 2592000): RefreshTokenDto
        {
            return new RefreshTokenDto($clientId, bin2hex(random_bytes(32)), $userId, $scope, $expiresIn);
        }

        public function getAuthorizationCodeDetails(string $clientId, string $authorizationCode): ?AuthorizationCodeDetailsDto
        {
            return new AuthorizationCodeDetailsDto($clientId, $authorizationCode, '123', 'read write', 'bKybd0Syvr9pTGvFy9P_G13jxG0_gW6Jf2TOK0vh34k', 'S256', 3600);
        }

        public function getDeviceCodeDetails(string $clientId, string $deviceCode): ?DeviceCodeDetailsDto
        {
            return new DeviceCodeDetailsDto($clientId, $deviceCode, 'usercode123', self::REDIRECT_URI . '/device', 'user123', 'read write', 600, 5);
        }

        public function getRefreshToken(string $clientId, string $refreshToken): ?RefreshTokenDto
        {
            return new RefreshTokenDto($clientId, $refreshToken, 'user123', 'read write', 3600);
        }

        public function getClientDetails(?Oauth2GrantType $grantType, string $clientIpAddress, string $clientId, ?string $clientSecret = null): ?OauthClientDetailsDto
        {
            return new OauthClientDetailsDto($clientId, $clientSecret, self::REDIRECT_URI, false);
        }

        public function authenticate(string $username, string $password): ?UserDetailsDto
        {
            return new UserDetailsDto('123', null, false);
        }

        public function generateAuthorizationCode(string $clientId, ?string $scope, string $userId, string $redirectUri, ?string $codeChallenge = null, ?string $codeChallengeMethod = null, int $expiresIn = 600): AccessTokenDto
        {
            return new AccessTokenDto($clientId, bin2hex(random_bytes(32)), $userId, $scope, $expiresIn);
        }

        public function revokeAuthorizationCode(string $clientId, string $authorizationCode): void
        {

        }

        public function revokeRefreshToken(string $clientId, string $refreshToken): void
        {

        }

        public function revokeDeviceCode(string $clientId, string $deviceCode): void
        {

        }

        public function generateDeviceCode(string $clientId, ?string $scope, string $userCode): DeviceCodeDetailsDto
        {
            return new DeviceCodeDetailsDto($clientId, bin2hex(random_bytes(32)), $userCode, self::REDIRECT_URI . '/device', 'user123', $scope, 600, 5);
        }

        public function revokeAllRefreshTokens(string $clientId, string $userId = null): void
        {

        }

        public function authorizeDeviceCode(string $userId, string $userCode, string $deviceCode): bool
        {
            return true;
        }
    }

}
