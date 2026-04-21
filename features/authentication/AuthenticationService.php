<?php

/**
 * Description of AuthenticationService
 * @author goddy
 *
 * Created on: Apr 7, 2026 at 9:14:26 AM
 */

namespace features\authentication {

    use features\documentation\scanners\Endpoints;
    use shani\http\RequestRoute;
    use shani\launcher\App;

    final class AuthenticationService implements AuthenticationStrategy
    {

        private const AUTH_CART = '2d574fce7ee49c';
        private const METADATA_CART = '0d53b584a59b';

        private readonly App $app;
        private ?UserDetailsDto $user = null;

        /**
         *
         * @param App $app Application object
         */
        public function __construct(App $app)
        {
            $this->app = $app;
        }

        public function login(): ?UserDetailsDto
        {
            if ($this->isAuthenticated()) {
                $this->redirect();
                return null;
            }
            $strategies = $this->app->config->getAuthenticationStrategies();
            foreach ($strategies as $index => $strategy) {
                $user = $strategy->login();
                if ($user === null) {
                    continue;
                }
                if ($user->isDisabled) {
                    break;
                }
                $this->user = $user;
                $this->app->session->cart(self::METADATA_CART)->addOne('strategy', $index);
                $this->app->session->cart(self::AUTH_CART)->add($user);
                $this->app->session->refresh();
                $this->redirect();
                return $user;
            }
            return null;
        }

        private function redirect(): void
        {
            if (!$this->app->response->redirectBack()) {
                $this->app->response->redirect('/');
            }
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

        public function unregister(): bool
        {
            $strategies = $this->app->config->getAuthenticationStrategies();
            foreach ($strategies as $strategy) {
                if ($strategy->unregister()) {
                    return true;
                }
            }
            return false;
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

        public function logout(): bool
        {
            if ($this->isAuthenticated()) {
                $index = $this->app->session->cart(self::METADATA_CART)->getOne('strategy');
                $strategy = $this->app->config->getAuthenticationStrategies()[$index];
                if ($strategy->logout()) {
                    unset($this->user);
                    $this->app->session->destroy();
                    $this->app->response->redirect('/');
                    return true;
                }
            }
            return false;
        }

        /**
         * Get authenticated user details
         * @return UserDetailsDto|null User details if user is found, null otherwise.
         */
        public function getUserDetails(): ?UserDetailsDto
        {
            if ($this->user === null && $this->app->session->cartExists(self::AUTH_CART)) {
                $cart = $this->app->session->cart(self::AUTH_CART);
                $this->user = UserDetailsDto::fromArray($cart->toArray());
            }
            return $this->user;
        }

        /**
         * Whether the current user is logged in and authenticated.
         * @var bool True if authenticated, false otherwise
         */
        public function isAuthenticated(): bool
        {
            return $this->user !== null || $this->app->session->cartExists(self::AUTH_CART);
        }

        /**
         * Check whether a user is granted access to a resource. If not parameter
         * given, it assume the parameters from current request i.e Request method
         * and/or Request route
         * @param string $method Request method.
         * @param RequestRoute $route Request route object
         * @return bool True if a user is granted access, false otherwise.
         */
        public function isAuthorized(string $method = null, RequestRoute $route = null): bool
        {
            $permissions = $this->getUserDetails()?->permissions;
            if (!empty($permissions)) {
                $reqMethod = $method ?? $this->app->request->method;
                $reqRoute = $route ?? $this->app->request->route();
                $target = Endpoints::digest($reqMethod, $reqRoute)['hash'];
                return str_contains($permissions, $target); //preg_match('\b' . $target . '\b', $permissions) === 1;
            }
            return false;
        }

        /**
         * Get session user unique ID
         * @return string|null User unique ID if authenticated, null otherwise.
         */
        public function getUserId(): ?string
        {
            return $this->getUserDetails()?->id;
        }

        /**
         * Get user storage bucket for storing private files.
         * @return string|null Unique storage bucket name
         */
        public function getUserStorageBucket(): ?string
        {
            return $this->getUserDetails()?->storageBucket;
        }

        /**
         * Get user group storage bucket for storing group (shared) files.
         * @return string|null Unique storage bucket name
         */
        public function getGroupStorageBucket(): ?string
        {
            return $this->getUserDetails()?->groupStorageBucket;
        }
    }

}
