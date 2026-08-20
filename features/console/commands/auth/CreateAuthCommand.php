<?php

/**
 * Description of CreateAuthCommand
 * @author goddy
 *
 * @since v1.0: Aug 8, 2026 at 12:12:19 PM
 */

namespace features\console\commands\auth {

    use features\console\builders\AuthBuilder;
    use features\console\builders\ProjectVersionBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\AuthType;
    use features\console\helpers\ModuleName;
    use features\console\helpers\ResourceName;
    use features\console\ResourceSelector;

    final class CreateAuthCommand extends CommandContract
    {

        /**
         * The authentication tyoe
         *
         * @var string
         */
        private readonly string $type;

        /**
         * The name of the project for which the version is being created.
         *
         * @var string
         */
        private readonly string $projectName;

        /**
         * The version number to call command from.
         *
         * @var string
         */
        private readonly string $versionNumber;

        /**
         * Constructor.
         *
         * Registers the command with the given registry.
         *
         * @param CommandRegistry $registry The command registry instance.
         */
        public function __construct(CommandRegistry $registry)
        {
            parent::__construct(
                    $registry, 'create:auth', 'project@version@authtype',
                    'Create custom authentication strategy used to authenticate, register and unregister user',
                    'demo@v1@[default|basic|jwt]');
        }

        public function execute(): void
        {
            $authType = AuthType::tryFrom($this->type);
            if ($authType === null) {
                throw new \RuntimeException('Supported authentication type are: ' . implode(',', array_map(fn(AuthType $type) => $type->value, AuthType::cases())));
            }
            $version = ProjectVersionBuilder::fromProjectName($this->projectName, $this->versionNumber);
            $builder = new AuthBuilder($version, $authType);
            $builder->build(fn($s) => $this->registry->addResult($s));
        }

        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $selector = new ResourceSelector();
                $this->projectName = $selector->selectProject(true);
                $this->versionNumber = $selector->selectProjectVersion(true);
                $this->type = $selector->selectAuthType(true);
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 3) {
                    throw new \ArgumentCountError('At least three arguments are required.');
                }
                $this->projectName = ResourceName::create($values[0])->shortName;
                $this->versionNumber = ModuleName::create($values[1])->directoryName;
                $this->type = $values[2];
            }
            return $this->projectName . self::SEPARATOR . $this->versionNumber . self::SEPARATOR . $this->type;
        }
    }

}
