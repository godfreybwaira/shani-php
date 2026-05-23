<?php

/**
 * Description of AuthorizationCheck
 * @author goddy
 *
 * Created on: May 18, 2026 at 9:11:41 AM
 */

namespace features\attributes\security {

    use features\exceptions\CustomException;
    use shani\contracts\AttributeInterface;
    use shani\http\RequestRoute;
    use shani\launcher\App;

    /**
     *
      /**
     * Check if current application user is authorized to access the requested
     * resource. If not, then 401 HTTP error will be raised.
     */
    #[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
    final class AuthorizationCheck implements AttributeInterface
    {

        private readonly bool $exempted;

        public function __construct(bool $exempted = false)
        {
            $this->exempted = $exempted;
        }

        #[\Override]
        public function execute(App $app): void
        {
            if ($this->exempted || $app->config->authenticationConfig()->skipAuthentication || $app->config->accessingPublicResource()) {
                return;
            }
            if ($app->config->accessingGuestResource()) {
                if ($app->auth->loggedIn()) {
                    $route = RequestRoute::fromPath($app->config->pathConfig()->homePath);
                    $app->request->changeRoute($route);
                }
                return;
            }
            if (!$app->auth->attemptAuthentication()) {
                throw CustomException::authorization($app);
            }
        }
    }

}
