<?php

namespace Fixpunkt\FpSocialBridge;

interface SerializableInterface {
    public static function fromJson(string $json) : self;
    public static function fromArray(array $array) : self;
    public function toArray() : array;

}