<?php

/**
 * Description of Oauth2Error
 * @author goddy
 *
 * Created on: Mar 9, 2026 at 12:11:01 PM
 */

namespace features\oauth2 {

    enum Oauth2Error
    {

        /**
         * The provided authorization grant is invalid, expired, revoked, or does not match the redirection URI.
         */
        case INVALID_GRANT;

        /**
         * Client authentication failed.
         */
        case INVALID_CLIENT;

        /**
         * The authorization grant type is not supported by the authorization server.
         */
        case UNSUPPORTED_GRANT_TYPE;

        /**
         * The request is missing a required parameter or is otherwise malformed.
         */
        case INVALID_REQUEST;

        /**
         * The access token is invalid or expired.
         */
        case INVALID_TOKEN;

        /**
         * The request requires user authentication.
         */
        case UNAUTHORIZED;

        /**
         * An internal server error occurred.
         */
        case SERVER_ERROR;

        /**
         * The requested endpoint was not found.
         */
        case NOT_FOUND;

        /**
         * The requested response type is not supported.
         */
        case UNSUPPORTED_RESPONSE_TYPE;

        /**
         * The device authorization is pending user approval.
         */
        case AUTHORIZATION_PENDING;

        /**
         * Access token has expired.
         */
        CASE EXPIRED_TOKEN;

        /**
         * Not allowed for a given scope.
         */
        CASE INVALID_SCOPE;

        /**
         * Unknown error.
         */
        case UNKNOWN_ERROR;
    }

}
