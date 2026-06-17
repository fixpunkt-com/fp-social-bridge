<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocialBridge\v2\Response;

use Fixpunkt\FpSocialBridge\SerializableInterface;

abstract class SocialServerResponse implements SerializableInterface
{
    public const version = 2;

    public function __construct(
        protected readonly int $version
    ) {}

    public static function fromJson(string $json): SocialServerResponse
    {

        $array = json_decode($json, true);

        // check if answer is corrupted
        if ($array === null || !is_array($array)) {
            if (($array['version'] ?? '') != self::version) {
                throw new \Exception('Received data is corrupted.', 1684785549);
            }
        }

        // Auf Fehler prüfen
        if ($array['type'] == SocialServerErrorResponse::class) {
            return SocialServerErrorResponse::fromArray($array);
        }

        // Prüfen ob korrekte Version abgerufen wurde
        if ($array['version'] != self::version) {
            throw new \Exception('Version of answer does not fit request version.', 1652117309);
        }

        // check which response we have
        switch ($array['type']) {
            case SocialServerPostResponse::class:
                return SocialServerPostResponse::fromArray($array);
            case SocialServerPostsResponse::class:
                return SocialServerPostsResponse::fromArray($array);
        }

        // throw exception
        throw new \Exception('The received response is not recognized.', 1741293955);
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }
}
