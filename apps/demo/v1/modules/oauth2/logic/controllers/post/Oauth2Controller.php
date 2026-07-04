<?php

/**
 * Description of Oauth2Controller
 * @author goddy
 *
 * @since Mar 18, 2026 at 10:52:52 AM
 */

namespace apps\demo\v1\modules\oauth2\logic\controllers\post {

    use features\attributes\security\AuthenticationCheck;
    use features\attributes\security\CsrfCheck;
    use features\attributes\security\PermissionCheck;
    use features\authentication\AuthenticationResult;
    use features\oauth2\OAuth2TokenAuthorizer;
    use features\oauth2\OAuth2TokenIssuer;
    use shani\launcher\App;

    #[AuthenticationCheck(exempted: true)]
    #[PermissionCheck(exempted: true)]
    #[CsrfCheck(exempted: true)]
    final class Oauth2Controller
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

        public function login(): ?AuthenticationResult
        {
            return $this->app->auth->login();
        }
    }

}
