<?php

/**
 * Description of CreateVersionCommand
 * @author goddy
 *
 * Created on: May 7, 2026 at 9:04:41 AM
 */

namespace features\console\commands\version {

    use features\console\CommandContract;

    final class CreateVersionCommand extends CommandContract
    {

        public function __construct()
        {
            parent::__construct('create:version', 'version_number@project_name', 'Create a new project version from an existing project', 'v1@blog');
        }

        public function execute(): void
        {

        }

        public function parse(string ...$args): CommandContract
        {

        }
    }

}
