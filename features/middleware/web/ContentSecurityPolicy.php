<?php

/**
 * Description of ContentSecurityPolicy
 * @author coder
 *
 * Created on: Apr 11, 2025 at 4:43:09 PM
 */

namespace features\middleware\web {

    use features\utils\Duration;
    use shani\http\HttpHeader;
    use shani\launcher\App;

    enum ContentSecurityPolicy: string
    {

        /**
         * Block Click-jacking attack, upgrade insecure requests, block embedding
         * this application on other domains
         */
        case BASIC = "base-uri 'self';upgrade-insecure-requests;frame-ancestors 'self'";

        /**
         * Disable CSP headers (Not recommended)
         */
        case DISABLE = '';

        /**
         * Adding basic Content-Security-Policy (CSP) header values
         * @return void
         */
        public function addCspHeaders(App $app): void
        {
            if ($this === self::DISABLE) {
                return;
            }
            $app->response->header()->addIfAbsent(HttpHeader::X_FRAME_OPTIONS, 'sameorigin');
            $app->response->header()->addIfAbsent(HttpHeader::CONTENT_SECURITY_POLICY, $this->value);
            if ($app->request->uri->secure()) {
                $duration = Duration::of(2, Duration::YEARS)->getTimestamp() - time();
                $hsts = 'max-age=' . $duration . ';includeSubDomains;preload';
                $app->response->header()->addIfAbsent(HttpHeader::STRICT_TRANSPORT_SECURITY, $hsts);
            }
        }
    }

}
