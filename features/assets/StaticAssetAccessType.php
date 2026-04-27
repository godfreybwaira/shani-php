<?php

/**
 * Description of StaticAssetAccessType
 * @author goddy
 *
 * Created on: Apr 26, 2026 at 6:17:13 PM
 */

namespace features\assets {

    /**
     * Defines the access types available for static assets.
     *
     * - PRIVATE_ACCESS: Asset is only accessible by the owner.
     * - PUBLIC_ACCESS: Asset is accessible by anyone without restriction.
     */
    enum StaticAssetAccessType
    {

        /** Asset is accessible by the owner or their group */
        case PRIVATE_ACCESS;

        /** Asset is accessible by anyone without restriction */
        case PUBLIC_ACCESS;
    }

}
