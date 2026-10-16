<?php


namespace apps\demo\v1\config\auth {

    use features\authentication\AuthenticationResult;
    use features\authentication\AuthenticationStrategy;
    use features\authentication\UserDetailsDto;
    use features\jwt\JWTAlgorithm;
    use features\jwt\JWTClaim;
    use shani\launcher\App;

    final class JwtAuthenticator implements AuthenticationStrategy
    {

        private readonly App $app;

        public function __construct(App $app)
        {
            $this->app = $app;
        }

        #[\override]
        public function login(): ?AuthenticationResult
        {
            $token = $this->app->request->header()->getBearerToken();
            if (empty($token)) {
                return null;
            }
            //TODO
            return null;
        }

        #[\override]
        public function logout(): bool
        {
            //TODO
            return true;
        }
    }

}

