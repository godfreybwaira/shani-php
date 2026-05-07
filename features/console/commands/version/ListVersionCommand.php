<?php

/**
 * Description of ListVersionCommand
 * @author goddy
 *
 * Created on: May 7, 2026 at 9:04:41 AM
 */

namespace features\console\commands\version {

    use features\console\CommandContract;

    final class ListVersionCommand extends CommandContract
    {

        public function __construct()
        {
            parent::__construct('list:version', 'project_name', 'Show all project versions from an existing project', 'blog');
        }

        public function execute(): void
        {

        }

        public function parse(string ...$args): CommandContract
        {

        }
    }

}
