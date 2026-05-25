<?php

namespace features\attributes\security {

    use features\assets\StaticAssetOwnership;
    use features\exceptions\client\AccessGrantException;
    use shani\contracts\AttributeInterface;
    use shani\launcher\App;

    /**
     * PermissionCheck Attribute
     *
     * Used to control access to controller methods based on user authentication
     * and permission rules. Can be applied to individual methods.
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
         * @throws AccessGrantException When access is denied
         */
        #[\Override]
        public function execute(App $app): void
        {
            if ($this->exempted || $app->auth->accessGranted()) {
                return;
            }

            if ($app->request->isStaticResource($app->preference->mapper)) {
                $user = $app->auth->getUserDetails();
                if (StaticAssetOwnership::hasAccess($user, $app->request->uri->path())) {
                    return;
                }
            }

            throw new AccessGrantException('Access Denied');
        }
    }

}