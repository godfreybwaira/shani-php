<?php

/**
 * Description of DeselectActiveProjectCommand
 * @author goddy
 *
 * @since v1.0: Jun 11, 2026 at 9:44:35 AM
 */

namespace features\console\commands\project {

    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\SelectedProjectResource;
    use features\console\printer\PrintedText;

    final class DeselectActiveProjectCommand extends CommandContract
    {

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct(
                    $registry,
                    'deselect:project',
                    null,
                    'Unset the current active project, project version, and project module',
                    null
            );
        }

        public function execute(): void
        {
            if (SelectedProjectResource::deselect()) {
                $this->registry->addResult(PrintedText::success('Active project deselected'));
            } else {
                $this->registry->addResult(PrintedText::error('Active project already deselected'));
            }
        }

        public function parse(string ...$args): ?string
        {
            return null;
        }
    }

}
