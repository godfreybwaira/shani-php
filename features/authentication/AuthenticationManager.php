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

        private const CART_NAME = '_4u7h_U53r!';

        public function __construct(App $app, AuthenticationStrategy ...$strategies)
        {
            $this->app = $app;
            $this->strategies = $strategies;
        }

        /**
         * Let user try logging in...
         * @return bool True on success, false otherwise.
         */
        public function login(): bool
        {
            if ($this->isAuthenticated()) {
                return true;
            }
            foreach ($this->strategies as $strategy) {
                $user = $strategy->authenticate();
                if ($user === null) {
                    continue;
                }
                if ($user->isDisabled) {
                    break;
                }
                $this->app->session->cart(self::CART_NAME)->addAll([
                    'permissions' => $user->permissions,
                    'id' => $user->id
                ]);
                $this->app->session->refresh();
                return true;
            }
            return false;
        }

        /**
         * Get all permissions of an authenticated user
         * @return string|null A string of permissions or null if a user has no permission
         */
        public function getPermissions(): ?string
        {
            if ($this->isAuthenticated()) {
                return $this->app->session->cart(self::CART_NAME)->getOne('permissions');
            }
            return null;
        }

        /**
         * Check if the current user is authenticated.
         * @return bool True if authenticated, false otherwise
         */
        private function isAuthenticated(): bool
        {
            return $this->app->session->cartExists(self::CART_NAME);
        }

        /**
         * Get authenticated user id
         * @return string|null User id on success, null otherwise.
         */
        public function getUserId(): ?string
        {
            if ($this->isAuthenticated()) {
                return $this->app->session->cart(self::CART_NAME)->getOne('id');
            }
            return null;
        }
    }

}
