<?php

/**
 * Description of RequestPreference
 * @author goddy
 *
 * Created on: Apr 17, 2026 at 5:17:11 PM
 */

namespace shani\launcher {

    use features\ds\map\ReadableMap;

    /**
     * Represents a resolved request preference for a given application version and host.
     *
     * This class encapsulates:
     * - The requested application version
     * - The configuration file loaded for that version
     * - The virtual host configuration parsed from YAML
     * - The request header used by the client to specify version
     * - The response header sent back to indicate which version was used
     *
     * It is typically created by the ApplicationLauncher when resolving
     * host and version preferences during request handling.
     */
    final class RequestPreference
    {

        /**
         * Requested application version.
         *
         * @var string
         */
        public readonly string $appVersion;

        /**
         * Loaded configuration file path.
         *
         * @var string
         */
        public readonly string $configFile;

        /**
         * Virtual host configurations parsed from YAML.
         *
         * @var ReadableMap
         */
        public readonly ReadableMap $vhost;

        /**
         * Header the client sends to request a specific version.
         *
         * @var string
         */
        public readonly string $requestVersionHeader;

        /**
         * Header we send back to tell the client which version was actually used.
         * Set to null if you don't want to return any version header.
         *
         * @var string|null
         */
        public readonly ?string $contentVersionHeader;

        /**
         * Constructor for RequestPreference.
         *
         * @param string $appVersion
         *     Requested application version.
         *
         * @param string $configFile
         *     Path to the loaded configuration file.
         *
         * @param string $requestVersionHeader
         *     Header name used by the client to request a specific version.
         *
         * @param string|null $contentVersionHeader
         *     Header name used in the response to indicate which version was applied.
         *     Null if no version header should be returned.
         */
        public function __construct(
                string $appVersion,
                string $configFile,
                string $requestVersionHeader,
                ?string $contentVersionHeader
        )
        {
            $this->appVersion = $appVersion;
            $this->configFile = $configFile;
            $this->vhost = new ReadableMap(yaml_parse_file($configFile));
            $this->requestVersionHeader = $requestVersionHeader;
            $this->contentVersionHeader = $contentVersionHeader;
        }
    }

}
