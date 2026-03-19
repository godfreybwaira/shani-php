<?php

/**
 * Description of OAuth2TokenIssuer
 * @author goddy
 *
 * Created on: Mar 9, 2026 at 11:25:31 AM
 */

namespace lib\oauth2 {

    use lib\ds\map\ReadableMap;
    use lib\http\HttpHeader;
    use lib\http\HttpStatus;
    use lib\MediaType;
    use shani\http\App;

    final class OAuth2TokenIssuer
    {

        private readonly App $app;
        private readonly ReadableMap $body;
        private readonly Oauth2Repository $repo;
        private readonly ?string $clientId, $clientSecret;

        public function __construct(App $app)
        {
            $this->app = $app;
            $this->body = $app->request->body();
            $creds = $this->app->request->header()->getBasicAuth();
            $this->clientId = $this->body->getOne('client_id') ?? $creds[0] ?? null;
            $this->clientSecret = $this->body->getOne('client_secret') ?? $creds[1] ?? null;
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
            $grantType = Oauth2GrantType::tryFrom($this->body->getOne('grant_type'));
            return match ($grantType) {
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
            $this->app->response->setStatus(HttpStatus::BAD_REQUEST);
            $redirectUri = $this->body->getOne('redirect_uri');
            $client = $this->repo->getClientDetails($this->clientId, $this->clientSecret, true);
            if ($client === null || $redirectUri !== $client->redirectUri) {  // Require secret
                return Oauth2Response::error(Oauth2Error::INVALID_CLIENT, 'Client authentication failed.');
            }
            $code = $this->body->getOne('code');
            if ($code === null) {
                return Oauth2Response::error(Oauth2Error::INVALID_REQUEST, 'The request is missing a required parameter `code`');
            }
            $authCode = $this->repo->getActiveAuthorizationDetails($this->clientId, $code, $redirectUri, null);  // No verifier
            if ($authCode === null) {
                return Oauth2Response::error(Oauth2Error::INVALID_GRANT, 'The provided authorization grant is invalid, expired, revoked, or does not match the redirection URI.');
            }
            $accessToken = $this->repo->generateAccessToken($this->clientId, $authCode->scope, $authCode->userId);
            $refreshToken = $this->repo->generateRefreshToken($this->clientId, $authCode->userId, $authCode->userId);
            $this->app->response->setStatus(HttpStatus::OK);
            return Oauth2Response::success($accessToken->token, $accessToken->expiresIn, $refreshToken->token, $accessToken->scope);
        }

        private function grantByAuthorizationCodePKCE(): Oauth2Response
        {
            $this->app->response->setStatus(HttpStatus::BAD_REQUEST);
            $keys = $this->body->absentKeys(['code_verifier', 'redirect_uri', 'code']);
            if ($keys !== null) {
                return Oauth2Response::error(Oauth2Error::INVALID_REQUEST, 'The following parameters were reqiured but missing: ' . implode(', ', $keys));
            }
            $codeVerifier = $this->body->getOne('code_verifier');
            $code = $this->body->getOne('code');
            $redirectUri = $this->body->getOne('redirect_uri');
            if ($this->repo->getClientDetails($this->clientId, $this->clientSecret, false) === null) {  // Secret optional
                $this->app->response->setStatus(HttpStatus::FORBIDDEN);
                return Oauth2Response::error(Oauth2Error::INVALID_CLIENT, 'Client authentication failed.');
            }
            $details = $this->repo->getActiveAuthorizationDetails($this->clientId, $code, $redirectUri, $codeVerifier);
            if ($details === null) {
                return Oauth2Response::error(Oauth2Error::INVALID_GRANT, 'The provided authorization grant is invalid, expired, revoked, or does not match the redirection URI.');
            }
            $accessToken = $this->repo->generateAccessToken($this->clientId, $details->scope, $details->userId);
            $refreshToken = $this->repo->generateRefreshToken($this->clientId, $details->userId, $details->scope);
            $this->app->response->setStatus(HttpStatus::OK);
            return Oauth2Response::success($accessToken->token, $accessToken->expiresIn, $refreshToken->token, $accessToken->scope);
        }

        private function grantByClientCredentials(): Oauth2Response
        {
            $this->app->response->setStatus(HttpStatus::FORBIDDEN);
            if ($this->repo->getClientDetails($this->clientId, $this->clientSecret) === null) {
                return Oauth2Response::error(Oauth2Error::INVALID_CLIENT, 'Client authentication failed.');
            }
            $scope = $this->body->getOne('scope');
            if ($this->repo->scopeAllowed($scope)) {
                $accessToken = $this->repo->generateAccessToken($this->clientId, $scope, null);
                $refreshToken = $this->repo->generateRefreshToken($this->clientId, $scope, null);
                $this->app->response->setStatus(HttpStatus::OK);
                return Oauth2Response::success($accessToken->token, $accessToken->expiresIn, $refreshToken->token, $accessToken->scope);
            }
            return Oauth2Response::error(Oauth2Error::INVALID_SCOPE, 'Not allowed for a given scope.');
        }

        private function grantByRefreshToken(): Oauth2Response
        {
            $refreshToken = $this->body->getOne('refresh_token');
            $this->app->response->setStatus(HttpStatus::BAD_REQUEST);
            if ($refreshToken === null) {
                return Oauth2Response::error(Oauth2Error::INVALID_REQUEST, 'The request is missing a required parameter `refresh_token`');
            }
            if ($this->repo->getClientDetails($this->clientId, $this->clientSecret) === null) {
                return Oauth2Response::error(Oauth2Error::INVALID_CLIENT, 'Client authentication failed.');
            }
            $refresh = $this->repo->getActiveRefreshToken($this->clientId, $refreshToken);
            if ($refresh === null) {
                return Oauth2Response::error(Oauth2Error::INVALID_GRANT, 'The provided refresh token is invalid, expired, or revoked.');
            }
            $accessToken = $this->repo->generateAccessToken($this->clientId, $refresh->scope, $refresh->userId);
            $newRefreshToken = $this->repo->generateRefreshToken($this->clientId, $refresh->scope, $refresh->userId);
            $this->app->response->setStatus(HttpStatus::OK);
            return Oauth2Response::success($accessToken->token, $accessToken->expiresIn, $newRefreshToken->token, $accessToken->scope);
        }

        private function grantByPassword(): Oauth2Response
        {
            $username = $this->body->getOne('username');
            $password = $this->body->getOne('password');
            $user = $this->repo->authenticate($username, $password);
            $this->app->response->setStatus(HttpStatus::FORBIDDEN);
            if ($user === null) {
                return Oauth2Response::error(Oauth2Error::INVALID_GRANT, 'The user credentials were incorrect.');
            }
            if ($this->repo->getClientDetails($this->clientId, $this->clientSecret, true) === null) {
                return Oauth2Response::error(Oauth2Error::INVALID_CLIENT, 'Client authentication failed.');
            }
            $scope = $this->body->getOne('scope');
            if ($this->repo->scopeAllowed($scope)) {
                $accessToken = $this->repo->generateAccessToken($this->clientId, $scope, $user->id);
                $refreshToken = $this->repo->generateRefreshToken($this->clientId, $scope, $user->id);
                $this->app->response->setStatus(HttpStatus::OK);
                return Oauth2Response::success($accessToken->token, $accessToken->expiresIn, $refreshToken->token, $accessToken->scope);
            }
            return Oauth2Response::error(Oauth2Error::INVALID_SCOPE, 'Not allowed for a given scope.');
        }

        private function grantByDeviceCode(): Oauth2Response
        {
            $deviceCode = $this->body->getOne('device_code');
            if ($deviceCode === null) {
                return Oauth2Response::error(Oauth2Error::INVALID_REQUEST, 'The request is missing a required parameter `device_code`');
            }
            if ($this->repo->getClientDetails($this->clientId, $this->clientSecret, false) === null) {  // Secret optional for public devices
                $this->app->response->setStatus(HttpStatus::FORBIDDEN);
                return Oauth2Response::error(Oauth2Error::INVALID_CLIENT, 'Client authentication failed.');
            }
            $device = $this->repo->getActiveDeviceDetails($deviceCode, $this->clientId);
            if ($device === null) {
                $this->app->response->setStatus(HttpStatus::BAD_REQUEST);
                return Oauth2Response::error(Oauth2Error::TOKEN_EXPIRED, 'The device code has expired.');
            }
            if ($device->status === 'PENDING') {
                $this->app->response->setStatus(HttpStatus::PRECONDITION_FAILED);
                return Oauth2Response::error(Oauth2Error::AUTHORIZATION_PENDING, 'The device authorization is pending user approval.');
            }
            $accessToken = $this->repo->generateAccessToken($this->clientId, $device->scope, $device->userId);
            $refreshToken = $this->repo->generateRefreshToken($this->clientId, $device->scope, $device->userId);
            $this->app->response->setStatus(HttpStatus::OK);
            return Oauth2Response::success($accessToken->token, $accessToken->expiresIn, $refreshToken->token, $accessToken->scope);
        }
    }

}
