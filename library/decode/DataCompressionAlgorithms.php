<?php

/**
 * Description of DataCompressionAlgorithms
 * @author coder
 *
 * Created on: Mar 5, 2025 at 10:48:56 AM
 */

namespace library\decode {

    enum DataCompressionAlgorithms: string
    {

        case GZIP = 'gzip';
        case DEFLATE = 'deflate';
        case COMPRESS = 'compress';
    }

}