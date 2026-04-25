<?php

/**
 * Description of AppConfig
 * @author goddy
 *
 * Created on: Apr 24, 2026 at 4:41:24 PM
 */

namespace shani\config {

    use shani\launcher\Framework;

    /**
     * Defines default application configurations such as language,
     * supported languages, application name, and log file configuration.
     *
     * This class centralizes application-level settings to ensure consistent
     * behavior across the framework. It allows customization of:
     * - Default application language
     * - Supported languages
     * - Application name
     * - Log file naming convention
     *
     * By default:
     * - Language → 'sw'
     * - Supported languages → ['sw', 'en']
     * - Application name → Framework::NAME with Framework::VERSION
     * - Log file name → Current date in 'Y-m-d.log' format
     */
    final class AppConfig
    {

        /**
         * Default application language.
         *
         * @var string
         */
        public readonly string $language;

        /**
         * Application supported languages where a key is a language code
         * and a value is a language name.
         *
         * @var array
         */
        public readonly array $supportedLanguages;

        /**
         * User application name.
         *
         * @var string
         */
        public readonly string $appName;

        /**
         * Log file name.
         *
         * @var string
         */
        public readonly string $logFileName;

        /**
         * Constructor for AppConfig.
         *
         * Initializes application configuration with defaults if none are provided.
         *
         * @param string $language
         *     Default application language. Defaults to 'sw'.
         *
         * @param array|null $supportedLanguages
         *     Supported languages. Defaults to ['sw', 'en'] if null.
         *
         * @param string|null $appName
         *     Application name. Defaults to Framework::NAME with Framework::VERSION if null.
         *
         * @param string|null $logFileName
         *     Log file name. Defaults to current date in 'Y-m-d.log' format if null.
         */
        public function __construct(
                string $language = 'sw',
                array $supportedLanguages = null,
                string $appName = null,
                string $logFileName = null
        )
        {
            $this->language = $language;
            $this->supportedLanguages = $supportedLanguages ?? ['sw', 'en'];
            $this->appName = $appName ?? Framework::NAME . ' v' . Framework::VERSION;
            $this->logFileName = $logFileName ?? date('Y-m-d') . '.log';
        }
    }

}
