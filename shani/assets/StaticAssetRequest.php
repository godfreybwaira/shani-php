<?php

/**
 * Description of StaticAssetProperties
 * @author goddy
 *
 * Created on: Apr 26, 2026 at 5:06:34 PM
 */

namespace shani\assets {

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

    final class StaticAssetRequest
    {

        public readonly string $uriPath;
        public readonly string $bucket;
        public readonly string $filename;
        public readonly StaticAssetAccessType $accessType;
        private static ?StaticAssetRequest $instance = null;

        private function __construct(string $uriPath, string $bucket, StaticAssetAccessType $accessType)
        {
            $this->bucket = $bucket;
            $this->uriPath = $uriPath;
            $this->accessType = $accessType;
            $this->filename = basename($uriPath);
        }

        public static function fromPath(PathConfig $config, string $uriPath): ?StaticAssetRequest
        {
            $values = explode('/', trim($uriPath, '/'));
            return match ('/' . $values[0]) {
                $config->privateBucket => self::createInstance($uriPath, $values[0], StaticAssetAccessType::PRIVATE_ACCESS),
                $config->protectedBucket => self::createInstance($uriPath, $values[0], StaticAssetAccessType::PROTECTED_ACCESS),
                $config->publicBucket => self::createInstance($uriPath, $values[0], StaticAssetAccessType::PUBLIC_ACCESS),
                default => null
            };
        }

        private static function createInstance(string $uriPath, string $bucket, StaticAssetAccessType $accessType): StaticAssetRequest
        {
            return self::$instance ??= new StaticAssetRequest($uriPath, $bucket, $accessType);
        }

        public function handleRequest(App $app): ?HttpResponse
        {
            $filepath = match ($this->accessType) {
                StaticAssetAccessType::PUBLIC_ACCESS => Framework::DIR_ASSETS . substr($this->uriPath, strlen($this->bucket) + 1),
                default => $app->storage->pathTo($this->uriPath)
            };
            return $this->sendFile($app, $filepath);
        }

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

        public static function isPublicResource(): bool
        {
            return self::$instance === null || self::$instance->accessType === StaticAssetAccessType::PUBLIC_ACCESS;
        }

        public static function isStaticesource(): bool
        {
            return self::$instance !== null;
        }

        public static function hasAccess(UserDetailsDto $user): bool
        {
            $ownership = new StaticAssetOwnership(self::$instance->filename);
            return $ownership->hasAccess($user);
        }

        /**
         * Serve static assets using this framework
         * @param string $filepath File path
         * @return HttpResponse
         */
        private static function delegateToShani(string $filepath): HttpResponse
        {
            return new HttpResponse(new FileOutputStream($filepath));
        }

        /**
         * Serve static assets using this Nginx server
         * @param App $app Application object
         * @param string $filepath File path
         * @return HttpResponse|null
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
         * Serve static assets using Apache server
         * @param App $app Application object
         * @param string $filepath File path
         * @return HttpResponse|null
         */
        private static function delegateToApache(App $app, string $filepath): ?HttpResponse
        {
            $app->response->header()->addAll([
                'X-Sendfile' => $app->storage->pathTo($filepath),
                HttpHeader::CONTENT_TYPE => MediaType::fromFilename($filepath)
            ]);
            return null;
        }
    }

}
