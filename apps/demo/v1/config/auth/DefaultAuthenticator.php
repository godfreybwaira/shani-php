<?php

namespace apps\demo\v1\config\auth {

    use features\authentication\AuthenticationResult;
    use features\authentication\AuthenticationStrategy;
    use features\authentication\UserDetailsDto;
    use shani\launcher\App;

    final class DefaultAuthenticator implements AuthenticationStrategy
    {

        private readonly App $app;

        public function __construct(App $app)
        {
            $this->app = $app;
        }

        #[\override]
        public function login(): ?AuthenticationResult
        {
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
