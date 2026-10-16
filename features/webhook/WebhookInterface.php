<?php

namespace features\webhook {

    use shani\http\HttpHeader;

    /**
     * Defines the contract for webhook payload containers.
     *
     * Implementations of this interface provide access to the HTTP headers
     * and raw request payload received from a webhook source. The webhook
     * consumer can use this information for signature verification,
     * authentication, validation, and payload processing.
     *
     * Example use cases include handling callbacks from payment gateways,
     * messaging platforms, CI/CD systems, and other third-party services
     * that communicate through HTTP webhooks.
     *
     * @author Goddy
     * @since 1.0.0
     */
    interface WebhookInterface
    {

        /**
         * Returns the HTTP headers associated with the webhook request.
         *
         * The returned headers may contain metadata required for request
         * validation, authentication, signature verification, content type
         * detection, and event identification.
         *
         * @return HttpHeader The webhook request headers.
         */
        public function getHeaders(): HttpHeader;

        /**
         * Returns the raw webhook request payload.
         *
         * The payload should be returned exactly as received from the sender
         * without modification to preserve integrity for signature validation
         * and event processing.
         *
         * @return string The raw request body.
         */
        public function getPayload(): string;
    }

}