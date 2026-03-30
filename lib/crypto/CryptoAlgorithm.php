<?php

/**
 * CryptoAlgorithm
 *
 * Cryptographic algorithm supported by both <code>openssl_get_md_methods()</code> and <code>hash_hmac_algos()</code>
 * @author goddy
 *
 * Created on: Mar 28, 2026 at 11:52:50 AM
 */

namespace lib\crypto {

    enum CryptoAlgorithm: string
    {

        case MD4 = 'md4';
        case MD5 = 'md5';
        case SHA1 = 'sha1';
        case SHA224 = 'sha224';
        case SHA256 = 'sha256';
        case SHA384 = 'sha384';
        case SHA512 = 'sha512';
        case SHA3_224 = 'sha3-224';
        case SHA3_256 = 'sha3-256';
        case SHA3_384 = 'sha3-384';
        case SHA3_512 = 'sha3-512';
        case RIPEMD160 = 'ripemd160';
        case WHIRLPOOL = 'whirlpool';
    }

}
