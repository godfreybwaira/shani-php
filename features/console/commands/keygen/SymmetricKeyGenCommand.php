<?php

/**
 * Description of SymmetricKeyGenCommand
 * @author goddy
 *
 * Created on: May 30, 2026 at 11:08:41 AM
 */

namespace features\console\commands\keygen {

    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\printer\PrintedText;
    use features\console\ResourceSelector;
    use features\crypto\SymmetricCipherKey;

    final class SymmetricKeyGenCommand extends CommandContract
    {

        private readonly string $algorithm;

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct(
                    $registry, 'keygen:sym', 'alg:<algorithm>',
                    'Generates a random base64 encoded password and initialization vector based on the specified algorithm',
                    'alg:aes-256-cbc'
            );
        }

        public function execute(): void
        {
            $keys = SymmetricCipherKey::create($this->algorithm);
            $this->registry->addResult(PrintedText::info('Algorithm:'));
            $this->registry->addResult($keys->algorithm);
            $this->registry->addResult(PrintedText::info('Password:'));
            $this->registry->addResult($keys->password);
            $this->registry->addResult(PrintedText::info('Initializaition Vector:'));
            $this->registry->addResult($keys->initVector);
        }

        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $this->algorithm = ResourceSelector::selectCipherAlgorithm();
            } else {
                $values = explode(':', $args[0]);
                if (count($values) < 2) {
                    throw new \ArgumentCountError('At least two arguments are required.');
                }
                if ($values[0] !== 'alg') {
                    throw new \InvalidArgumentException('Unknown argument "' . $values[0] . '"');
                }
                $this->algorithm = $values[1];
            }
            return 'alg:' . $this->algorithm;
        }
    }

}
