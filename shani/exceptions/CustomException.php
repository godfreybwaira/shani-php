<?php

/**
 * Description of CustomException
 * @author coder
 *
 * Created on: Apr 5, 2025 at 12:53:01 PM
 */

namespace shani\exceptions {

    use lib\http\HttpHeader;
    use lib\http\HttpStatus;
    use shani\http\App;

    final class CustomException
    {

        public static function notFound(App &$app, string $message = null): ClientException
        {
            $app->response->setStatus(HttpStatus::NOT_FOUND);
            return new ClientException($message ?? 'File or resource not found');
        }

        public static function badRequest(App &$app, string $message = null): ClientException
        {
            $app->response->setStatus(HttpStatus::BAD_REQUEST);
            return new ClientException($message ?? 'Malformed request');
        }

        public static function methodNotAllowed(App &$app, string $message = null): ClientException
        {
            $app->response->setStatus(HttpStatus::METHOD_NOT_ALLOWED);
            $app->response->header()->addIfAbsent(HttpHeader::ACCESS_CONTROL_ALLOW_METHODS, $app->config->allowedRequestMethods());
            return new ClientException($message ?? 'Request Method not allowed');
        }

        public static function notAcceptable(App &$app, string $message = null): ClientException
        {
            $app->response->setStatus(HttpStatus::NOT_ACCEPTABLE);
            return new ClientException($message ?? 'Request not acceptable');
        }

        public static function serverError(App &$app, string $message = null): ServerException
        {
            $app->response->setStatus(HttpStatus::INTERNAL_SERVER_ERROR);
            return new ServerException($message ?? 'Could not process the request');
        }

        public static function notAuthorized(App &$app, string $message = null): ClientException
        {
            $app->response->setStatus(HttpStatus::UNAUTHORIZED);
            return new ClientException($message ?? 'Not authorized to access the resource');
        }

        public static function offline(App &$app, string $message = null): ServerException
        {
            $app->response->setStatus(HttpStatus::SERVICE_UNAVAILABLE);
            return new ServerException($message ?? 'Server is offline');
        }

        public static function sessionExpired(App &$app, string $message = null): ClientException
        {
            $app->response->setStatus(HttpStatus::BAD_REQUEST);
            return new ClientException($message ?? 'Session has expired');
        }

        public static function forbidden(App &$app, string $message = null): ClientException
        {
            $app->response->setStatus(HttpStatus::FORBIDDEN);
            return new ClientException($message ?? 'Access denied');
        }
    }

}
