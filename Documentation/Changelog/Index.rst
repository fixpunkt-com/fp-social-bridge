..  include:: /Includes.rst.txt

..  _changelog:

=========
Changelog
=========

1.5.0
=====

*   Added ``v2\Response\SocialServerRateLimitResponse``. The social server answers with it
    when a request limit has been reached, and it carries the point in time from which the
    service can be used again -- as a unix timestamp (``retryAfter``), as the remaining
    seconds (``retryAfterSeconds``) and spelled out in ``message``. ``limit`` and ``interval``
    name the limit that was hit, ``scope`` says whether it was the per-profile limit
    (``profile``) or the overall one (``general``).
*   The new response extends ``SocialServerErrorResponse``. Clients that do not know the type
    yet therefore still treat a throttled request as an ordinary error with a readable
    message instead of failing on a type error.
*   ``SocialServerResponse::fromJson()`` now recognizes the new rate limit response, and it
    reads an unknown response type that carries ``code`` and ``message`` as an error response
    instead of throwing. Newer server versions no longer break older clients hard.
*   Added ``v2\Response\SocialServerVersionMismatchResponse``. It replaces the exception with
    code ``1652117309``: if the protocol version of the answer does not match the expected one,
    ``fromJson()`` returns this response instead of throwing. ``getExpectedVersion()`` and
    ``getReceivedVersion()`` name both versions.
*   Added ``v2\Response\SocialServerUnrecognizedResponse``. It replaces the exception with
    code ``1741293955``: an answer whose type is unknown and that carries neither ``code`` nor
    ``message`` is returned as this response. ``getReceivedType()`` and ``getPayload()`` keep
    the type and the raw data of the answer available for logging.
*   Both new responses extend ``SocialServerErrorResponse`` as well, so an existing
    ``instanceof SocialServerErrorResponse`` branch already covers them with a readable
    message. ``fromJson()`` therefore only throws for corrupted data (code ``1684785549``)
    now; code that catches the two former exceptions can be dropped.

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
