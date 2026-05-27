<?php

/**
 * Description of BasicAuthenticator
 * @author goddy
 *
 * Created on: Apr 7, 2026 at 12:32:00 PM
 */

namespace apps\demo\v1\config\auth {

    use features\authentication\AuthenticationResult;
    use features\authentication\AuthenticationStrategy;
    use features\authentication\UserDetailsDto;
    use shani\launcher\App;

    final class BasicAuthenticator implements AuthenticationStrategy
    {

        private readonly App $app;

        public function __construct(App $app)
        {
            $this->app = $app;
        }

        public function login(): ?AuthenticationResult
        {
            $credentials = $this->app->request->header()->getBasicAuth();
            if (empty($credentials)) {
                return null;
            }
            if (hash_equals('38a79817-6f70-400b-90d4-8d1912dd8b89', $credentials[1]) && hash_equals('client101', $credentials[0])) {
                $user = new UserDetailsDto('id' . rand(10, 1000), '24354fed,5ca2536e', false, '79a7ac18440680f461b', '16e9a5ecb65264ebbfd');
                return new AuthenticationResult($user, rememberUser: true);
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
