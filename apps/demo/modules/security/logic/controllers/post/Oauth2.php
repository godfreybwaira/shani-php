<?php

/**
 * Description of Oauth2
 * @author goddy
 *
 * Created on: Mar 18, 2026 at 10:52:52 AM
 */

namespace apps\demo\modules\security\logic\controllers\post {

    use features\authentication\UserDetailsDto;
    use features\oauth2\OAuth2TokenAuthorizer;
    use features\oauth2\OAuth2TokenIssuer;
    use shani\launcher\App;

    final class Oauth2
    {

        private readonly App $app;

        public function __construct(App $app)
        {
            $this->app = $app;
        }

        public function token(): \JsonSerializable
        {
            $issuer = new OAuth2TokenIssuer($this->app);
            $response = $issuer->handleRequest();
            return $response->body;
        }

        public function authorize(): ?\JsonSerializable
        {
            $authorizer = new OAuth2TokenAuthorizer($this->app);
            $response = $authorizer->handleGeneralAuthorization();
            return $response?->body;
        }

        public function deviceAuthorization(): ?\JsonSerializable
        {
            $authorizer = new OAuth2TokenAuthorizer($this->app);
            $response = $authorizer->handleDeviceAuthorization();
            return $response?->body;
        }

        public function device(): ?\JsonSerializable
        {
            $authorizer = new OAuth2TokenAuthorizer($this->app);
            $body = $this->app->request->body();
            $userCode = $body->getOne('user_code');
            $deviceCode = $body->getOne('device_code');
            $response = $authorizer->handleDeviceVerification($userCode, $deviceCode);
            return $response?->body;
        }

        public function login(): ?UserDetailsDto
        {
            return $this->app->auth->login();
        }
    }

}
