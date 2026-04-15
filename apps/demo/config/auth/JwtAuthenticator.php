<?php

/**
 * Description of JwtAuthenticator
 * @author goddy
 *
 * Created on: Apr 7, 2026 at 12:32:19 PM
 */

namespace apps\demo\config\auth {

    use features\authentication\AuthenticationStrategy;
    use features\authentication\UserDetailsDto;
    use features\documentation\scanners\Endpoints;
    use shani\http\App;

    final class JwtAuthenticator implements AuthenticationStrategy
    {

        private readonly App $app;

        public function __construct(App $app)
        {
            $this->app = $app;
        }

        public function authenticate(): ?UserDetailsDto
        {
            $token = $this->app->request->header()->getBearerToken();
            $permissions = Endpoints::digest($this->app->request->method, $this->app->request->route());
            return new UserDetailsDto('no' . rand(10, 100), $permissions['hash'], false);
        }
    }

}
