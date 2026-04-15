<?php

/**
 * Description of OAuth2TokenIssuer
 * @author goddy
 *
 * Created on: Mar 9, 2026 at 11:25:31 AM
 */

namespace features\oauth2 {

    use features\ds\map\ReadableMap;
    use shani\http\HttpHeader;
    use shani\http\enums\HttpStatus;
    use features\utils\MediaType;
    use shani\launcher\App;

    final class OAuth2TokenIssuer
    {

        private readonly App $app;
        private readonly ReadableMap $body;
        private readonly Oauth2Repository $repo;
        private readonly ?Oauth2GrantType $grantType;
        private readonly ?string $clientId, $clientSecret;

        public function __construct(App $app)
        {
            $this->app = $app;
            $this->body = $app->request->body();
            $creds = $this->app->request->header()->getBasicAuth();
            $this->clientId = $creds[0] ?? $this->body->getOne('client_id');
            $this->clientSecret = $creds[1] ?? $this->body->getOne('client_secret');
            $this->grantType = Oauth2GrantType::tryFrom($this->body->getOne('grant_type'));
            $this->repo = $app->config->getOauth2Repository();
        }

        /**
         * Handles incoming requests and routes to appropriate oauth handler method.
         * @return Oauth2Response Returns Oauth 2 response on success
         */
        public function handleRequest(): Oauth2Response
        {
            $this->app->response->header()->addOne(HttpHeader::CONTENT_TYPE, MediaType::JSON);
            if ($this->clientId === null || $this->clientSecret === null) {
                $this->app->response->setStatus(HttpStatus::BAD_REQUEST);
                return Oauth2Response::error(Oauth2Error::INVALID_REQUEST, 'The following parameter(s) were required but missing: client_id, client_secret');
            }
            return match ($this->grantType) {
                Oauth2GrantType::AUTHORIZATION_CODE => $this->grantByAuthorizationCode(),
                Oauth2GrantType::AUTHORIZATION_CODE_PKCE => $this->grantByAuthorizationCodePKCE(),
                Oauth2GrantType::CLIENT_CREDENTIALS => $this->grantByClientCredentials(),
                Oauth2GrantType::REFRESH_TOKEN => $this->grantByRefreshToken(),
                Oauth2GrantType::PASSWORD => $this->grantByPassword(),
                Oauth2GrantType::DEVICE_CODE => $this->grantByDeviceCode(),
                default => Oauth2Response::error(Oauth2Error::UNSUPPORTED_GRANT_TYPE, 'The authorization grant type is not supported by the authorization server.')
            };
        }

        private function grantByAuthorizationCode(): Oauth2Response
        {
            $redirectUri = $this->body->getOne('redirect_uri');
            $code = $this->body->getOne('code');
            $client = $this->repo->getClientDetails($this->grantType, $this->app->request->ip, $this->clientId, $this->clientSecret);
            if ($client === null || $client->isDisabled) {
                $this->app->response->setStatus(HttpStatus::UNAUTHORIZED);
                return Oauth2Response::error(Oauth2Error::INVALID_CLIENT, 'Client authentication failed.');
            }
            if ($redirectUri !== $client->redirectUri) {
                return Oauth2Response::error(Oauth2Error::INVALID_REQUEST, 'The redirect_uri parameter does not match the registered value.');
            }
            $this->app->response->setStatus(HttpStatus::UNAUTHORIZED);
            $authCode = $this->repo->getAuthorizationCodeDetails($this->clientId, $code);
            if ($authCode === null) {
                return Oauth2Response::error(Oauth2Error::INVALID_GRANT, 'The provided authorization code is invalid, expired or revoked.');
            }
            $this->repo->revokeAuthorizationCode($this->clientId, $code); // Revoke code (one-time use)
            if ($authCode->expired) {
                return Oauth2Response::error(Oauth2Error::INVALID_GRANT, 'The authorization code has expired.');
            }
            $this->repo->revokeAllRefreshTokens($this->clientId, $authCode->userId);
            $accessToken = $this->repo->generateAccessToken($this->clientId, $authCode->scope, $authCode->userId);
            $refreshToken = $this->repo->generateRefreshToken($this->clientId, $authCode->scope, $authCode->userId);
            $this->app->response->setStatus(HttpStatus::OK);
            return Oauth2Response::success($accessToken->token, $accessToken->expiresIn, $refreshToken->token, $accessToken->scope);
        }

