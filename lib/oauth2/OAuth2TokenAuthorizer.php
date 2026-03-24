<?php

/**
 * Description of OAuth2TokenAuthorizer
 * @author goddy
 *
 * Created on: Mar 9, 2026 at 11:25:31 AM
 */

namespace lib\oauth2 {

    use lib\ds\map\ReadableMap;
    use lib\http\HttpStatus;
    use shani\http\App;

    final class OAuth2TokenAuthorizer
    {

        private readonly App $app;
        private readonly ReadableMap $body;
        private readonly Oauth2Repository $repo;

        public function __construct(App $app)
        {
            $this->app = $app;
            $this->body = $app->request->query;
            $this->repo = $app->config->getOauth2Repository();
        }

        /**
         * Handles incoming requests and routes to appropriate oauth handler method.
         * @return Oauth2Response|null Returns Oauth 2 response on failure, or null on success
         */
        public function handleGeneralAuthorization(): ?Oauth2Response
        {
            $keys = $this->body->absentKeys(['client_id', 'redirect_uri', 'response_type']);
            $this->app->response->setStatus(HttpStatus::BAD_REQUEST);
            if ($keys !== null) {
                return Oauth2Response::error(Oauth2Error::INVALID_REQUEST, 'Missing required parameter(s): ' . implode(', ', $keys));
            }
            $clientId = $this->body->getOne('client_id');
            $redirectUri = $this->body->getOne('redirect_uri');
            $responseType = $this->body->getOne('response_type');
            $scope = $this->body->getOne('scope');
            $codeChallengeMethod = $this->body->getOne('code_challenge_method');
            $codeChallenge = $this->body->getOne('code_challenge');
            if ($responseType !== 'code') {
                return Oauth2Response::error(Oauth2Error::UNSUPPORTED_RESPONSE_TYPE, 'Supported response_type is `code`.');
            }
            $client = $this->repo->getClientDetails(null, $this->app->request->ip, $clientId);
            if ($client === null || $redirectUri !== $client->redirectUri) {
                return Oauth2Response::error(Oauth2Error::INVALID_CLIENT, 'Client authentication failed.');
            }
            if ($codeChallenge !== null && $codeChallengeMethod !== 'S256') {
                return Oauth2Response::error(Oauth2Error::INVALID_REQUEST, 'Invalid code challenge method.');
            }
            $userId = $this->app->config->getUserPrivateId();
            if ($userId === null) {
                return Oauth2Response::error(Oauth2Error::INVALID_REQUEST, 'Granting user is not authenticated.');
            }
            $authCode = $this->repo->generateAuthorizationCode($clientId, $scope, $userId, $redirectUri, $codeChallenge, $codeChallengeMethod);
            $query = http_build_query([$responseType => $authCode->token]);
            $this->app->response->setStatus(HttpStatus::OK);
            $this->app->response->redirect($redirectUri . '?' . $query);
            return null;
        }

        /**
         * Designed for devices that either lack a browser to perform a user-agent-based
         * authorization or are input constrained to the extent that requiring the user
         * to input text in order to authenticate during the authorization flow is impractical.
         * It enables OAuth clients on such devices (like smart TVs, media consoles,
         * digital picture frames, and printers) to obtain user authorization to access
         * protected resources by using a user agent on a separate device.
         * @return Oauth2Response|null Returns Oauth 2 response on failure, or null on success
         */
        public function handleDeviceAuthorization(): ?Oauth2Response
        {
            $clientId = $this->body->getOne('client_id');
            $this->app->response->setStatus(HttpStatus::BAD_REQUEST);
            if ($clientId === null) {
                return Oauth2Response::error(Oauth2Error::INVALID_REQUEST, 'Missing required parameter: client_id');
            }
            $scope = $this->body->getOne('scope');
            $clientSecret = $this->body->getOne('client_secret');
            $client = $this->repo->getClientDetails(null, $this->app->request->ip, $clientId, $clientSecret);
            if ($client === null) {
                return Oauth2Response::error(Oauth2Error::INVALID_CLIENT, 'Client authentication failed.');
            }
            $device = $this->repo->generateDeviceCode($clientId, $scope, self::generateUserCode());
            $this->app->response->setStatus(HttpStatus::OK);
            return Oauth2Response::deviceSuccess($device->deviceCode, $device->userCode, $device->verificationUri, $device->expiresIn, $device->pollingInterval);
        }

        /**
         * Generates a user-friendly user code (e.g., 8 uppercase letters/numbers).
         *
         * @param int $length Number of characters to generate
         * @return string User code.
         */
        private static function generateUserCode(int $length = 8): string
        {
            $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
            return $code;
        }
    }

}
