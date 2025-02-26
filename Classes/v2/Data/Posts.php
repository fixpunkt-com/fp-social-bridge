<?php

namespace Fixpunkt\FpSocialBridge\SocialServer\v2\Data;

use Fixpunkt\FpSocialBridge\SocialServer\SerializableInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Posts implements SerializableInterface, \Iterator, \Countable {
    /** @var int  */
    private int $position = 0;

    public function __construct(
        protected readonly array $posts
    ) {}


    public static function fromJson(string $json): Posts {
        $array = json_decode($json, true);
        return self::fromArray($array);
    }

    public static function fromArray(array $array): Posts {
        $posts = [];

        /** @var array $postData */
        foreach($array as $postData) {
            $posts[] = Post::fromArray($postData);
        }

        return GeneralUtility::makeInstance(Posts::class, $posts);
    }

    public function toArray(): array {
        $return = [];
        /** @var Post $post */
        foreach($this -> posts as $post) {
            $return[] = $post->toArray();
        }
        return $return;
    }

    public function current(): ?Post {
        return $this -> posts[$this -> position] ?? null;
    }

    public function next(): void {
        $this -> position++;
    }

    public function key(): int {
        return $this -> position;
    }

    public function valid(): bool {
        return $this -> current() !== null;
    }

    public function rewind(): void {
        $this -> position = 0;
    }

    public function count(): int {
        return count($this -> posts);
    }
}