<?php

namespace features\console\commands\misc {

    use features\cache\Cache;
    use features\console\CommandContract;
    use features\console\CommandRegistry;

    /**
     * DeleteCacheCommand
     *
     * Console command to clear all application caches.
     * This command integrates with the Cache facade and CommandRegistry
     * to provide a simple way to reset cached data from the CLI.
     *
     * Note:
     *   Ensure php extension is installed and enabled APCu is enabled
     *  (`apc.enabled=1` and `apc.enable_cli=1`) and PHP-FPM is restarted for
     *  APCu cache clearing to work properly.
     *
     * @author goddy
     * @created May 18, 2026 at 5:34:23 PM
     */
    final class DeleteCacheCommand extends CommandContract
    {

        /**
         * Constructor.
         *
         * Registers the command with the given registry.
         *
         * @param CommandRegistry $registry The command registry instance.
         */
        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'delete:cache', null, 'Delete all application caches', null);
        }

        /**
         * Execute the command.
         *
         * Clears all caches using the Cache facade and
         * records the result in the command registry.
         *
         * @return void
         */
        public function execute(): void
        {
            Cache::instance()->clear();
            $this->registry->addResult('Application cache cleared');
        }

        /**
         * Parse command arguments.
         *
         * This command does not accept arguments, so parsing always returns null.
         *
         * @param string ...$args Command-line arguments.
         * @return string|null Always null, since no arguments are required.
         */
        public function parse(string ...$args): ?string
        {
            return null;
        }
    }

}
