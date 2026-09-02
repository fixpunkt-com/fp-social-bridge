..  include:: /Includes.rst.txt

..  _introduction:

============
Introduction
============

About this extension
====================

``fp_social_bridge`` is a lean library extension. It provides the
**data-transfer and response objects** exchanged between the fixpunkt social
server and TYPO3. The protocol (currently version 2) is encapsulated as typed
PHP objects and is used, among others, by
`fp_social <https://github.com/fixpunkt-com/fp-social>`__.

The extension deliberately contains **no** business logic of its own, no TCA and
no plugins – only the shared classes, so that the server and client side use the
same format.

Included classes
================

..  list-table::
    :header-rows: 1
    :widths: 40 60

    *   -   Class
        -   Purpose
    *   -   ``SerializableInterface``
        -   Contract: ``fromJson()``, ``fromArray()``, ``toArray()``
    *   -   ``v2\Data\Post``
        -   A single post (id, headline, message, url, date, hashtags,
            mentions, pictures)
    *   -   ``v2\Data\Posts``
        -   Iterable, countable collection of ``Post`` objects
    *   -   ``v2\Data\Channel``
        -   A selectable page or channel of a connected account (id, name)
    *   -   ``v2\Data\Account``
        -   A connected account incl. its channels (uid, network, display name,
            e-mail, expiry)
    *   -   ``v2\Data\Accounts``
        -   Iterable, countable collection of ``Account`` objects, grouped by
            network
    *   -   ``v2\Response\SocialServerResponse``
        -   Abstract base incl. version check and the ``fromJson()`` factory
    *   -   ``v2\Response\SocialServerPostResponse``
        -   Response containing a single post
    *   -   ``v2\Response\SocialServerPostsResponse``
        -   Response containing multiple posts + pagination
            (``previousPage``/``nextPage``)
    *   -   ``v2\Response\SocialServerAccountsResponse``
        -   Response containing all connected accounts, grouped by network
    *   -   ``v2\Response\SocialServerErrorResponse``
        -   Error response (code with prefix ``5550``, message)
    *   -   ``v2\Response\SocialServerRateLimitResponse``
        -   Answer to a throttled request: which limit was hit and from when the
            service can be used again
    *   -   ``v2\Response\SocialServerVersionMismatchResponse``
        -   Answer whose protocol version does not fit the expected one
    *   -   ``v2\Response\SocialServerUnrecognizedResponse``
        -   Answer whose type is unknown, incl. the received type and the raw
            data
