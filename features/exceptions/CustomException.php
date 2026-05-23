<?php

/**
 * Description of CustomException
 * @author coder
 *
 * Created on: Apr 5, 2025 at 12:53:01 PM
 */

namespace features\exceptions {

    use features\exceptions\client\AccessGrantException;
    use features\exceptions\client\AuthorizationException;
    use features\exceptions\client\BadRequestException;
    use features\exceptions\client\ClientException;
    use features\exceptions\client\CsrfException;
    use features\exceptions\client\NotFoundException;
    use features\exceptions\server\ServerException;
    use features\exceptions\server\ServiceUnavailableException;
    use shani\http\enums\HttpStatus;
    use shani\launcher\App;

    final class CustomException
    {

        public static function notFound(string $message = null): NotFoundException
        {
            return new NotFoundException($message ?? 'Resource not found');
        }

        public static function badRequest(App $app, string $message = null): BadRequestException
        {
            $app->response->setStatus(HttpStatus::BAD_REQUEST);
            return new BadRequestException($message ?? 'Malformed request');
        }

        public static function methodNotAllowed(App $app, string $message = null): ClientException
        {
            $app->response->setStatus(HttpStatus::METHOD_NOT_ALLOWED);
            return new ClientException($message ?? 'Request Method not allowed');
        }

        public static function notAcceptable(App $app, string $message = null): ClientException
        {
            $app->response->setStatus(HttpStatus::NOT_ACCEPTABLE);
            return new ClientException($message ?? 'Request not acceptable');
        }

        public static function serverError(App $app, string $message = null): ServerException
        {
            $app->response->setStatus(HttpStatus::INTERNAL_SERVER_ERROR);
            return new ServerException($message ?? 'Could not process the request');
        }

        public static function authorization(App $app, string $message = null): AuthorizationException
        {
            $app->response->setStatus(HttpStatus::UNAUTHORIZED);
            return new AuthorizationException($message ?? 'Not authorized to access the resource');
        }

        public static function offline(App $app, string $message = null): ServiceUnavailableException
        {
            $app->response->setStatus(HttpStatus::SERVICE_UNAVAILABLE);
            return new ServiceUnavailableException($message ?? HttpStatus::SERVICE_UNAVAILABLE->getMessage());
        }

        public static function forbidden(App $app, string $message = null): AccessGrantException
        {
            $app->response->setStatus(HttpStatus::FORBIDDEN);
            return new AccessGrantException($message ?? 'Access denied');
        }

        public static function csrf(App $app, string $message = null): CsrfException
        {
            $app->response->setStatus(HttpStatus::BAD_REQUEST);
            return new CsrfException($message ?? 'Malformed request');
        }
    }

}
