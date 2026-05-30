<?php

/**
 * Description of JwtAuthenticator
 * @author goddy
 *
 * Created on: Apr 7, 2026 at 12:32:19 PM
 */

namespace apps\demo\v1\config\auth {

    use features\authentication\AuthenticationResult;
    use features\authentication\AuthenticationStrategy;
    use features\authentication\UserDetailsDto;
    use features\jwt\JWTAlgorithm;
    use features\jwt\JWTClaim;
    use features\utils\Duration;
    use features\utils\URI;
    use shani\launcher\App;

    final class JwtAuthenticator implements AuthenticationStrategy
    {

        private readonly App $app;

        public function __construct(App $app)
        {
            $this->app = $app;
        }

        public function login(): ?AuthenticationResult
        {
            $subject = 'id' . rand(10, 1000);
            $token = $this->app->request->header()->getBearerToken();
            if (empty($token)) {
                return null;
            }
            $publicKey = '-----BEGIN PUBLIC KEY-----' . PHP_EOL . 'MCowBQYDK2VwAyEAKaA5Dmite1sGhNXMfFZPpGarV7/Yg63Fplolwbw7Pn0=';
            $publicKey .= PHP_EOL . '-----END PUBLIC KEY-----';
            $claim = JWTClaim::fromToken($token, $publicKey, JWTAlgorithm::EdDSA);
            $access = $claim->payload?->getOne('access');
            $user = new UserDetailsDto($subject, $access, false, '79a7ac18440680f461b', '16e9a5ecb65264ebbfd');
            return new AuthenticationResult($user, rememberUser: false);
        }

        public function register(): ?UserDetailsDto
        {
            return null;
        }

        public function update(): ?UserDetailsDto
        {
            return null;
        }

        public function unregister(): bool
        {
            return true;
        }

        public function logout(): bool
        {
            return true;
        }
    }

}
