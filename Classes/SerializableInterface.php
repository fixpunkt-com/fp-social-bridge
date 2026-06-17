<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocialBridge;

interface SerializableInterface
{
    public static function fromJson(string $json): self;
    public static function fromArray(array $array): self;
    public function toArray(): array;

}