        private function grantByAuthorizationCodePKCE(): Oauth2Response
        {
            $this->app->response->setStatus(HttpStatus::BAD_REQUEST);
            $keys = $this->body->absentKeys(['code_verifier', 'redirect_uri', 'code']);
            if ($keys !== null) {
                return Oauth2Response::error(Oauth2Error::INVALID_REQUEST, 'Missing required parameter(s): ' . implode(', ', $keys));
            }
            $codeVerifier = $this->body->getOne('code_verifier');
            $code = $this->body->getOne('code');
            $redirectUri = $this->body->getOne('redirect_uri');
            $client = $this->repo->getClientDetails($this->grantType, $this->app->request->ip, $this->clientId, $this->clientSecret);
            if ($client === null || $client->isDisabled) {
                $this->app->response->setStatus(HttpStatus::UNAUTHORIZED);
                return Oauth2Response::error(Oauth2Error::INVALID_CLIENT, 'Client authentication failed.');
            }
            if ($redirectUri !== $client->redirectUri) {
                return Oauth2Response::error(Oauth2Error::INVALID_REQUEST, 'The redirect_uri parameter does not match the registered value.');
            }
            $this->app->response->setStatus(HttpStatus::UNAUTHORIZED);
            $authCode = $this->repo->getAuthorizationCodeDetails($this->clientId, $code);
            if ($authCode === null) {
                return Oauth2Response::error(Oauth2Error::INVALID_GRANT, 'The provided authorization code is invalid, expired or revoked.');
            }
            $this->repo->revokeAuthorizationCode($this->clientId, $code); // Revoke code (one-time use)
            if ($authCode->expired) {
                return Oauth2Response::error(Oauth2Error::INVALID_GRANT, 'The authorization code has expired.');
            }
            if (!PKCEGenerator::validatePKCE($authCode->codeChallenge, $authCode->codeChallengeMethod, $codeVerifier)) {
                $this->app->response->setStatus(HttpStatus::BAD_REQUEST);
                return Oauth2Response::error(Oauth2Error::INVALID_REQUEST, 'Code challenge verification failed.');
            }
            $this->repo->revokeAllRefreshTokens($this->clientId, $authCode->userId);
            $accessToken = $this->repo->generateAccessToken($this->clientId, $authCode->scope, $authCode->userId);
            $refreshToken = $this->repo->generateRefreshToken($this->clientId, $authCode->scope, $authCode->userId);
            $this->app->response->setStatus(HttpStatus::OK);
            return Oauth2Response::success($accessToken->token, $accessToken->expiresIn, $refreshToken->token, $accessToken->scope);
        }

        /**
         * Machine to machine communication
         * @return Oauth2Response
         */
        private function grantByClientCredentials(): Oauth2Response
        {
            $client = $this->repo->getClientDetails($this->grantType, $this->app->request->ip, $this->clientId, $this->clientSecret);
            if ($client === null || $client->isDisabled) {
                $this->app->response->setStatus(HttpStatus::UNAUTHORIZED);
                return Oauth2Response::error(Oauth2Error::INVALID_CLIENT, 'Client authentication failed.');
            }
            $scope = $this->body->getOne('scope');
            $accessToken = $this->repo->generateAccessToken($this->clientId, $scope, userId: null, expiresIn: 3600);
            $this->app->response->setStatus(HttpStatus::OK);
            return Oauth2Response::success($accessToken->token, $accessToken->expiresIn, null, $accessToken->scope);
        }

