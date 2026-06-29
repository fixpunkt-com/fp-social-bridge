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

Responses
=========

The response objects in the ``v2\Response`` namespace represent the various
response types of the social server. The base class factory turns a received
JSON response into the matching object.

..  _reference-social-server-response:

v2\\Response\\SocialServerResponse
---------------------------------

Abstract base class of all server responses.

..  list-table::
    :header-rows: 1
    :widths: 40 60

    *   -   Method
        -   Description
    *   -   ``fromJson(string $json): static``
        -   Factory: checks the protocol version and returns the matching
            response object. Throws an ``\Exception`` on corrupted data or a
            mismatching version.
    *   -   ``getVersion(): int``
        -   Protocol version of the response.

..  _reference-post-response:

v2\\Response\\SocialServerPostResponse
-------------------------------------

Response containing a single post.

*   ``getPost(): Post`` – returns the contained post.

..  _reference-posts-response:

v2\\Response\\SocialServerPostsResponse
--------------------------------------

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

..  _reference-error-response:

v2\\Response\\SocialServerErrorResponse
--------------------------------------

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
