<?php

namespace Fixpunkt\FpSocialBridge\v2\Response;

use Fixpunkt\FpSocialBridge\SerializableInterface;
use Fixpunkt\FpSocialBridge\v2\Data\Post;
use Fixpunkt\FpSocialBridge\v2\Data\Posts;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SocialServerPostsResponse extends SocialServerResponse implements SerializableInterface {
    public function __construct(
        int $version,
        protected readonly Posts $posts,
        protected readonly string $previous,
        protected readonly string $next
    ) {
        parent::__construct($version);
    }

    public static function fromArray(array $array) : SocialServerResponse {
        return GeneralUtility::makeInstance(SocialServerPostsResponse::class,
            $array["version"],
            Posts::fromArray($array["posts"]),
            $array["requests"]["previousPage"],
            $array["requests"]["nextPage"],
        );
    }

    public function toArray() : array {
        $posts = [];
        /** @var Post $post */
        foreach($this -> posts as $post) {
            $posts[] = $post -> toArray();
        }

        return [
            "type" => self::class,
            "version" => $this -> version,
            "posts" => $posts,
            "requests" => [
                "previousPage" => $this -> previous,
                "nextPage" => $this ->next
            ]
        ];
    }

    /**
     * @return Posts
     */
    public function getPosts(): Posts
    {
        return $this->posts;
    }

    /**
     * @return string
     */
    public function getNext(): string
    {
        return $this->next;
    }

    /**
     * @return string
     */
    public function getPrevious(): string
    {
        return $this->previous;
    }
}