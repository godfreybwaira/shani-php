<?php

/**
 * Description of LocateVersionCommand
 * @author goddy
 *
 * Created on: May 7, 2026 at 9:04:41 AM
 */

namespace features\console\commands\version {

    use features\console\CommandContract;

    final class LocateVersionCommand extends CommandContract
    {

        public function __construct()
        {
            parent::__construct('locate:version', 'version_number@project_name', 'Show the location of the given project version', 'v1@blog');
        }

        public function execute(): void
        {

        }

        public function parse(string ...$args): CommandContract
        {

        }
    }

}
