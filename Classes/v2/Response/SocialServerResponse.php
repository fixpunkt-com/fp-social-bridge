<?php

namespace Fixpunkt\FpSocialBridge\SocialServer\v2\Response;

use Fixpunkt\FpSocialBridge\SocialServer\SerializableInterface;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

abstract class SocialServerResponse implements SerializableInterface {
    const version = 2;

    public function __construct(
        protected readonly int $version
    ) {}

    public static function fromJson(string $json) : SocialServerResponse {

        $array = json_decode($json, true);

        // check if answer is corrupted
        if($array === null || !is_array($array)) {
            if(($array["version"] ?? "") != self::version) {
                throw new \Exception("Received data is corrupted.", 1684785549);
            }
        }

        // Auf Fehler prüfen
        if(key_exists("error", $array)) {
            throw new \Exception(
                $array["error"]["message"],
                $array["error"]["code"]
            );
        }

        // Prüfen ob korrekte Version abgerufen wurde
        if($array["version"] != self::version) {
            throw new \Exception("Version of answer does not fit request version.", 1652117309);
        }

        // check which response we have
        if(key_exists("post", $array)) {
            return SocialServerPostResponse::fromArray($array);
        } else if(key_exists("posts", $array)) {
            return SocialServerPostsResponse::fromArray($array);
        }

        DebuggerUtility::var_dump($array);
        die();
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }
}