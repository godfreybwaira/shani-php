<?php

/**
 * Description of CsrfCheck
 * @author goddy
 *
 * Created on: May 18, 2026 at 9:11:41 AM
 */

namespace features\attributes {

    use features\exceptions\CustomException;
    use shani\contracts\AttributeInterface;
    use shani\launcher\App;

    /**
     *
     * Block incoming CSRF attacks. All attacks coming via HTTP GET request will
     * be discarded. User must make sure not submitting sensitive information
     * via GET request
     */
    #[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
    final class CsrfCheck implements AttributeInterface
    {

        public readonly bool $exempted;

        public function __construct(bool $exempted)
        {
            $this->exempted = $exempted;
        }

        #[\Override]
        public function execute(App $app): void
        {
            $csrf = $app->config->csrfConfig();
            if ($this->exempted || $csrf->skipTest) {
                return;
            }
            $expectedToken = $app->csrfToken()->getOne($csrf->tokenName);
            $submittedToken = $app->request->header()->getOne($csrf->tokenName) ?? $app->request->body()->getOne($csrf->tokenName);
            if (empty($submittedToken) || !hash_equals($expectedToken, $submittedToken)) {
                throw CustomException::notAcceptable($app, 'Invalid or missing CSRF token');
            }
        }

        public static function protect(App $app): void
        {
            (new self(exempted: false))->execute($app);
        }
    }

}
