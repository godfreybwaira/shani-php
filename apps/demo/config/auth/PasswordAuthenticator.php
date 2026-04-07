<?php

/**
 * Description of PasswordAuthenticator
 * @author goddy
 *
 * Created on: Apr 7, 2026 at 12:32:00 PM
 */

namespace apps\demo\config\auth {

    use shani\authentication\AuthenticationStrategy;
    use shani\authentication\UserDetailsDto;
    use shani\documentation\scanners\Endpoints;
    use shani\http\App;

    final class PasswordAuthenticator implements AuthenticationStrategy
    {

        private readonly App $app;

        public function __construct(App $app)
        {
            $this->app = $app;
        }

        public function authenticate(): ?UserDetailsDto
        {
            $permissions = Endpoints::digest($this->app->request->method, $this->app->request->route());
            return new UserDetailsDto('no' . rand(10, 100), $permissions['hash'], false);
        }
    }

}
