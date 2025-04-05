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

        public static function notFound(App &$app): ClientException
        {
            $app->response->setStatus(HttpStatus::NOT_FOUND);
            return new ClientException('File or resource not found');
        }

        public static function badRequest(App &$app): ClientException
        {
            $app->response->setStatus(HttpStatus::BAD_REQUEST);
            return new ClientException('Malformed request');
        }

        public static function methodNotAllowed(App &$app): ClientException
        {
            $app->response->setStatus(HttpStatus::METHOD_NOT_ALLOWED);
            $app->response->header()->addIfAbsent(HttpHeader::ACCESS_CONTROL_ALLOW_METHODS, $app->config->allowedRequestMethods());
            return new ClientException('Request Method not allowed');
        }

        public static function notAcceptable(App &$app): ClientException
        {
            $app->response->setStatus(HttpStatus::NOT_ACCEPTABLE);
            return new ClientException('Request not acceptable');
        }

        public static function serverError(App &$app): ServerException
        {
            $app->response->setStatus(HttpStatus::INTERNAL_SERVER_ERROR);
            return new ServerException('Could not process the request');
        }

        public static function notAuthorized(App &$app): ClientException
        {
            $app->response->setStatus(HttpStatus::UNAUTHORIZED);
            return new ClientException('Not authorized to access the resource');
        }

        public static function offline(App &$app): ServerException
        {
            $app->response->setStatus(HttpStatus::SERVICE_UNAVAILABLE);
            return new ServerException('Server is offline');
        }

        public static function sessionExpired(App &$app): ClientException
        {
            $app->response->setStatus(HttpStatus::BAD_REQUEST);
            return new ClientException('Session has expired');
        }

        public static function forbidden(App &$app): ClientException
        {
            $app->response->setStatus(HttpStatus::FORBIDDEN);
            return new ClientException('Access denied');
        }
    }

}
