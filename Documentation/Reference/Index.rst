..  include:: /Includes.rst.txt

..  _reference:

===============
Class reference
===============

All classes live in the ``Fixpunkt\FpSocialBridge`` namespace. They fall into
three types:

*   **Interface** – the shared serialization contract.
*   **DTOs** (data transfer objects) – the plain data carriers.
*   **Responses** – the typed response objects of the social server.

..  contents::
    :local:

Interface
=========

..  _reference-serializable-interface:

SerializableInterface
---------------------

Shared contract of all data-transfer and response objects.

..  list-table::
    :header-rows: 1
    :widths: 40 60

    *   -   Method
        -   Description
    *   -   ``fromJson(string $json): static``
        -   Creates an object from a JSON string.
    *   -   ``fromArray(array $data): static``
        -   Creates an object from an associative array.
    *   -   ``toArray(): array``
        -   Serializes the object back into an array.

DTOs
====

The data transfer objects in the ``v2\Data`` namespace contain only data and
accessor methods, no business logic.

..  _reference-post:

v2\\Data\\Post
-------------

Represents a single post.

..  list-table::
    :header-rows: 1
    :widths: 40 60

    *   -   Method
        -   Return value
    *   -   ``getId()``
        -   ``string`` – unique ID of the post
    *   -   ``getHeadline()``
        -   ``string`` – headline (empty for many sources)
    *   -   ``getMessage()``
        -   ``string`` – message text as HTML (may contain ``<br />``/``<a>`` as
            well as emoji placeholders ``{emoji:…}``)
    *   -   ``getPostUrl()``
        -   ``string`` – URL of the post on the network
    *   -   ``getLink()``
        -   ``string`` – linked URL
    *   -   ``getUpdateTime()``
        -   ``\DateTime`` – time of last update (in JSON as ``date`` /
            ``timezone_type`` / ``timezone``)
    *   -   ``getHashtags()``
        -   ``array`` – hashtags as strings **without** a leading ``#``
    *   -   ``getMentions()``
        -   ``array`` – mentions as objects with ``displayName`` and
            ``systemName``
    *   -   ``getPictures()``
        -   ``array`` – picture URLs

..  _reference-posts:

v2\\Data\\Posts
--------------

Iterable, countable collection of ``Post`` objects. Implements ``Iterator`` and
``Countable``, so it can be used directly with ``foreach`` and ``count()``.

..  _reference-channel:

v2\\Data\\Channel
----------------

A selectable page or channel of a connected account. This is exactly what the
client side writes into the ``channel`` and ``label`` fields of an account
record.

..  list-table::
    :header-rows: 1
    :widths: 40 60

    *   -   Method
        -   Return value
    *   -   ``getId()``
        -   ``string`` – id of the page/channel in the network
    *   -   ``getName()``
        -   ``string`` – display name of the page/channel

..  _reference-account:

v2\\Data\\Account
----------------

A single connected account of the social server.

..  list-table::
    :header-rows: 1
    :widths: 40 60

    *   -   Method
        -   Return value
    *   -   ``getUid()``
        -   ``int`` – uid of the access key record on the social server
    *   -   ``getNetwork()``
        -   ``string`` – connector class name, e.g.
            ``Fixpunkt\FpSocialServer\Networks\Facebook\Connector``
    *   -   ``getNetworkKey()``
        -   ``string`` – short form of the network (``facebook``,
            ``instagram``, ``instagramlogin``, ``linkedin``)
    *   -   ``getNetworkName()``
        -   ``string`` – human readable network name, e.g.
            ``Instagram (Direktanmeldung)``
    *   -   ``getDisplayName()``
        -   ``string`` – name of the account as shown to the user
    *   -   ``getUsername()``
        -   ``string`` – identifier of the account within the network
    *   -   ``getEmail()``
        -   ``string`` – e-mail address, may be empty
    *   -   ``getExpires()``
        -   ``\DateTime|null`` – expiry of the access key, ``null`` for
            networks whose keys do not expire
    *   -   ``getExpired()``
        -   ``bool`` – whether the access key has already expired
    *   -   ``getChannels()``
        -   ``Channel[]`` – the pages/channels reachable with this account

..  _reference-accounts:

v2\\Data\\Accounts
-----------------

Iterable, countable collection of ``Account`` objects, **grouped by network**.
Unlike :ref:`Posts <reference-posts>` it is not a flat list: iterating yields
the network class name as key and an ``Account[]`` as value.

..  list-table::
    :header-rows: 1
    :widths: 40 60

    *   -   Method
        -   Description
    *   -   ``getIterator()``
        -   Iterates ``network => Account[]`` (``IteratorAggregate``)
    *   -   ``count()``
        -   Total number of accounts across all networks
    *   -   ``getNetworks()``
        -   ``string[]`` – the networks that have accounts
    *   -   ``getByNetwork(string $network)``
        -   ``Account[]`` – the accounts of one network, empty if unknown
    *   -   ``getAll()``
        -   ``Account[]`` – all accounts as a flat list

