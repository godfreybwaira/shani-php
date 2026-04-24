<?php

/**
 * Description of ResourceAccessPolicy
 * @author coder
 *
 * Created on: Mar 25, 2025 at 10:09:50 AM
 */

namespace features\middleware\web {

    use features\middleware\UtilityMiddlewares;
    use shani\contracts\BasicConfig;
    use shani\http\HttpHeader;
    use shani\launcher\App;

    enum ResourceAccessPolicy: string
    {

        /**
         *  allows resource access on this application from this domain only
         */
        case THIS_DOMAIN = 'same-origin';

        /**
         *  allows resource access on this application from this domain and it's subdomain
         */
        case THIS_DOMAIN_AND_SUBDOMAIN = 'same-site';

        /**
         *  allows resource access on this application from any domain (Not recommended)
         */
        case ANY_DOMAIN = 'cross-origin';

        /**
         *  Do not use resource access policy (Not recommended)
         */
        case DISABLED = '';

        /**
         * Tells a web browser whether to allow other sites to access your resources
         * @param App $app Application object
         * @return void
         */
        public function setPolicy(App $app): void
        {
            if ($this === self::DISABLED) {
                return;
            }
            $app->response->header()->addAll([
                HttpHeader::CROSS_ORIGIN_RESOURCE_POLICY => $this->value,
                HttpHeader::ACCESS_CONTROL_ALLOW_METHODS => $app->config->requestConfig()->allowedMethods
            ]);
            UtilityMiddlewares::addAllowOrigin($app);
        }
    }

}