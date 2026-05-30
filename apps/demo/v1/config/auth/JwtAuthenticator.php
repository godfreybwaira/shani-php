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
    use features\jwt\JWTClaim;
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
//            $jwt = new JWTClaim(new URI('http://localhost'), ttl: Duration::ofMinutes(2), subject: $subject, payload: [
//                'access' => '24354fed,5ca2536e'
//            ]);
//            echo $jwt->asToken('key123');
//            exit;
            $token = $this->app->request->header()->getBearerToken();
            if (empty($token)) {
                return null;
            }
            $claim = JWTClaim::fromToken($token, 'MCowBQYDK2VwAyEAKaA5Dmite1sGhNXMfFZPpGarV7/Yg63Fplolwbw7Pn0=', \features\jwt\JWTAlgorithm::EdDSA);
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
