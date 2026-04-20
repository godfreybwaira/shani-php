<?php

/**
 * Description of AuthenticationManager
 * @author goddy
 *
 * Created on: Apr 7, 2026 at 9:14:26 AM
 */

namespace features\authentication {

    use shani\launcher\App;

    final class AuthenticationManager
    {

        /**
         * Supported authentication strategies.
         * @var array<AuthenticationStrategy>
         */
        private readonly array $strategies;
        private readonly App $app;

        /**
         *
         * @param App $app Application object
         */
        public function __construct(App $app)
        {

            $this->app = $app;
            $this->strategies = $app->config->getAuthenticationStrategies();
        }

        /**
         * Let user try logging in...
         * @return UserDetailsDto|null User details on success, null otherwise
         */
        public function login(): ?UserDetailsDto
        {
            foreach ($this->strategies as $strategy) {
                $user = $strategy->authenticate();
                if ($user === null) {
                    continue;
                }
                if ($user->isDisabled) {
                    break;
                }
                $this->app->session->refresh();
                return $user;
            }
            return null;
        }
    }

}
