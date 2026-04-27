<?php

/**
 * Description of StaticAssetProperties
 * @author goddy
 *
 * Created on: Apr 26, 2026 at 5:06:34 PM
 */

namespace features\assets {

    use features\authentication\UserDetailsDto;
    use features\utils\MediaType;
    use shani\config\PathConfig;
    use shani\http\enums\HttpStatus;
    use shani\http\FileOutputStream;
    use shani\http\HttpCache;
    use shani\http\HttpHeader;
    use shani\http\HttpResponse;
    use shani\launcher\App;
    use shani\launcher\Framework;

    /**
     * Represents a request for a static asset.
     *
     * This class encapsulates the URI path, bucket, filename, and access type
     * of a static asset request. It also provides methods to handle the request
     * and serve the asset via different server configurations (Apache, Nginx, Shani).
     */
    final class StaticAssetRequest
    {

        /** @var string The URI path of the requested asset */
        public readonly string $uriPath;

        /** @var string The bucket identifier (private, protected, or public) */
        public readonly string $bucket;

        /** @var string The filename of the requested asset */
        public readonly string $filename;

        /** @var StaticAssetAccessType The access type of the asset */
        public readonly StaticAssetAccessType $accessType;

        /**
         * Constructor.
         *
         * @param string                $uriPath   The URI path of the asset.
         * @param string                $bucket    The bucket identifier.
         * @param StaticAssetAccessType $accessType The access type of the asset.
         */
        private function __construct(string $uriPath, string $bucket, StaticAssetAccessType $accessType)
        {
            $this->bucket = $bucket;
            $this->uriPath = $uriPath;
            $this->accessType = $accessType;
            $this->filename = basename($uriPath);
        }

        /**
         * Creates a StaticAssetRequest from a given URI path and configuration.
         *
         * @param PathConfig $config   Path configuration containing bucket mappings.
         * @param string     $uriPath  The URI path of the asset.
         *
         * @return StaticAssetRequest|null The created request, or null if invalid.
         */
        public static function fromPath(PathConfig $config, string $uriPath): ?StaticAssetRequest
        {
            $values = explode('/', trim($uriPath, '/'));
            return match ('/' . $values[0]) {
                $config->privateBucket => new StaticAssetRequest($uriPath, $values[0], StaticAssetAccessType::PRIVATE_ACCESS),
                $config->protectedBucket => new StaticAssetRequest($uriPath, $values[0], StaticAssetAccessType::PROTECTED_ACCESS),
                $config->publicBucket => new StaticAssetRequest($uriPath, $values[0], StaticAssetAccessType::PUBLIC_ACCESS),
                default => null
            };
        }

        /**
         * Handles the request by resolving the file path and serving the asset.
         *
         * @param App $app The application context.
         *
         * @return HttpResponse|null The HTTP response, or null if not served.
         */
        public function handleRequest(App $app): ?HttpResponse
        {
            $filepath = match ($this->accessType) {
                StaticAssetAccessType::PUBLIC_ACCESS => Framework::DIR_ASSETS . substr($this->uriPath, strlen($this->bucket) + 1),
                default => $app->storage->pathTo($this->uriPath)
            };
            return $this->sendFile($app, $filepath);
        }

        /**
         * Sends the file to the client with appropriate headers and caching.
         *
         * @param App    $app      The application context.
         * @param string $filepath The resolved file path.
         *
         * @return HttpResponse|null The HTTP response, or null if not served.
         */
        private function sendFile(App $app, string $filepath): ?HttpResponse
        {
            $assetServer = $app->config->getStaticAssetServer();
            $etag = md5($this->filename);

            if ($app->request->header()->getOne(HttpHeader::IF_NONE_MATCH) === $etag) {
                $app->response->setStatus(HttpStatus::NOT_MODIFIED);
                return null;
            }

            $cache = (new HttpCache())->setEtag($etag);
            $app->response->setStatus(HttpStatus::OK)->setCache($cache);

            return match ($assetServer) {
                StaticAssetServers::APACHE => self::delegateToApache($app, $filepath),
                StaticAssetServers::NGINX => self::delegateToNginx($app, $this->uriPath),
                StaticAssetServers::SHANI => self::delegateToShani($filepath),
                default => null
            };
        }

        /**
         * Determines if the given user has access to the requested asset.
         *
         * @param UserDetailsDto $user The user details.
         * @param string $filename The filename of the requested asset
         *
         * @return bool True if the user has access, false otherwise.
         */
        public static function hasAccess(UserDetailsDto $user, string $filename): bool
        {
            return (new StaticAssetOwnership($filename))->hasAccess($user);
        }

        /**
         * Serve static assets using this framework.
         *
         * @param string $filepath File path.
         *
         * @return HttpResponse The HTTP response.
         */
        private static function delegateToShani(string $filepath): HttpResponse
        {
            return HttpResponse::withBody(new FileOutputStream($filepath));
        }

        /**
         * Serve static assets using Nginx server.
         *
         * @param App    $app      Application object.
         * @param string $filepath File path.
         *
         * @return HttpResponse|null The HTTP response, or null if delegated.
         */
        private static function delegateToNginx(App $app, string $filepath): ?HttpResponse
        {
            $app->response->header()->addAll([
                'X-Accel-Redirect' => $filepath,
                HttpHeader::CONTENT_TYPE => MediaType::fromFilename($filepath)
            ]);
            return null;
        }

        /**
         * Serve static assets using Apache server.
         *
         * @param App    $app      Application object.
         * @param string $filepath File path.
         *
         * @return HttpResponse|null The HTTP response, or null if delegated.
         */
        private static function delegateToApache(App $app, string $filepath): ?HttpResponse
        {
            $app->response->header()->addAll([
                'X-Sendfile' => $app->storage->pathTo($filepath),
                HttpHeader::CONTENT_TYPE => MediaType::fromFilename($filepath)
            ]);
            return null;
        }

        /**
         * Get asset real path
         * @param string $path asset location relative to asset directory
         * @return string real path pointing to asset
         */
        public static function assetPath(string $path): string
        {
            return Framework::DIR_ASSETS . $path;
        }
    }

}
