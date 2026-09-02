<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocialBridge\v2\Response;

use Fixpunkt\FpSocialBridge\SerializableInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Antwort des Social Servers, wenn ein Anfragelimit erreicht ist.
 */
class SocialServerRateLimitResponse extends SocialServerErrorResponse implements SerializableInterface
{
    public const rateLimitCode = 1788307200;

    /** Das Limit fuer dasselbe Social-Media-Profil ist erreicht. */
    public const scopeProfile = 'profile';

    /** Das allgemeine Limit ueber alle Anfragen ist erreicht. */
    public const scopeGeneral = 'general';

    /**
     * @param int $version
     * @param string $message
     * @param int $retryAfter Unix-Zeitstempel: ab dann sind wieder Anfragen erlaubt.
     * @param int $retryAfterSeconds Sekunden bis dahin.
     * @param int $limit Erlaubte Anfragen im Zeitfenster.
     * @param string $interval Zeitfenster, z.B. "1 minute".
     * @param string $scope Welches der Limits gegriffen hat.
     */
    public function __construct(
        int $version,
        string $message,
        protected readonly int $retryAfter,
        protected readonly int $retryAfterSeconds,
        protected readonly int $limit,
        protected readonly string $interval,
        protected readonly string $scope
    ) {
        parent::__construct($version, self::rateLimitCode, $message);
    }

    public static function fromArray(array $array): SocialServerRateLimitResponse
    {
        return GeneralUtility::makeInstance(
            SocialServerRateLimitResponse::class,
            $array['version'],
            $array['message'],
            (int)($array['retryAfter'] ?? 0),
            (int)($array['retryAfterSeconds'] ?? 0),
            (int)($array['limit'] ?? 0),
            (string)($array['interval'] ?? ''),
            (string)($array['scope'] ?? '')
        );
    }

    public static function fromJson(string $json): SocialServerRateLimitResponse
    {
        $array = json_decode($json, true);
        return self::fromArray($array);
    }

    public function toArray(): array
    {
        return [
            'type' => self::class,
            'version' => $this -> version,
            'code' => $this -> code,
            'message' => $this -> message,
            'retryAfter' => $this -> retryAfter,
            'retryAfterSeconds' => $this -> retryAfterSeconds,
            'limit' => $this -> limit,
            'interval' => $this -> interval,
            'scope' => $this -> scope,
        ];
    }

    /**
     * Unix-Zeitstempel, ab dem wieder Anfragen erlaubt sind.
     * @return int
     */
    public function getRetryAfterTimestamp(): int
    {
        return $this->retryAfter;
    }

    /**
     * Zeitpunkt, ab dem wieder Anfragen erlaubt sind.
     * @return \DateTimeImmutable
     */
    public function getRetryAfter(): \DateTimeImmutable
    {
        return (new \DateTimeImmutable('@' . $this->retryAfter))
            ->setTimezone(new \DateTimeZone(date_default_timezone_get()));
    }

    /**
     * Sekunden, bis wieder Anfragen erlaubt sind.
     * @return int
     */
    public function getRetryAfterSeconds(): int
    {
        return $this->retryAfterSeconds;
    }

    /**
     * Anzahl der im Zeitfenster erlaubten Anfragen.
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * Zeitfenster des Limits, z.B. "1 minute".
     * @return string
     */
    public function getInterval(): string
    {
        return $this->interval;
    }

    /**
     * Welches Limit gegriffen hat: "profile" oder "general".
     * @return string
     */
    public function getScope(): string
    {
        return $this->scope;
    }
}
