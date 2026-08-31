<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocialBridge\v2\Data;

use Fixpunkt\FpSocialBridge\SerializableInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Account implements SerializableInterface
{
    /**
     * @param Channel[] $channels
     */
    public function __construct(
        protected readonly int $uid,
        protected readonly string $network,
        protected readonly string $networkKey,
        protected readonly string $networkName,
        protected readonly string $displayName,
        protected readonly string $username,
        protected readonly string $email,
        protected readonly ?\DateTime $expires,
        protected readonly bool $expired,
        protected readonly array $channels
    ) {}

    public static function fromArray(array $array): Account
    {
        $channels = [];
        foreach ($array['channels'] ?? [] as $channelData) {
            $channels[] = Channel::fromArray($channelData);
        }

        return GeneralUtility::makeInstance(
            Account::class,
            (int)$array['uid'],
            $array['network'],
            $array['networkKey'] ?? '',
            $array['networkName'] ?? '',
            $array['displayName'] ?? '',
            $array['username'] ?? '',
            $array['email'] ?? '',
            self::processDateTimeArray($array['expires'] ?? null),
            (bool)($array['expired'] ?? false),
            $channels
        );
    }

    public static function fromJson(string $json): Account
    {
        $array = json_decode($json, true);
        return self::fromArray($array);
    }

    /**
     * Anders als bei Post::processDateTimeArray() darf das Ablaufdatum fehlen: Netzwerke ohne
     * verfallenden Zugriffsschluessel liefern hier null.
     * @param array|null $data
     * @return \DateTime|null
     */
    protected static function processDateTimeArray(?array $data): ?\DateTime
    {
        if ($data === null || !isset($data['date'])) {
            return null;
        }

        $date = substr($data['date'], 0, 19);
        $timezone = $data['timezone'];

        $dateTime = \DateTime::createFromFormat('Y-m-d H:i:s P', $date . $timezone);

        if ($dateTime === false) {
            throw new \Exception('Input data is not valid.', 1756652001);
        }
        return $dateTime;
    }

    public function toArray(): array
    {
        $channels = [];
        /** @var Channel $channel */
        foreach ($this -> channels as $channel) {
            $channels[] = $channel -> toArray();
        }

        return [
            'uid' => $this -> uid,
            'network' => $this -> network,
            'networkKey' => $this -> networkKey,
            'networkName' => $this -> networkName,
            'displayName' => $this -> displayName,
            'username' => $this -> username,
            'email' => $this -> email,
            'expires' => $this -> expires,
            'expired' => $this -> expired,
            'channels' => $channels,
        ];
    }

    /**
     * @return int
     */
    public function getUid(): int
    {
        return $this->uid;
    }

    /**
     * Klassenname des Connectors auf dem Social Server, z.B.
     * Fixpunkt\FpSocialServer\Networks\Facebook\Connector.
     * @return string
     */
    public function getNetwork(): string
    {
        return $this->network;
    }

    /**
     * Kurzform des Netzwerks, z.B. "facebook" oder "instagramlogin".
     * @return string
     */
    public function getNetworkKey(): string
    {
        return $this->networkKey;
    }

    /**
     * Anzeigename des Netzwerks, z.B. "Instagram (Direktanmeldung)".
     * @return string
     */
    public function getNetworkName(): string
    {
        return $this->networkName;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return \DateTime|null
     */
    public function getExpires(): ?\DateTime
    {
        return $this->expires;
    }

    /**
     * @return bool
     */
    public function getExpired(): bool
    {
        return $this->expired;
    }

    /**
     * @return Channel[]
     */
    public function getChannels(): array
    {
        return $this->channels;
    }
}
