<?php

/**
 * Description of AppPresets
 * @author goddy
 *
 * Created on: Apr 24, 2026 at 4:41:24 PM
 */

namespace shani\presets {

    use shani\launcher\Framework;

    /**
     * Defines default application presets such as language, running state,
     * supported languages, application name, and log file configuration.
     *
     * This class centralizes application-level settings to ensure consistent
     * behavior across the framework. It allows customization of:
     * - Default application language
     * - Application running state
     * - Supported languages
     * - Application name
     * - Log file naming convention
     *
     * By default:
     * - Language → 'sw'
     * - Running state → true
     * - Supported languages → ['sw', 'en']
     * - Application name → Framework::NAME with Framework::VERSION
     * - Log file name → Current date in 'Y-m-d.log' format
     */
    final class AppPresets
    {

        /**
         * Default application language.
         *
         * @var string
         */
        public readonly string $language;

        /**
         * Application running state.
         *
         * @var bool
         */
        public readonly bool $isRunning;

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
         * Constructor for AppPresets.
         *
         * Initializes application configuration with defaults if none are provided.
         *
         * @param string $language
         *     Default application language. Defaults to 'sw'.
         *
         * @param bool $isRunning
         *     Application running state. Defaults to true.
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
                bool $isRunning = true,
                array $supportedLanguages = null,
                string $appName = null,
                string $logFileName = null
        )
        {
            $this->language = $language;
            $this->isRunning = $isRunning;
            $this->supportedLanguages = $supportedLanguages ?? ['sw', 'en'];
            $this->appName = $appName ?? Framework::NAME . ' v' . Framework::VERSION;
            $this->logFileName = $logFileName ?? date('Y-m-d') . '.log';
        }
    }

}
