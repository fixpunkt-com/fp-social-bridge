<?php

namespace Fixpunkt\FpSocialBridge\v2\Response;

use Fixpunkt\FpSocialBridge\SerializableInterface;
use Fixpunkt\FpSocialBridge\v2\Data\Post;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SocialServerPostResponse extends SocialServerResponse implements SerializableInterface {
    public function __construct(
        int $version,
        protected readonly Post $post
    ) {
        parent::__construct($version);
    }

    public static function fromArray(array $array) : SocialServerPostResponse {
        return GeneralUtility::makeInstance(SocialServerPostResponse::class,
            $array["version"],
            Post::fromArray($array["post"])
        );
    }
    public static function fromJson(string $json) : SocialServerPostResponse {
        $array = json_decode($json, true);
        return self::fromArray($array);
    }

    public function toArray(): array {
        return [
            "type" => self::class,
            "version" => $this -> version,
            "post" => $this -> post -> toArray()
        ];
    }

    /**
     * @return SocialServerResponse
     */
    public function getPost(): Post
    {
        return $this->post;
    }
}