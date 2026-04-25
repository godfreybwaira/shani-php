<?php

/**
 * Description of AuthenticationManager
 * @author goddy
 *
 * Created on: Apr 7, 2026 at 9:14:26 AM
 */

namespace features\authentication {

    use features\documentation\scanners\Endpoints;
    use shani\http\RequestRoute;
    use shani\launcher\App;

    /**
     * Manages user authentication using configured authentication strategies.
     *
     * This class implements the AuthenticationStrategy interface and provides
     * centralized login, registration, update, and logout functionality.
     *
     * Responsibilities:
     * - Delegates authentication operations to configured strategies
     * - Stores authenticated user details in session carts
     * - Handles session metadata for tracking active strategy
     * - Provides login, register, unregister, update, and logout methods
     *
     * Notes:
     * - Multiple authentication strategies can be configured (e.g., password-based, OAuth, etc.)
     * - The first successful strategy determines the authenticated user
     * - Disabled users cannot log in
     * - Session carts are used to persist authentication state
     */
    final class AuthenticationManager implements AuthenticationStrategy
    {

        private const AUTH_CART = '2d574fke49c';
        private const METADATA_CART = '0d53b5k59b';

        /**
         * Application instance reference.
         *
         * @var App
         */
        private readonly App $app;

        /**
         * Currently authenticated user details.
         *
         * @var UserDetailsDto|null
         */
        private ?UserDetailsDto $user = null;

        /**
         * Constructor for AuthenticationManager.
         *
         * @param App $app Application object providing access to configuration and session.
         */
        public function __construct(App $app)
        {
            $this->app = $app;
        }

        public function login(): ?UserDetailsDto
        {
            if ($this->loggedIn()) {
                return null;
            }
            $strategies = $this->app->config->authenticationConfig()->authenticationStrategies;
            foreach ($strategies as $index => $strategy) {
                $user = $strategy->login();
                if ($user === null) {
                    continue;
                }
                if ($user->isDisabled) {
                    return null;
                }
                $this->user = $user;
                $this->app->session->cart(self::METADATA_CART)->addOne('strategy', $index);
                $this->app->session->cart(self::AUTH_CART)->add($user);
                $this->app->session->refresh();
                return $user;
            }
            return null;
        }

        public function register(): ?UserDetailsDto
        {
            $strategies = $this->app->config->authenticationConfig()->authenticationStrategies;
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
            $strategies = $this->app->config->authenticationConfig()->authenticationStrategies;
            foreach ($strategies as $strategy) {
                if ($strategy->unregister()) {
                    return true;
                }
            }
            return false;
        }

        public function update(): ?UserDetailsDto
        {
            $strategies = $this->app->config->authenticationConfig()->authenticationStrategies;
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
            if ($this->loggedIn()) {
                $index = $this->app->session->cart(self::METADATA_CART)->getOne('strategy');
                $strategy = $this->app->config->authenticationConfig()->authenticationStrategies[$index];
                if ($strategy->logout()) {
                    $this->user = null;
                    $this->app->session->destroy();
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
         * Tries to check authenticity of a user (user is logged in). If fails,
         * it tries to login (assuming that the current user request comes with credentials).
         * If it also fails it return false, otherwise true.
         * @var bool True if authenticated, false otherwise
         */
        public function attemptAuthentication(): bool
        {
            return $this->loggedIn() || $this->login() !== null;
        }

        /**
         * Whether the current user is logged in and authenticated.
         * @var bool True if a current user is logged in, false otherwise
         */
        public function loggedIn(): bool
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
        public function accessGranted(string $method = null, RequestRoute $route = null): bool
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
    }

}
