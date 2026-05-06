<?php

/**
 * Description of ListVhostCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\vhost {

    use features\console\CommandContract;
    use features\console\helpers\Formatter;
    use shani\launcher\Framework;

    final class ListVhostCommand extends CommandContract
    {

        public function __construct()
        {
            parent::__construct('list:vhost', null, 'Show the list of all existing virtual hosts', null);
        }

        public function execute(): void
        {
            echo Formatter::placeCenter('List of Virtual Hosts', underline: true);

            $hosts = glob(Framework::DIR_HOSTS . '/*.yml');
            if (empty($hosts)) {
                echo 'No host found.' . PHP_EOL;
                return;
            }
            foreach ($hosts as $key => $name) {
                echo Formatter::formatSentence($key + 1, basename($name, '.yml'));
            }
        }

        public function parse(string ...$args): CommandContract
        {
            return $this;
        }
    }

}
