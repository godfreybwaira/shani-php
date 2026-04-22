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
            $credentials = $this->app->request->header()->getBasicAuth();
            if (empty($credentials)) {
                return null;
            }
            if (hash_equals('38a79817-6f70-400b-90d4-8d1912dd8b89', $credentials[1]) && hash_equals('client101', $credentials[0])) {
                return new UserDetailsDto('no' . rand(10, 100), '430704a766', false);
            }
            return null;
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
