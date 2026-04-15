<?php

/**
 * Description of FileConnectionDto
 * @author goddy
 *
 * Created on: Apr 6, 2026 at 12:06:35 PM
 */

namespace features\persistence\session\dto {

    use features\persistence\session\SessionConnectionInterface;

    /**
     * Handle session using session default mechanism
     */
    final class FileConnectionDto implements SessionConnectionInterface
    {

        public function getConnectionString(): string
        {
            return '';
        }

        public function getHandler(): string
        {
            return 'files';
        }
    }

}
