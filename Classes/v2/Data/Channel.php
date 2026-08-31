<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocialBridge\v2\Data;

use Fixpunkt\FpSocialBridge\SerializableInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Channel implements SerializableInterface
{
    public function __construct(
        protected readonly string $id,
        protected readonly string $name
    ) {}

    public static function fromArray(array $array): Channel
    {
        return GeneralUtility::makeInstance(
            Channel::class,
            (string)$array['id'],
            (string)($array['name'] ?? '')
        );
    }

    public static function fromJson(string $json): Channel
    {
        $array = json_decode($json, true);
        return self::fromArray($array);
    }

    public function toArray(): array
    {
        return [
            'id' => $this -> id,
            'name' => $this -> name,
        ];
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
