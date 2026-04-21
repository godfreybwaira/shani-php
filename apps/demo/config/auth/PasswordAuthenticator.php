<?php

/**
 * Description of PasswordAuthenticator
 * @author goddy
 *
 * Created on: Apr 7, 2026 at 12:32:00 PM
 */

namespace apps\demo\config\auth {

    use features\authentication\AuthenticationStrategy;
    use features\authentication\UserDetailsDto;
    use shani\launcher\App;

    final class PasswordAuthenticator implements AuthenticationStrategy
    {

        private readonly App $app;

        public function __construct(App $app)
        {
            $this->app = $app;
        }

        public function login(): ?UserDetailsDto
        {
            return new UserDetailsDto('no' . rand(10, 100), '430704a766', false);
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
