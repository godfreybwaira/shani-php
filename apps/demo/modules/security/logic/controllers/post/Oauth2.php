<?php

/**
 * Description of Oauth2
 * @author goddy
 *
 * Created on: Mar 18, 2026 at 10:52:52 AM
 */

namespace apps\demo\modules\security\logic\controllers\post {

    use lib\oauth2\OAuth2TokenAuthorizer;
    use lib\oauth2\OAuth2TokenIssuer;
    use shani\http\App;

    final class Oauth2
    {

        private readonly App $app;

        public function __construct(App $app)
        {
            $this->app = $app;
        }

        public function token()
        {
            $issuer = new OAuth2TokenIssuer($this->app);
            $response = $issuer->handleRequest();
            $this->app->writer->send($response->body);
        }

        public function authorize()
        {
            $authorizer = new OAuth2TokenAuthorizer($this->app);
            $response = $authorizer->handleGeneralAuthorization();
            $this->app->writer->send($response?->body);
        }

        public function deviceAuthorization()
        {
            $authorizer = new OAuth2TokenAuthorizer($this->app);
            $response = $authorizer->handleDeviceAuthorization();
            $this->app->writer->send($response?->body);
        }

        public function device()
        {
            $authorizer = new OAuth2TokenAuthorizer($this->app);
            $body = $this->app->request->body();
            $userCode = $body->getOne('user_code');
            $deviceCode = $body->getOne('device_code');
            $response = $authorizer->handleDeviceVerification($userCode, $deviceCode);
            $this->app->writer->send($response?->body);
        }
    }

}
