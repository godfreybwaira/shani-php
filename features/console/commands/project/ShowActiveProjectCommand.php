<?php

/**
 * Description of ShowActiveProjectCommand
 * @author goddy
 *
 * @since v1.0: Jun 11, 2026 at 9:44:35 AM
 */

namespace features\console\commands\project {

    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\Formatter;
    use features\console\helpers\SelectedProjectResource;

    final class ShowActiveProjectCommand extends CommandContract
    {

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct(
                    $registry,
                    'active:project',
                    null,
                    'Show the selected active project, with optional version number, and optional module name',
                    null
            );
        }

        public function execute(): void
        {
            $selector = SelectedProjectResource::getInstance();
            if ($selector->projectName === null) {
                throw new \RuntimeException('No project was selected.');
            }
            $this->registry->addResult(Formatter::formatSentence('DESCRIPTION', 'NAME'));
            $this->registry->addResult(Formatter::formatSentence('Current Project', $selector->projectName));
            $this->registry->addResult(Formatter::formatSentence('Current Project Version', $selector->versionNumber ?? '(Not Available)'));
            $this->registry->addResult(Formatter::formatSentence('Current Project Module', $selector->moduleName->originalValue ?? '(Not Available)'));
        }

        public function parse(string ...$args): ?string
        {
            return null;
        }
    }

}
