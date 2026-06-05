<?php

/**
 * Description of RequestPreference
 * @author goddy
 *
 * @since Apr 17, 2026 at 5:17:11 PM
 */

namespace shani\utils {

    use features\ds\map\ReadMap;
    use shani\launcher\Framework;

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
         * Requested application version number.
         *
         * @var string
         */
        public readonly string $versionNumber;

        /**
         * Virtual host configurations parsed from YAML.
         *
         * @var ReadMap
         */
        public readonly ReadMap $vhost;

        /**
         * Host configuration from host file.
         * @var VirtualHostMapper
         */
        public readonly VirtualHostMapper $mapper;

        /**
         * Constructor for RequestPreference.
         *
         * @param string $selectedVersion
         *     Requested application version.
         *
         * @param VirtualHostMapper $mapper Host configuration from host file.
         *
         * @param string $hostName Host name.
         *
         */
        public function __construct(string $selectedVersion, VirtualHostMapper $mapper, string $hostName)
        {
            $configFile = Framework::DIR_HOSTS . DIRECTORY_SEPARATOR . $hostName;
            $configFile .= DIRECTORY_SEPARATOR . $mapper->supportedVersions[$selectedVersion]['config'];
            $this->versionNumber = $selectedVersion;
            $configs = yaml_parse_file($configFile);
            $this->vhost = new ReadMap($configs);
            $this->mapper = $mapper;
        }
    }

}
