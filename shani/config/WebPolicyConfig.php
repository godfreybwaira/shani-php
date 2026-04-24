<?php

/**
 * Description of WebPolicyConfig
 * @author goddy
 *
 * Created on: Apr 24, 2026 at 4:18:38 PM
 */

namespace shani\config {

    use features\middleware\web\BrowsingPrivacy;
    use features\middleware\web\ContentSecurityPolicy;
    use features\middleware\web\ResourceAccessPolicy;

    /**
     * Defines default web security and privacy policies for an application.
     *
     * This class provides a convenient way to configure browser-related policies
     * that affect security and privacy. It allows customization of:
     * - Content Security Policy (CSP) headers
     * - Resource access rules (which domains can access resources)
     * - Browsing privacy via referrer header behavior
     *
     * By default:
     * - CSP is set to ContentSecurityPolicy::BASIC
     * - Resource access is limited to this domain and its subdomains
     * - Browsing privacy restricts referrer information to this domain
     */
    final class WebPolicyConfig
    {

        /**
         * Whether to send CSP (Content Security Policy) headers or not.
         *
         * @var ContentSecurityPolicy
         */
        public readonly ContentSecurityPolicy $csp;

        /**
         * Tells a web browser how to decide which domain can access resources
         * on this application.
         *
         * @var ResourceAccessPolicy
         */
        public readonly ResourceAccessPolicy $resourceAccess;

        /**
         * Tells a web browser how to send the HTTP referrer header.
         * This is important for managing user browsing privacy.
         *
         * @var BrowsingPrivacy
         */
        public readonly BrowsingPrivacy $browsingPrivacy;

        /**
         * Constructor for WebPolicyConfig.
         *
         * Initializes default web security and privacy policies if none are provided.
         *
         * @param ContentSecurityPolicy|null $csp
         *     Defines whether and how CSP headers are sent.
         *     Defaults to ContentSecurityPolicy::BASIC if null.
         *
         * @param ResourceAccessPolicy|null $resourceAccess
         *     Controls which domains can access resources from this application.
         *     Defaults to ResourceAccessPolicy::THIS_DOMAIN_AND_SUBDOMAIN if null.
         *
         * @param BrowsingPrivacy|null $browsingPrivacy
         *     Determines how the HTTP referrer header is sent, impacting user privacy.
         *     Defaults to BrowsingPrivacy::THIS_DOMAIN if null.
         */
        public function __construct(
                ContentSecurityPolicy $csp = null,
                ResourceAccessPolicy $resourceAccess = null,
                BrowsingPrivacy $browsingPrivacy = null
        )
        {
            $this->csp = $csp ?? ContentSecurityPolicy::BASIC;
            $this->resourceAccess = $resourceAccess ?? ResourceAccessPolicy::THIS_DOMAIN_AND_SUBDOMAIN;
            $this->browsingPrivacy = $browsingPrivacy ?? BrowsingPrivacy::THIS_DOMAIN;
        }
    }

}
