<?php

/**
 * Description of DigestedEndpoint
 * @author goddy
 *
 * @since May 11, 2026 at 2:52:51 PM
 */

namespace features\documentation {

    final class DigestedEndpoint
    {

        public readonly string $hash;
        public readonly string $endpoint;

        public function __construct(string $hash, string $endpoint)
        {
            $this->hash = $hash;
            $this->endpoint = $endpoint;
        }
    }

}
