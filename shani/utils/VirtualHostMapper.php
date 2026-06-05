<?php

/**
 * Description of VirtualHostMapper
 * @author goddy
 *
 * @since May 8, 2026 at 9:49:51 AM
 */

namespace shani\utils {

    final class VirtualHostMapper
    {

        public readonly string $projectName;
        public readonly string $appStorage;
        public readonly string $privateBucket;
        public readonly string $publicBucket;
        public readonly string $defaultVersion;
        public readonly string $cgiDirectory;
        public readonly string $requestHeader;
        public readonly string $responseHeader;
        public readonly array $supportedVersions;

        public function __construct(
                string $projectName, string $appStorage, string $privateBucket,
                string $publicBucket, string $defaultVersion, string $cgiDirectory,
                string $requestHeader, string $responseHeader, array $supportedVersions
        )
        {
            $this->projectName = $projectName;
            $this->appStorage = $appStorage;
            $this->privateBucket = $privateBucket;
            $this->publicBucket = $publicBucket;
            $this->defaultVersion = $defaultVersion;
            $this->cgiDirectory = $cgiDirectory;
            $this->requestHeader = $requestHeader;
            $this->responseHeader = $responseHeader;
            $this->supportedVersions = $supportedVersions;
        }

        public static function fromArray(array $host): VirtualHostMapper
        {
            $version = $host['version'];
            return new VirtualHostMapper(
                    $host['project_name'], $host['app_storage'],
                    $host['buckets']['private'], $host['buckets']['public'],
                    $version['default'], $host['cgi_directory'], $version['request_header'],
                    $version['response_header'], $version['supported']
            );
        }

        public function getConfigFileName(string $versionNumber): ?string
        {
            return $this->supportedVersions[$versionNumber]['config'] ?? null;
        }
    }

}