Responses
=========

The response objects in the ``v2\Response`` namespace represent the various
response types of the social server. The base class factory turns a received
JSON response into the matching object.

..  _reference-social-server-response:

v2\\Response\\SocialServerResponse
----------------------------------

Abstract base class of all server responses.

..  list-table::
    :header-rows: 1
    :widths: 40 60

    *   -   Method
        -   Description
    *   -   ``fromJson(string $json): static``
        -   Factory: checks the protocol version and returns the matching
            response object. A mismatching version yields a
            :ref:`SocialServerVersionMismatchResponse
            <reference-version-mismatch-response>`, an unknown type a
            :ref:`SocialServerUnrecognizedResponse
            <reference-unrecognized-response>`. Throws an ``\Exception`` only on
            corrupted data (code ``1684785549``).
    *   -   ``getVersion(): int``
        -   Protocol version of the response.

..  _reference-post-response:

v2\\Response\\SocialServerPostResponse
--------------------------------------

Response containing a single post.

*   ``getPost(): Post`` – returns the contained post.

..  _reference-posts-response:

v2\\Response\\SocialServerPostsResponse
---------------------------------------

Response containing multiple posts including pagination.

..  list-table::
    :header-rows: 1
    :widths: 40 60

    *   -   Method
        -   Description
    *   -   ``getPosts(): Posts``
        -   Collection of the contained posts.
    *   -   ``getNext(): string``
        -   Cursor for the next page (``nextPage``).
    *   -   ``getPrevious(): string``
        -   Cursor for the previous page (``previousPage``).

..  _reference-accounts-response:

v2\\Response\\SocialServerAccountsResponse
------------------------------------------

Response containing all accounts the authenticated user has connected on the
social server, grouped by network.

*   ``getAccounts(): Accounts`` – the contained collection.

..  _reference-error-response:

v2\\Response\\SocialServerErrorResponse
---------------------------------------

Error response of the social server.

..  list-table::
    :header-rows: 1
    :widths: 40 60

    *   -   Method
        -   Description
    *   -   ``getCode(): int``
        -   Composite error code (prefix ``5550``).
    *   -   ``getMessage(): string``
        -   Error message.

..  _reference-rate-limit-response:

v2\\Response\\SocialServerRateLimitResponse
-------------------------------------------

Response of the social server when a request limit has been reached. Extends
:ref:`SocialServerErrorResponse <reference-error-response>`, so ``getCode()`` and
``getMessage()`` are available as well – ``getCode()`` always returns
``55501788307200``, and ``getMessage()`` already spells out the point in time from
which the service can be used again.

..  list-table::
    :header-rows: 1
    :widths: 40 60

    *   -   Method
        -   Description
    *   -   ``getRetryAfter(): \DateTimeImmutable``
        -   Point in time from which requests are allowed again, in the local
            time zone.
    *   -   ``getRetryAfterTimestamp(): int``
        -   The same point in time as a unix timestamp.
    *   -   ``getRetryAfterSeconds(): int``
        -   Seconds until then. Suitable for a ``Retry-After`` header or a wait
            before retrying.
    *   -   ``getLimit(): int``
        -   Number of requests allowed within the interval.
    *   -   ``getInterval(): string``
        -   The interval of the limit, e.g. ``1 minute``.
    *   -   ``getScope(): string``
        -   Which limit was hit: ``profile`` for requests to the same social
            media profile, ``general`` for the overall limit.

..  _reference-version-mismatch-response:

v2\\Response\\SocialServerVersionMismatchResponse
-------------------------------------------------

Response whose protocol version does not fit the expected one. Extends
:ref:`SocialServerErrorResponse <reference-error-response>`; ``getCode()`` always
returns ``55501652117309`` and ``getMessage()`` names both versions.

..  list-table::
    :header-rows: 1
    :widths: 40 60

    *   -   Method
        -   Description
    *   -   ``getExpectedVersion(): int``
        -   Protocol version that was expected (currently ``2``).
    *   -   ``getReceivedVersion(): int``
        -   Protocol version the answer was delivered with. Identical to
            ``getVersion()``.

..  _reference-unrecognized-response:

v2\\Response\\SocialServerUnrecognizedResponse
----------------------------------------------

Response whose type is unknown and that carries neither ``code`` nor ``message``.
Extends :ref:`SocialServerErrorResponse <reference-error-response>`;
``getCode()`` always returns ``55501741293955``.

..  list-table::
    :header-rows: 1
    :widths: 40 60

    *   -   Method
        -   Description
    *   -   ``getReceivedType(): string``
        -   The type the answer stated, or an empty string if it did not carry
            one.
    *   -   ``getPayload(): array``
        -   Raw data of the answer, e.g. for the log.