        private function grantByRefreshToken(): Oauth2Response
        {
            $refreshToken = $this->body->getOne('refresh_token');
            if ($refreshToken === null) {
                $this->app->response->setStatus(HttpStatus::BAD_REQUEST);
                return Oauth2Response::error(Oauth2Error::INVALID_REQUEST, 'The request is missing a required parameter `refresh_token`');
            }
            $this->app->response->setStatus(HttpStatus::UNAUTHORIZED);
            $client = $this->repo->getClientDetails($this->grantType, $this->app->request->ip, $this->clientId, $this->clientSecret);
            if ($client === null || $client->isDisabled) {
                return Oauth2Response::error(Oauth2Error::INVALID_CLIENT, 'Client authentication failed.');
            }
            $refresh = $this->repo->getRefreshToken($this->clientId, $refreshToken);
            if ($refresh === null) {
                return Oauth2Response::error(Oauth2Error::INVALID_GRANT, 'The provided refresh token is invalid, expired, or revoked.');
            }
            $this->repo->revokeRefreshToken($this->clientId, $refresh->token); // Revoke old refresh token (rotation security)
            if ($refresh->expired) {
                return Oauth2Response::error(Oauth2Error::INVALID_GRANT, 'The refresh token has expired.');
            }
            $accessToken = $this->repo->generateAccessToken($this->clientId, $refresh->scope, $refresh->userId);
            $newRefreshToken = $this->repo->generateRefreshToken($this->clientId, $refresh->scope, $refresh->userId);
            $this->app->response->setStatus(HttpStatus::OK);
            return Oauth2Response::success($accessToken->token, $accessToken->expiresIn, $newRefreshToken->token, $accessToken->scope);
        }

        private function grantByPassword(): Oauth2Response
        {
            $keys = $this->body->absentKeys(['username', 'password']);
            if ($keys !== null) {
                return Oauth2Response::error(Oauth2Error::INVALID_REQUEST, 'Missing required parameter(s): ' . implode(', ', $keys));
            }
            $username = $this->body->getOne('username');
            $password = $this->body->getOne('password');
            $user = $this->repo->authenticate($username, $password);
            $this->app->response->setStatus(HttpStatus::UNAUTHORIZED);
            if ($user === null || $user->isDisabled) {
                return Oauth2Response::error(Oauth2Error::INVALID_GRANT, 'The user credentials were incorrect or disabled.');
            }
            $client = $this->repo->getClientDetails($this->grantType, $this->app->request->ip, $this->clientId, $this->clientSecret);
            if ($client === null || $client->isDisabled) {
                return Oauth2Response::error(Oauth2Error::INVALID_CLIENT, 'Client authentication failed.');
            }
            $scope = $this->body->getOne('scope');
            $this->repo->revokeAllRefreshTokens($this->clientId, $user->id);
            $accessToken = $this->repo->generateAccessToken($this->clientId, $scope, $user->id);
            $refreshToken = $this->repo->generateRefreshToken($this->clientId, $scope, $user->id);
            $this->app->response->setStatus(HttpStatus::OK);
            return Oauth2Response::success($accessToken->token, $accessToken->expiresIn, $refreshToken->token, $accessToken->scope);
        }

        private function grantByDeviceCode(): Oauth2Response
        {
            $this->app->response->setStatus(HttpStatus::BAD_REQUEST);
            $deviceCode = $this->body->getOne('device_code');
            if ($deviceCode === null) {
                return Oauth2Response::error(Oauth2Error::INVALID_REQUEST, 'The request is missing a required parameter `device_code`');
            }
            $client = $this->repo->getClientDetails($this->grantType, $this->app->request->ip, $this->clientId, $this->clientSecret);
            if ($client === null || $client->isDisabled) {
                $this->app->response->setStatus(HttpStatus::UNAUTHORIZED);
                return Oauth2Response::error(Oauth2Error::INVALID_CLIENT, 'Client authentication failed.');
            }
            $device = $this->repo->getDeviceCodeDetails($this->clientId, $deviceCode);
            if ($device === null) {
                return Oauth2Response::error(Oauth2Error::EXPIRED_TOKEN, 'The device code has expired or invalid.');
            }
            if ($device->status === 'PENDING') {
                return Oauth2Response::error(Oauth2Error::AUTHORIZATION_PENDING, 'The device authorization is pending user approval.');
            }
            $this->repo->revokeDeviceCode($this->clientId, $deviceCode); //One-time use
            if ($device->expired) {
                $this->app->response->setStatus(HttpStatus::UNAUTHORIZED);
                return Oauth2Response::error(Oauth2Error::INVALID_GRANT, 'The device code has expired.');
            }
            $this->repo->revokeAllRefreshTokens($this->clientId, $device->userId);
            $accessToken = $this->repo->generateAccessToken($this->clientId, $device->scope, $device->userId);
            $refreshToken = $this->repo->generateRefreshToken($this->clientId, $device->scope, $device->userId);
            $this->app->response->setStatus(HttpStatus::OK);
            return Oauth2Response::success($accessToken->token, $accessToken->expiresIn, $refreshToken->token, $accessToken->scope);
        }
    }

}
