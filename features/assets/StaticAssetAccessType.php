<?php

/**
 * Description of StaticAssetAccessType
 * @author goddy
 *
 * Created on: Apr 26, 2026 at 6:17:13 PM
 */

namespace features\assets {

    enum StaticAssetAccessType
    {

        case PRIVATE_ACCESS;
        case PROTECTED_ACCESS;
        case PUBLIC_ACCESS;
    }

}
