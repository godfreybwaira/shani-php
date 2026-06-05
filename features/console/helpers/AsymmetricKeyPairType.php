<?php

/**
 * Description of AsymmetricKeyPairType
 * @author goddy
 *
 * @since May 30, 2026 at 12:39:14 PM
 */

namespace features\console\helpers {

    enum AsymmetricKeyPairType: string
    {

        case ECDSA = 'ecdsa';
        case ED25519 = 'ed25519';
        case RSA = 'rsa';
    }

}
