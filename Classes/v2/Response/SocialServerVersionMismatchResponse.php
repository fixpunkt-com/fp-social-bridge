<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocialBridge\v2\Response;

use Fixpunkt\FpSocialBridge\SerializableInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Antwort, wenn die Protokollversion der Antwort nicht zur erwarteten Version passt.
 */
class SocialServerVersionMismatchResponse extends SocialServerErrorResponse implements SerializableInterface
{
    public const versionMismatchCode = 1652117309;

    /**
     * @param int $version Version, mit der die Antwort ausgeliefert wurde.
     * @param string $message
     * @param int $expectedVersion Version, die erwartet wurde.
     */
    public function __construct(
        int $version,
        string $message,
        protected readonly int $expectedVersion
    ) {
        parent::__construct($version, self::versionMismatchCode, $message);
    }

    /**
     * Nimmt sowohl eine bewusst verschickte Versionsmeldung als auch eine beliebige Antwort
     * mit unpassender Version. Fehlt eine Meldung, wird sie aus den Versionen gebildet.
     */
    public static function fromArray(array $array): SocialServerVersionMismatchResponse
    {
        $version = (int)($array['version'] ?? 0);
        $expectedVersion = (int)($array['expectedVersion'] ?? SocialServerResponse::version);

        return GeneralUtility::makeInstance(
            SocialServerVersionMismatchResponse::class,
            $version,
            (string)($array['message'] ?? sprintf(
                'Version of answer (%d) does not fit request version (%d).',
                $version,
                $expectedVersion
            )),
            $expectedVersion
        );
    }

    public static function fromJson(string $json): SocialServerVersionMismatchResponse
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
            'expectedVersion' => $this -> expectedVersion,
        ];
    }

    /**
     * Version, die erwartet wurde.
     * @return int
     */
    public function getExpectedVersion(): int
    {
        return $this->expectedVersion;
    }

    /**
     * Version, mit der die Antwort ausgeliefert wurde. Identisch zu getVersion().
     * @return int
     */
    public function getReceivedVersion(): int
    {
        return $this->version;
    }
}
