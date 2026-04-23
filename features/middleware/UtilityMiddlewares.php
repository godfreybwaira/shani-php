<?php

/**
 * Description of UtilityMiddlewares
 * @author goddy
 *
 * Created on: Apr 23, 2026 at 1:42:44 PM
 */

namespace features\middleware {

    use features\utils\MediaType;
    use shani\http\enums\HttpStatus;
    use shani\http\HttpHeader;
    use shani\http\RequestRoute;
    use shani\launcher\App;

    final class UtilityMiddlewares
    {

        /**
         * Add Access-Control-Allow-Origin header to a response of the origin
         * header is in white-listed domain.
         * @param App $app Application object
         * @return void
         */
        public static function addAllowOrigin(App $app): void
        {
            $origin = $app->request->header()->getOne(HttpHeader::ORIGIN);
            if (!empty($origin) && $app->config->whitelistedDomain($origin)) {
                $app->response->header()->addIfAbsent(HttpHeader::ACCESS_CONTROL_ALLOW_ORIGIN, $origin);
            }
        }

        /**
         * A request sent by the browser before sending the actual request to verify
         * whether a server can process the incoming request.
         * @param App $app Application object
         * @param int $cacheTime Tells the browser to cache the preflight response
         * @return void
         */
        public static function preflightRequest(App $app, int $cacheTime = 86400): void
        {
            if ($app->request->method === 'options') {
                $app->response->setStatus(HttpStatus::NO_CONTENT)->header()->addAll([
                    HttpHeader::ACCESS_CONTROL_ALLOW_METHODS => $app->config->allowedRequestMethods(),
                    HttpHeader::ACCESS_CONTROL_ALLOW_HEADERS => $app->config->allowedRequestHeaders(),
                    HttpHeader::ACCESS_CONTROL_MAX_AGE => $cacheTime
                ]);
                UtilityMiddlewares::addAllowOrigin($app);
            }
        }

        /**
         * Set client content type priority.
         * @param App $app Application object
         * @return void
         */
        public static function setProperContentType(App $app): void
        {
            //1. extension 2. accept 3. content_type 4. default
            $ext = $app->request->route()->extension;
            if ($ext !== null) {
                $app->response->header()->addOne(HttpHeader::CONTENT_TYPE, MediaType::mime($ext));
                return;
            }
            $accepted = $app->request->header()->getOne(HttpHeader::ACCEPT, HttpHeader::CONTENT_TYPE);
            if ($accepted === '*/*' || $accepted === null) {
                $accepted = MediaType::TEXT_HTML;
            }
            $app->response->header()->addOne(HttpHeader::CONTENT_TYPE, MediaType::parse($accepted)[0]);
        }

        /**
         * Handle empty (/) url when user navigate to root domain.
         * @param App $app Application object
         * @return void
         */
        public static function handleEmptyurlPath(App $app): void
        {
            if ($app->request->uri->path() === '/') {
                $app->request->changeRoute(RequestRoute::fromPath($app->config->homePath()));
            }
        }

        /**
         * Modify response before sending to user. Example signing a response,
         * encrypting, compressing response body etc
         * @return void
         */
        public static function responseMutator(App $app): void
        {
            $app->response
                    ->compress($app->config->compressionMinSize(), $app->config->compressionLevel())
                    ->sign($app->config->signature(), $app->config->signatureHeaderName())
                    ->encrypt($app->config->encryption());
        }

        /**
         * Modify request before processing it. Example verifying signature,
         * decrypting, decompressing request body etc
         * @return void
         */
        public static function requestMutator(App $app): void
        {
            $app->request
                    ->decrypt($app->config->encryption())
                    ->verify($app->config->signature(), $app->config->signatureHeaderName())
                    ->decompress();
        }
    }

}
