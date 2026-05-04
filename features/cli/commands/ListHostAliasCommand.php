<?php

/**
 * Description of ListHostAliasCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\cli\commands {

    use features\cli\CommandContract;
    use features\cli\helpers\Formatter;
    use shani\launcher\Framework;

    final class ListHostAliasCommand extends CommandContract
    {

        private readonly string $hostname;

        public function __construct()
        {
            parent::__construct('list:alias', 'hostname', 'Show all available host aliases', 'localhost');
        }

        public function execute(): void
        {
            echo 'Listing all host aliases: ' . $this->hostname . PHP_EOL;
            if (!is_file(Framework::DIR_HOSTS . '/' . $this->hostname . '.yml')) {
                echo 'Host "' . $this->hostname . '" does not exists.' . PHP_EOL;
                return;
            }
            $aliases = glob(Framework::DIR_HOSTS . '/*.alias');
            if (empty($aliases)) {
                echo 'No alias found for host "' . $this->hostname . '"' . PHP_EOL;
                return;
            }
            foreach ($aliases as $key => $name) {
                if (file_get_contents($name) === $this->hostname) {
                    echo Formatter::formatSentence($key + 1, basename($name, '.alias'));
                }
            }
        }

        public function parse(string ...$args): CommandContract
        {
            $values = explode(self::SEPARATOR, $args[0]);
            if (count($values) < 1) {
                throw new \ArgumentCountError('Atleast one argument is required.');
            }
            $this->validateHostName($values[0]);
            $this->hostname = $values[0];
            return $this;
        }
    }

}
