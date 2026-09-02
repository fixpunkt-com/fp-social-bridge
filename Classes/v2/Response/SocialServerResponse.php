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

        $type = $array['type'] ?? '';

        // Auf Drosselung prüfen. Bewusst vor der Versionsprüfung: ein erreichtes Anfragelimit
        // soll auch dann verstanden werden, wenn die Protokollversionen auseinanderlaufen.
        if ($type == SocialServerRateLimitResponse::class) {
            return SocialServerRateLimitResponse::fromArray($array);
        }

        // Meldungen zur Version und zu unbekannten Typen ebenfalls vor der Versionsprüfung:
        // sie beschreiben genau den Fall, in dem die Versionen nicht zusammenpassen.
        if ($type == SocialServerVersionMismatchResponse::class) {
            return SocialServerVersionMismatchResponse::fromArray($array);
        }

        if ($type == SocialServerUnrecognizedResponse::class) {
            return SocialServerUnrecognizedResponse::fromArray($array);
        }

        // Auf Fehler prüfen
        if ($type == SocialServerErrorResponse::class) {
            return SocialServerErrorResponse::fromArray($array);
        }

        // Prüfen ob korrekte Version abgerufen wurde
        if (($array['version'] ?? null) != self::version) {
            return SocialServerVersionMismatchResponse::fromArray($array);
        }

        // check which response we have
        switch ($type) {
            case SocialServerPostResponse::class:
                return SocialServerPostResponse::fromArray($array);
            case SocialServerPostsResponse::class:
                return SocialServerPostsResponse::fromArray($array);
            case SocialServerAccountsResponse::class:
                return SocialServerAccountsResponse::fromArray($array);
        }

        // Auffangnetz fuer Typen, die diese Version noch nicht kennt: traegt die Antwort Code und
        // Meldung, wird sie als Fehler gelesen. So bricht eine neuere Server-Version bei einem
        // aelteren Client nicht hart ab.
        if (isset($array['code'], $array['message'])) {
            return SocialServerErrorResponse::fromArray($array);
        }

        // Alles andere bleibt unverstanden, wird aber als lesbare Fehlerantwort weitergegeben.
        return SocialServerUnrecognizedResponse::fromArray($array);
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }
}
