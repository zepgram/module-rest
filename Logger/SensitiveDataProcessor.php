<?php
/**
 * This file is part of Zepgram\Rest\Model
 *
 * @package    Zepgram\Rest\Logger
 * @file       ObfuscateSensitiveData.php
 * @date       11 24 2024 21:03
 *
 * @author     Benjamin Calef <zepgram@gmail.com>
 * @copyright  2024 Zepgram Copyright (c) (https://github.com/zepgram)
 * @license    MIT License
 **/

declare(strict_types=1);

namespace Zepgram\Rest\Logger;

use Monolog\Processor\ProcessorInterface;

class SensitiveDataProcessor implements ProcessorInterface
{
    private $sensitiveKeyPattern;

    public function __construct(
        private array $sensitiveKeys = [],
        private array $overrideSensitiveKeys = [],
        private string $redactionPlaceholder = '***REDACTED***',
        private ?bool $isEnabled = null,
    ) {
        $defaultSensitiveKeys = [
            'password',
            'username',
            'user',
            'token',
            'key',
            'secret',
            'hash',
            'hmac',
            'sha',
            'sign',
            'authorization',
            'jwt',
            'access',
            'auth',
            'sso',
            'passphrase',
            'ssh',
            'pin',
            'cvv',
            'ccv',
            'cvc',
            'card'
        ];
        $this->isEnabled = $isEnabled ?? (getenv('MAGE_MODE') === 'production');
        $this->sensitiveKeys = array_unique(array_merge($defaultSensitiveKeys, $sensitiveKeys));
        $this->sensitiveKeys = $this->overrideSensitiveKeys ?: $this->sensitiveKeys;
        $this->sensitiveKeyPattern = '/' . implode('|', array_map('preg_quote', $this->sensitiveKeys)) . '/i';
    }

    public function __invoke(array $record): array
    {
        if (!$this->isEnabled) {
            return $record;
        }

        foreach ($record as &$line) {
            $line = $this->redactSensitiveData($line);
        }

        return $record;
    }

    private function redactSensitiveData(mixed $data): mixed
    {
        if (is_array($data)) {
            foreach ($data as $key => &$value) {
                if (is_array($value)) {
                    $value = $this->redactSensitiveData($value);
                } elseif ($key && is_string($key) && $this->isSensitiveKey($key)) {
                    $value = $this->redactionPlaceholder;
                }
            }
            return $data;
        }

        return $data;
    }

    private function isSensitiveKey(string $key): bool
    {
        return preg_match($this->sensitiveKeyPattern, $key) === 1;
    }
}
