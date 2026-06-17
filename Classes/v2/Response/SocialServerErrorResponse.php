<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocialBridge\v2\Response;

use Fixpunkt\FpSocialBridge\SerializableInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SocialServerErrorResponse extends SocialServerResponse implements SerializableInterface
{
    protected readonly int $code;

    public function __construct(
        int $version,
        int $code,
        protected readonly string $message
    ) {
        $this -> code = (int)('5550' . $code);
        parent::__construct($version);
    }

    public static function fromArray(array $array): SocialServerErrorResponse
    {
        return GeneralUtility::makeInstance(
            SocialServerErrorResponse::class,
            $array['version'],
            $array['code'],
            $array['message']
        );
    }
    public static function fromJson(string $json): SocialServerErrorResponse
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
        ];
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}
