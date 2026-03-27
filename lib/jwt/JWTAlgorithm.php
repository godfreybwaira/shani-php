<?php

/**
 * Description of JwtAlgorithm
 * @author goddy
 *
 * Created on: Mar 26, 2026 at 12:04:43 PM
 */

namespace lib\jwt {

    enum JWTAlgorithm: string
    {

        case HS256 = 'sha256';
        case HS384 = 'sha384';
        case HS512 = 'sha512';
    }

}
