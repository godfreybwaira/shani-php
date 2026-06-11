<?php

/**
 * Description of SelectActiveProjectCommand
 * @author goddy
 *
 * @since v1.0: Jun 11, 2026 at 9:44:35 AM
 */

namespace features\console\commands\project {

    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\ModuleName;
    use features\console\helpers\ResourceName;
    use features\console\helpers\SelectedProjectResource;
    use features\console\printer\PrintedText;
    use features\console\ResourceSelector;

    final class SelectActiveProjectCommand extends CommandContract
    {

        private readonly string $projectName;
        private ?string $versionNumber = null;
        private ?ModuleName $moduleName = null;
        private string $parameters;

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct(
                    $registry,
                    'select:project',
                    'project_name[@version_number[@module_name]]',
                    'Select the current working project, optional version number, and optional module name',
                    'blog@v1@posts'
            );
        }

        public function execute(): void
        {
            $success = SelectedProjectResource::select($this->projectName, $this->versionNumber, $this->moduleName);
            if ($success) {
                $this->registry->addResult(PrintedText::success('Selected: ' . $this->parameters));
            } else {
                $this->registry->addResult(PrintedText::error('Failed to select resource'));
            }
        }

        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $selector = new ResourceSelector();
                $this->projectName = $selector->selectProject();
                $this->versionNumber = $selector->selectProjectVersion(false);
                if (!empty($this->versionNumber)) {
                    $this->moduleName = $selector->selectModule(false);
                }
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 1) {
                    throw new \ArgumentCountError('At least one argument is required.');
                }
                $this->projectName = ResourceName::create($values[0])->shortName;
                if (!empty($values[1])) {
                    $this->versionNumber = ResourceName::create($values[1])->shortName;
                    $this->moduleName = !empty($values[2]) ? ModuleName::create($values[2]) : null;
                }
            }
            $this->parameters = $this->projectName;
            if (!empty($this->versionNumber)) {
                $this->parameters .= self::SEPARATOR . $this->versionNumber;
                if (!empty($this->moduleName)) {
                    $this->parameters .= self::SEPARATOR . $this->moduleName->originalValue;
                }
            }
            return $this->parameters;
        }
    }

}
