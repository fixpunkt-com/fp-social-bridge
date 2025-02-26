<?php

namespace Fixpunkt\FpSocialBridge\v2\Data;

use Fixpunkt\FpSocialBridge\SerializableInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Post implements SerializableInterface {

    public function __construct(
        protected readonly string $id,
        protected readonly string $headline,
        protected readonly string $message,
        protected readonly string $post_url,
        protected readonly \DateTime $update_time,
        protected readonly string $link,
        protected readonly array $hashtags,
        protected readonly array $mentions,
        protected readonly array $pictures
    ) {}

    public static function fromArray(array $array) : Post {
        return GeneralUtility::makeInstance(Post::class,
            $array["id"],
            $array["headline"] ?? "",
            $array["message"],
            $array["post_url"],
            self::processDateTimeArray($array["update_time"]),
            $array["link"],
            $array["hashtags"] ?? [],
            $array["mentions"] ?? [],
            $array["pictures"] ?? []
        );
    }

    public static function fromJson(string $json) : Post {
        $array = json_decode($json, true);
        return self::fromArray($array);
    }

    protected static function processDateTimeArray(array $data) : \DateTime {
        $date = substr($data['date'],0,19);
        $timezone = $data["timezone"];

        $dateTime = \DateTime::createFromFormat("Y-m-d H:i:s P", $date.$timezone);

        if($dateTime === false) {
            throw new \Exception("Input data is not valid.", 1652115948);
        }
        return $dateTime;

    }

    public function toArray() : array {
        return [
            "id" => $this -> id,
            "headline" => $this -> headline,
            "message" => $this -> message,
            "post_url" => $this -> post_url,
            "update_time" => $this -> update_time,
            "link" => $this -> link,
            "hashtags" => $this -> hashtags,
            "mentions" => $this -> mentions,
            "pictures" => $this -> pictures,
        ];
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getHeadline(): string
    {
        return $this->headline;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getPostUrl(): string
    {
        return $this->post_url;
    }

    /**
     * @return \DateTime
     */
    public function getUpdateTime(): \DateTime
    {
        return $this->update_time;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @return array
     */
    public function getHashtags(): array
    {
        return $this->hashtags;
    }

    /**
     * @return array
     */
    public function getMentions(): array
    {
        return $this->mentions;
    }

    /**
     * @return array
     */
    public function getPictures(): array
    {
        return $this->pictures;
    }
}