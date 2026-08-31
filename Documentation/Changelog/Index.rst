..  include:: /Includes.rst.txt

..  _changelog:

=========
Changelog
=========

1.4.0
=====

*   Added ``v2\Response\SocialServerAccountsResponse`` together with the data
    transfer objects ``v2\Data\Account``, ``v2\Data\Accounts`` and
    ``v2\Data\Channel``. They carry the accounts a user has connected on the
    social server, grouped by network, so that the client side can prefill the
    account, channel and label fields of an account record.
*   ``SocialServerResponse::fromJson()`` now also recognizes the new accounts
    response.

1.3.0
=====

*   Added compatibility with TYPO3 14. The extension now supports TYPO3 12.4,
    13.4 and 14.3.
