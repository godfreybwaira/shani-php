<?php

/**
 * Represents Oauth2 response returned during oauth2 handling
 * @author goddy
 *
 * Created on: Mar 9, 2026 at 11:55:57 AM
 */

namespace lib\oauth2 {

    use lib\DataConvertor;

    final class Oauth2Response
    {

        public readonly \JsonSerializable $body;
        public readonly Oauth2ResponseType $type;

        private function __construct(Oauth2ResponseType $type, array $content)
        {
            $this->body = DataConvertor::array2JsonSerializable($content);
            $this->type = $type;
        }

        public static function success(string $accessToken, int $expiresIn, ?string $refreshToken, ?string $scope): Oauth2Response
        {
            return new self(Oauth2ResponseType::OK, [
                'access_token' => $accessToken,
                'token_type' => 'Bearer',
                'expires_in' => $expiresIn,
                'refresh_token' => $refreshToken,
                'scope' => $scope
            ]);
        }

        public static function error(Oauth2Error $error, ?string $description = null): Oauth2Response
        {
            return new self(Oauth2ResponseType::ERROR, [
                'error' => strtolower($error->name),
                'error_description' => $description
            ]);
        }
    }

}
