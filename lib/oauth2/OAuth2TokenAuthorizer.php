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
        public function handleRequest(): ?Oauth2Response
        {
            $keys = $this->body->absentKeys(['client_id', 'redirect_uri', 'response_type']);
            $this->app->response->setStatus(HttpStatus::BAD_REQUEST);
            if ($keys !== null) {
                return Oauth2Response::error(Oauth2Error::INVALID_REQUEST, 'The following parameters were required but missing: ' . implode(', ', $keys));
            }
            $clientId = $this->body->getOne('client_id');
            $redirectUri = $this->body->getOne('redirect_uri');
            $responseType = $this->body->getOne('response_type');
            $scope = $this->body->getOne('scope');
            $codeChallengeMethod = $this->body->getOne('code_challenge_method');
            $codeChallenge = $this->body->getOne('code_challenge');
            $requireSecret = $codeChallenge !== null;
            if ($responseType !== 'code') {
                return Oauth2Response::error(Oauth2Error::UNSUPPORTED_RESPONSE_TYPE, 'Supported response_type is `code`.');
            }
            $client = $this->repo->getClientDetails($clientId, null, $redirectUri, $requireSecret);
            if ($client === null || $redirectUri !== $client->redirectUri) {
                return Oauth2Response::error(Oauth2Error::INVALID_CLIENT, 'Client authentication failed.');
            }
            if ($requireSecret && $codeChallengeMethod !== 'S256') {
                return Oauth2Response::error(Oauth2Error::INVALID_REQUEST, 'Invalid code challenge method.');
            }
            $userId = $this->app->config->userPrivateId(); //get session user id
            $accessToken = $this->repo->generateAuthorizationCode($clientId, $scope, $userId, $redirectUri, $codeChallenge, $codeChallengeMethod);
            $query = http_build_query([$responseType => $accessToken->token]);
            $this->app->response->redirect($redirectUri . '?' . $query);
            return null;
        }
    }

}
