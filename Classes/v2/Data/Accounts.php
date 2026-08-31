<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocialBridge\v2\Data;

use Fixpunkt\FpSocialBridge\SerializableInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Alle verbundenen Zugaenge, nach Netzwerk gruppiert.
 *
 * Anders als Posts ist das keine flache Liste: iteriert wird ueber die Netzwerke, der Wert ist
 * jeweils ein Array von Account-Objekten.
 */
class Accounts implements SerializableInterface, \IteratorAggregate, \Countable
{
    /**
     * @param array<string, Account[]> $accountsByNetwork
     */
    public function __construct(
        protected readonly array $accountsByNetwork
    ) {}

    public static function fromJson(string $json): Accounts
    {
        $array = json_decode($json, true);
        return self::fromArray($array);
    }

    public static function fromArray(array $array): Accounts
    {
        $accountsByNetwork = [];

        foreach ($array as $network => $accountsData) {
            $accounts = [];
            foreach ($accountsData as $accountData) {
                $accounts[] = Account::fromArray($accountData);
            }
            $accountsByNetwork[$network] = $accounts;
        }

        return GeneralUtility::makeInstance(Accounts::class, $accountsByNetwork);
    }

    public function toArray(): array
    {
        $return = [];

        foreach ($this -> accountsByNetwork as $network => $accounts) {
            $return[$network] = [];
            /** @var Account $account */
            foreach ($accounts as $account) {
                $return[$network][] = $account -> toArray();
            }
        }

        return $return;
    }

    /**
     * Iteriert ueber die Netzwerke: Schluessel ist der Netzwerk-Klassenname, Wert ein Account[].
     * @return \ArrayIterator
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this -> accountsByNetwork);
    }

    /**
     * Gesamtzahl der Zugaenge ueber alle Netzwerke hinweg.
     * @return int
     */
    public function count(): int
    {
        $count = 0;
        foreach ($this -> accountsByNetwork as $accounts) {
            $count += count($accounts);
        }
        return $count;
    }

    /**
     * Alle Netzwerke, zu denen es Zugaenge gibt.
     * @return string[]
     */
    public function getNetworks(): array
    {
        return array_keys($this -> accountsByNetwork);
    }

    /**
     * @param string $network
     * @return Account[]
     */
    public function getByNetwork(string $network): array
    {
        return $this -> accountsByNetwork[$network] ?? [];
    }

    /**
     * Alle Zugaenge als flache Liste, ohne Gruppierung.
     * @return Account[]
     */
    public function getAll(): array
    {
        return array_merge(...array_values($this -> accountsByNetwork));
    }
}
