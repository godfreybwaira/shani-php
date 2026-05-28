<?php

/**
 * Description of AuthenticationCheck
 * @author goddy
 *
 * Created on: May 18, 2026 at 9:11:41 AM
 */

namespace features\attributes\security {

    use features\exceptions\client\AuthenticationException;
    use shani\contracts\AttributeInterface;
    use shani\launcher\App;

    /**
     * Check if current application user is logged in successfully (Authenticated).
     */
    #[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
    final class AuthenticationCheck implements AttributeInterface
    {

        private readonly bool $exempted;

        public function __construct(bool $exempted = false)
        {
            $this->exempted = $exempted;
        }

        #[\Override]
        public function execute(App $app): void
        {
            if ($this->exempted || $app->config->authenticationConfig()->skipAuthentication) {
                return;
            }
            if (!$app->auth->attemptAuthentication()) {
                throw new AuthenticationException('Not authenticated. Please login first.');
            }
        }
    }

}
