<?php

namespace features\attributes\security {

    use features\assets\StaticAssetOwnership;
    use features\exceptions\client\AuthorizationException;
    use shani\contracts\AttributeInterface;
    use shani\launcher\App;

    /**
     * PermissionCheck Attribute
     *
     * Check if the current authenticated user has sufficient privileges to access
     * the given resource.
     *
     * @author     Goddy
     * @created    May 18, 2026
     */
    #[\Attribute(\Attribute::TARGET_METHOD)]
    final class PermissionCheck implements AttributeInterface
    {

        /**
         * Whether this method is exempted from permission checking
         *
         * @var bool
         */
        private readonly bool $exempted;

        /**
         * Constructor
         *
         * @param bool $exempted If true, permission check will be skipped for this method
         */
        public function __construct(bool $exempted = false)
        {
            $this->exempted = $exempted;
        }

        /**
         * Execute the permission check
         *
         * @param App $app The application instance
         * @return void
         * @throws AuthorizationException When access is denied
         */
        #[\Override]
        public function execute(App $app): void
        {
            if ($this->exempted || $app->config->accessingPublicResource() || $app->auth->accessGranted()) {
                return;
            }
            if ($app->config->accessingGuestResource()) {
                if ($app->auth->loggedIn()) {
                    $route = RequestRoute::fromPath($app->config->pathConfig()->homePath);
                    $app->request->changeRoute($route);
                }
                return;
            }

            if ($app->request->isStaticResource($app->preference->mapper)) {
                $user = $app->auth->getUserDetails();
                if (StaticAssetOwnership::hasAccess($user, $app->request->uri->path())) {
                    return;
                }
            }

            throw new AuthorizationException('Not authorized to access this resource.');
        }
    }

}