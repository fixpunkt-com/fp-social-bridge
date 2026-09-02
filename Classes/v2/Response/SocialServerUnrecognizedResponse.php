<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocialBridge\v2\Response;

use Fixpunkt\FpSocialBridge\SerializableInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Antwort, deren Typ nicht bekannt ist. Traegt den empfangenen Typ und die Rohdaten mit,
 * damit die Antwort protokolliert werden kann, ohne dass die Verarbeitung hart abbricht.
 */
class SocialServerUnrecognizedResponse extends SocialServerErrorResponse implements SerializableInterface
{
    public const unrecognizedResponseCode = 1741293955;

    /**
     * @param int $version
     * @param string $message
     * @param string $receivedType Typ, den die Antwort angegeben hat.
     * @param array $payload Rohdaten der Antwort.
     */
    public function __construct(
        int $version,
        string $message,
        protected readonly string $receivedType,
        protected readonly array $payload = []
    ) {
        parent::__construct($version, self::unrecognizedResponseCode, $message);
    }

    /**
     * Nimmt sowohl eine bewusst verschickte Meldung als auch die unverstandene Antwort selbst:
     * fehlt eine Meldung, wird sie aus dem empfangenen Typ gebildet.
     */
    public static function fromArray(array $array): SocialServerUnrecognizedResponse
    {
        $receivedType = (string)($array['receivedType'] ?? $array['type'] ?? '');
        $payload = $array['payload'] ?? $array;

        return GeneralUtility::makeInstance(
            SocialServerUnrecognizedResponse::class,
            (int)($array['version'] ?? 0),
            (string)($array['message'] ?? ($receivedType !== ''
                ? sprintf('The received response is not recognized: "%s".', $receivedType)
                : 'The received response is not recognized.')),
            $receivedType,
            is_array($payload) ? $payload : []
        );
    }

    public static function fromJson(string $json): SocialServerUnrecognizedResponse
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
            'receivedType' => $this -> receivedType,
            'payload' => $this -> payload,
        ];
    }

    /**
     * Typ, den die Antwort angegeben hat, oder ein leerer String, wenn sie keinen mitbrachte.
     * @return string
     */
    public function getReceivedType(): string
    {
        return $this->receivedType;
    }

    /**
     * Rohdaten der Antwort, z.B. fuer das Log.
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload;
    }
}
