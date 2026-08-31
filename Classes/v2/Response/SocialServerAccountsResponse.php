<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocialBridge\v2\Response;

use Fixpunkt\FpSocialBridge\SerializableInterface;
use Fixpunkt\FpSocialBridge\v2\Data\Accounts;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SocialServerAccountsResponse extends SocialServerResponse implements SerializableInterface
{
    public function __construct(
        int $version,
        protected readonly Accounts $accounts
    ) {
        parent::__construct($version);
    }

    public static function fromArray(array $array): SocialServerAccountsResponse
    {
        return GeneralUtility::makeInstance(
            SocialServerAccountsResponse::class,
            $array['version'],
            Accounts::fromArray($array['accounts'])
        );
    }

    public function toArray(): array
    {
        return [
            'type' => self::class,
            'version' => $this -> version,
            'accounts' => $this -> accounts -> toArray(),
        ];
    }

    /**
     * @return Accounts
     */
    public function getAccounts(): Accounts
    {
        return $this->accounts;
    }
}
