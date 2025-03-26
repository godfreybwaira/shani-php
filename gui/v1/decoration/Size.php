<?php

/**
 * Description of Size
 * @author coder
 *
 * Created on: Mar 25, 2025 at 10:43:44 AM
 */

namespace gui\v1\decoration {

    enum Size: string
    {

        case SIZE_SM = 0;
        case SIZE_MD = 1;
        case SIZE_LG = 2;
        case SIZE_XL = 3;
        case SIZE_FULL = 4;
        case SIZE_DEFAULT = self::SIZE_MD;
    }

}
