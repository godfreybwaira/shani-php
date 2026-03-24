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
    use lib\oauth2\dto\AuthorizationCodeDetailsDto;
    use lib\oauth2\dto\ClientDetailsDto;
    use lib\oauth2\dto\DeviceCodeDetailsDto;
    use lib\oauth2\dto\RefreshTokenDto;
    use lib\oauth2\dto\UserDetailsDto;
    use lib\oauth2\Oauth2GrantType;
    use lib\oauth2\Oauth2Repository;

    final class Oauth2Client implements Oauth2Repository
    {

        private const REDIRECT_URI = 'http://dev.shani.v2.local/security/0/oauth2/0';
        private const REFRESH_TOKEN = 'ac7248a057146a295c7b9628dc9be0f74f938bbb181d6806fc5f18440f0bb9c9';
        private const ACCESS_TOKEN = 'cd8b76f03e85cb5da72fd0481b671a996641a3bb32c214a8188e31f313179f8d';
        private const CLIENT_ID = '1c:81:25:28:94:8e';
        private const CLIENT_SECRET = '38a79817-6f70-400b-90d4-8d1912dd8b89';
        private const USERNAME = 'Freeda84';
        private const PASSWORD = 'krPBoWaaqRsgQVL';

        public function generateAccessToken(string $clientId, ?string $scope, ?string $userId, int $expiresIn = 9): AccessTokenDto
        {
            return new AccessTokenDto($clientId, bin2hex(base64_decode(KeyGen::signature(32))), $userId, $scope, $expiresIn);
        }

        public function generateRefreshToken(string $clientId, ?string $scope, ?string $userId, int $expiresIn = 2592000): RefreshTokenDto
        {
            return new RefreshTokenDto($clientId, bin2hex(base64_decode(KeyGen::signature(32))), $userId, $scope, $expiresIn);
        }

        public function getAuthorizationCodeDetails(string $clientId, string $authorizationCode): ?AuthorizationCodeDetailsDto
        {
            return new AuthorizationCodeDetailsDto($clientId, $authorizationCode, '123', 'read write', 'bKybd0Syvr9pTGvFy9P_G13jxG0_gW6Jf2TOK0vh34k', 'S256', 3600);
        }

        public function getDeviceCodeDetails(string $clientId, string $deviceCode): ?DeviceCodeDetailsDto
        {
            return new DeviceCodeDetailsDto($clientId, $deviceCode, 'usercode123', 'user123', 'read write', 3600);
        }

        public function getRefreshToken(string $clientId, string $refreshToken): ?RefreshTokenDto
        {
            return new RefreshTokenDto($clientId, $refreshToken, 'user123', 'read write', 3600);
        }

        public function getClientDetails(?Oauth2GrantType $grantType, string $clientIpAddress, string $clientId, ?string $clientSecret = null): ?ClientDetailsDto
        {
            return new ClientDetailsDto($clientId, $clientSecret, self::REDIRECT_URI);
        }

        public function authenticate(string $username, string $password): ?UserDetailsDto
        {
            return new UserDetailsDto('123', $username, $password);
        }

        public function generateAuthorizationCode(string $clientId, ?string $scope, string $userId, string $redirectUri, ?string $codeChallenge = null, ?string $codeChallengeMethod = null, int $expiresIn = 600): AccessTokenDto
        {
            return new AccessTokenDto($clientId, bin2hex(base64_decode(KeyGen::signature(32))), $userId, $scope, $expiresIn);
        }

        public function validateAccessToken(string $requestIp, string $token): ?AccessTokenDto
        {
            return new AccessTokenDto('123', $token, 'user2', '430704a766', 100);
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
            return new DeviceCodeDetailsDto($clientId, bin2hex(base64_decode(KeyGen::signature(32))), $userCode, 'user123', $scope, 3600, 5);
        }

        public function revokeAllRefreshTokens(string $clientId, string $userId = null): void
        {

        }
    }

}
