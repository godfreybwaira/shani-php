<?php

/**
 * Description of AsymmetricKeyGenCommand
 * @author goddy
 *
 * @since May 30, 2026 at 11:08:41 AM
 */

namespace features\console\commands\keygen {

    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\AsymmetricKeyPairType;
    use features\console\printer\ConsoleIO;
    use features\console\printer\PrintedText;
    use features\console\ResourceSelector;
    use features\crypto\AsymmetricKeyPair;
    use features\crypto\CryptoAlgorithm;

    final class AsymmetricKeyGenCommand extends CommandContract
    {

        private readonly AsymmetricKeyPairType $type;
        private ?string $algorithm = null;
        private ?string $curveName = null;
        private ?int $length = null;

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct(
                    $registry, 'keygen:asym', 'type:ecdsa|rsa|ed25519[ alg:<algorithm>[ curve:<curve_name>[ len:<int>]]',
                    'Generates an asymmetric key-pair for asymmetric encryption and decryption',
                    'type:rsa alg:sha256 len:2048'
            );
        }

        public function execute(): void
        {
            $keypair = match ($this->type) {
                AsymmetricKeyPairType::ECDSA => $this->ecdsa(),
                AsymmetricKeyPairType::RSA => $this->rsa(),
                AsymmetricKeyPairType::ED25519 => AsymmetricKeyPair::ed25519()
            };
            $this->registry->addResult(PrintedText::info('Private Key:'));
            $this->registry->addResult($keypair->privateKey);
            $this->registry->addResult(PrintedText::info('Public Key:'));
            $this->registry->addResult($keypair->publicKey);
        }

        private function ecdsa(): AsymmetricKeyPair
        {
            return AsymmetricKeyPair::ecdsa($this->curveName);
        }

        private function rsa(): AsymmetricKeyPair
        {
            return AsymmetricKeyPair::rsa($this->length, CryptoAlgorithm::tryFrom($this->algorithm));
        }

        private static function collectArgs(array $args): array
        {
            $params = [];
            foreach ($args as $value) {
                $strval = explode(':', $value);
                if (isset($strval[1])) {
                    $params[$strval[0]] = $strval[1];
                }
            }
            return $params;
        }

        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $this->type = ResourceSelector::selectKeyPairType();
                if ($this->type === AsymmetricKeyPairType::RSA) {
                    $this->algorithm = ResourceSelector::selectCryptoAlgorithm(false);
                    $size = ConsoleIO::read('Enter key length or press ENTER to skip: ', fn($s) => empty($s) || ((int) $s) > 0);
                    $this->length = empty($size) ? null : (int) $size;
                } else if ($this->type === AsymmetricKeyPairType::ECDSA) {
                    $this->curveName = ResourceSelector::selectCurveName(false);
                }
            } else {
                $params = self::collectArgs($args);
                $type = AsymmetricKeyPairType::tryfrom($params['type']);
                if (empty($type)) {
                    throw new \InvalidArgumentException('Not an allowed value for parameter "type"');
                }
                $this->type = $type;
                $this->algorithm = $params['alg'] ?? null;
                $this->length = $params['len'] ?? null;
                $this->curveName = $params['curve'] ?? null;
            }
            $command = 'type:' . $this->type->value;
            if ($this->type === AsymmetricKeyPairType::RSA) {
                return $command . ' alg:' . ($this->algorithm ?? 'null') . ' len:' . ($this->length ?? 'null');
            } else if ($this->type === AsymmetricKeyPairType::ECDSA) {
                return $command . ' curve:' . ($this->curveName ?? 'null');
            }
            return $command;
        }
    }

}
