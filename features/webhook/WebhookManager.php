<?php

/**
 * Dispatches webhook requests to remote endpoints.
 *
 * This manager sends the payload and headers provided by a
 * {@see WebhookInterface} implementation using an HTTP POST request.
 * It supports configurable retry attempts and request timeouts, and
 * allows callers to determine success through an optional response callback.
 *
 * When no callback is provided, a successful dispatch is determined by
 * the receipt of a 2xx HTTP status code.
 *
 * @author Goddy
 * @since 1.0.0
 */

namespace features\webhook {

    use features\utils\HttpClient;
    use features\utils\URI;
    use shani\http\ResponseEntity;

    final class WebhookManager
    {

        /**
         * Request timeout in seconds.
         *
         * The timeout determines the maximum amount of time the HTTP client
         * will wait for a response before aborting the request.
         */
        private readonly int $timeout;

        /**
         * Maximum number of retry attempts for failed requests.
         *
         * A retry is performed when the request cannot be completed due to a
         * transient failure such as a network interruption or timeout.
         */
        private readonly int $maxRetries;

        /**
         * Webhook request being dispatched.
         *
         * Provides the payload and HTTP headers that will be sent to the
         * destination endpoint.
         */
        private readonly WebhookInterface $webhook;

        /**
         * Creates a new webhook manager.
         *
         * @param WebhookInterface $webhook The webhook to dispatch.
         * @param int $maxRetries Maximum number of retry attempts for failed requests.
         * @param int $timeout Request timeout in seconds.
         */
        public function __construct(WebhookInterface $webhook, int $maxRetries = 3, int $timeout = 10)
        {
            $this->maxRetries = $maxRetries;
            $this->timeout = $timeout;
            $this->webhook = $webhook;
        }

        /**
         * Sends the configured webhook to the specified endpoint.
         *
         * The webhook payload is transmitted as the request body and any
         * associated headers are included in the outgoing request.
         *
         * If a callback is provided, it receives the response status and
         * must return a boolean indicating whether the request should be
         * considered successful. Otherwise, success is determined by a
         * 2xx response status code.
         *
         * @param URI $url Webhook destination URL.
         * @param \Closure(\shani\http\Status):bool|null $callback Optional success evaluator.
         * @return bool True if the webhook was successfully processed; otherwise false.
         */
        public function post(URI $url, \Closure $callback = null): bool
        {
            $client = new HttpClient($url, $this->maxRetries, $this->timeout);
            $client->setBody($this->webhook->getPayload());
            $client->enableAsync($callback === null);
            $headers = $this->webhook->getHeaders();
            if (!$headers->isEmpty()) {
                $client->setHeader($headers);
            }
            $result = false;
            $client->post($url->pathInfo(), function (ResponseEntity $resp) use (&$callback, &$result) {
                $result = $callback !== null ? $callback($resp->status()) : $resp->status()->is2xx();
            });
            return $result;
        }
    }

}
