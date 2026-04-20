<?php

/**
 * Description of AuthenticationService
 * @author goddy
 *
 * Created on: Apr 7, 2026 at 9:14:26 AM
 */

namespace features\authentication {

    use shani\launcher\App;

    final class AuthenticationService
    {

        private const CART_NAME = '_4u7h_U53r!';

        private readonly App $app;

        /**
         *
         * @param App $app Application object
         */
        public function __construct(App $app)
        {
            $this->app = $app;
        }

        /**
         * Let user try logging in...
         * @return UserDetailsDto|null User details on success, null otherwise
         */
        private function login(): ?UserDetailsDto
        {
            $strategies = $this->app->config->getAuthenticationStrategies();
            foreach ($strategies as $strategy) {
                $user = $strategy->authenticate();
                if ($user === null) {
                    continue;
                }
                if ($user->isDisabled) {
                    break;
                }
                $this->app->session->cart(self::CART_NAME)->addAll([
                    'permissions' => $user->permissions, 'id' => $user->id,
                    'disabled' => $user->isDisabled
                ]);
                $this->app->session->refresh();
                return $user;
            }
            return null;
        }

        public function register(): ?UserDetailsDto
        {
            $strategies = $this->app->config->getAuthenticationStrategies();
            foreach ($strategies as $strategy) {
                $user = $strategy->register();
                if ($user !== null) {
                    return $user;
                }
            }
            return null;
        }

        public function update(): ?UserDetailsDto
        {
            $strategies = $this->app->config->getAuthenticationStrategies();
            foreach ($strategies as $strategy) {
                $user = $strategy->update();
                if ($user !== null) {
                    return $user;
                }
            }
            return null;
        }

        /**
         * Destroy user session and return to home page.
         * @return void
         */
        public function logout(): void
        {
            $this->app->session->destroy();
            $this->app->response->redirect($this->app->config->homePath());
            $this->app->writer->send();
        }

        /**
         * Get authenticated user details
         * @return UserDetailsDto|null User details if user is found, null otherwise.
         */
        public function getSessionUserDetails(): ?UserDetailsDto
        {
            if ($this->isAuthenticated()) {
                $cart = $this->app->session->cart(self::CART_NAME);
                return new UserDetailsDto($cart->getOne('id'), $cart->getOne('permissions'), $cart->getOne('disabled'));
            }
            return $this->login();
        }

        /**
         * Whether the current user is authenticated.
         * @var bool True if authenticated, false otherwise
         */
        private function isAuthenticated(): bool
        {
            return $this->app->session->cartExists(self::CART_NAME);
        }
    }

}
