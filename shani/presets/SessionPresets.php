<?php

/**
 * Description of SessionPresets
 * @author goddy
 *
 * Created on: Apr 24, 2026 at 1:12:19 PM
 */

namespace shani\presets {

    use features\session\dto\FileConnectionDto;
    use features\session\SessionConnectionInterface;
    use shani\http\enums\HttpSameSite;

    /**
     * Defines default session handling policies for cookies, lifetime, and connection.
     *
     * This class provides a convenient way to configure session behavior in a web application.
     * It allows customization of:
     * - The session connection handler (e.g., file-based, database, etc.)
     * - The session cookie name
     * - The session lifetime (in seconds)
     * - The SameSite policy for cookies (controls cross-site request behavior)
     *
     * By default:
     * - Connection uses a FileConnectionDto
     * - Session name is "sessionId"
     * - Session lifetime is 0 (ends when browser closes)
     * - Cookie SameSite policy is LAX
     */
    final class SessionPresets
    {

        /**
         * Session connection handler.
         *
         * @var SessionConnectionInterface
         */
        public readonly SessionConnectionInterface $connection;

        /**
         * Session cookie name.
         *
         * @var string
         */
        public readonly string $sessionName;

        /**
         * Session lifetime in seconds.
         * If set to zero, the session terminates as soon as the browser is closed.
         *
         * @var int
         */
        public readonly int $sessionLifetime;

        /**
         * Controls whether a cookie is sent on cross‑site requests.
         *
         * @var HttpSameSite
         */
        public readonly HttpSameSite $cookieSameSite;

        /**
         * Constructor for SessionPresets.
         *
         * Initializes session configuration with defaults if no values are provided.
         *
         * @param SessionConnectionInterface|null $connection
         *     The session connection handler. Defaults to a FileConnectionDto instance if null.
         *
         * @param string $sessionName
         *     The name of the session cookie. Defaults to 'sessionId'.
         *
         * @param int $sessionLifetime
         *     The session lifetime in seconds. If set to 0, the session ends when the browser closes.
         *
         * @param HttpSameSite|null $cookieSameSite
         *     Controls whether cookies are sent on cross-site requests.
         *     Defaults to HttpSameSite::LAX if null.
         */
        public function __construct(
                SessionConnectionInterface $connection = null,
                string $sessionName = 'sessionId',
                int $sessionLifetime = 0,
                HttpSameSite $cookieSameSite = null
        )
        {
            $this->connection = $connection ?? new FileConnectionDto();
            $this->sessionName = $sessionName;
            $this->sessionLifetime = $sessionLifetime;
            $this->cookieSameSite = $cookieSameSite ?? HttpSameSite::LAX;
        }
    }

}
