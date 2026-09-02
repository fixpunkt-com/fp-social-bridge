..  include:: /Includes.rst.txt

..  _usage:

=====
Usage
=====

This extension describes the protocol between the **fixpunkt social server**
(server side) and a TYPO3 instance such as `fp_social
<https://github.com/fixpunkt-com/fp-social>`__ (client side). Both sides use the
same classes, so that creation and evaluation are guaranteed to use the same
format.

..  contents::
    :local:

Request flow
============

#.  The client side (e.g. ``fp_social``) requests posts or the available
    accounts from the social server.
#.  The social server gathers the data and **creates a response object**. Which
    of the five types is created depends on the result:

    *   a single post → :ref:`SocialServerPostResponse <reference-post-response>`
    *   multiple posts (with pagination) → :ref:`SocialServerPostsResponse
        <reference-posts-response>`
    *   the connected accounts → :ref:`SocialServerAccountsResponse
        <reference-accounts-response>`
    *   an error → :ref:`SocialServerErrorResponse <reference-error-response>`
    *   a reached request limit → :ref:`SocialServerRateLimitResponse
        <reference-rate-limit-response>`

#.  The server serializes the object via ``toArray()`` and ``json_encode()`` and
    sends the JSON to the client.
#.  The client passes the JSON to the factory :ref:`SocialServerResponse\:\:fromJson()
    <reference-social-server-response>` and receives the matching, typed object
    back. If the answer does not fit, the factory builds the fitting response
    itself:

    *   a mismatching protocol version → :ref:`SocialServerVersionMismatchResponse
        <reference-version-mismatch-response>`
    *   an unknown response type → :ref:`SocialServerUnrecognizedResponse
        <reference-unrecognized-response>`

Every response carries the fully qualified class name in the ``type`` field and
the protocol version (currently ``2``) in the ``version`` field. Based on these
two fields the factory decides which object to reconstruct and checks version
compatibility.

..  note::

    All of the following examples use anonymized sample data (``example.com``,
    fictional IDs). The format matches that of real responses.

..  _usage-post-fields:

Structure of a post object
==========================

Before we get to the responses, it is worth looking at the JSON of a single
:ref:`Post <reference-post>`, since some fields have a particular format here:

..  list-table::
    :header-rows: 1
    :widths: 20 80

    *   -   Field
        -   Format
    *   -   ``headline``
        -   Headline. Empty (``""``) for many sources (e.g. Facebook).
    *   -   ``message``
        -   HTML text. May contain ``<br />`` and ``<a>`` tags as well as emoji
            placeholders of the form ``{emoji:9728}`` (Unicode code point).
    *   -   ``update_time``
        -   Serialized ``\DateTime`` object with the keys ``date``,
            ``timezone_type`` and ``timezone``.
    *   -   ``hashtags``
        -   List of strings **without** a leading ``#`` (e.g. ``"summer"``).
    *   -   ``mentions``
        -   List of objects with ``displayName`` and ``systemName``; empty when
            there are no mentions.
    *   -   ``pictures``
        -   List of picture URLs.

..  _usage-post-response:

Example 1: A single post (SocialServerPostResponse)
--------------------------------------------------

Server side – create and output as JSON:

..  code-block:: php

    use Fixpunkt\FpSocialBridge\v2\Data\Post;
    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerPostResponse;
    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerResponse;

    $post = new Post(
        id: '100000000000001_200000000000001',
        headline: '',
        message: 'Summertime! Here is our favourite recipe for hot days.<br />'
            . "\n" . 'Have fun trying it out {emoji:9728} '
            . '<a href=\'https://social.example.com/hashtag/recipe\'>#recipe</a>',
        post_url: 'https://social.example.com/100000000000001/posts/200000000000001',
        update_time: new \DateTime('2026-06-27 08:00:38+00:00'),
        link: 'https://social.example.com/100000000000001/posts/200000000000001',
        hashtags: ['recipe', 'summer', 'drinks', 'tip'],
        mentions: [],
        pictures: ['https://cdn.example.com/media/image-1.jpg'],
    );

    $response = new SocialServerPostResponse(SocialServerResponse::version, $post);

    echo json_encode($response->toArray());

The resulting JSON (values shortened):

..  code-block:: json

    {
        "type": "Fixpunkt\\FpSocialBridge\\v2\\Response\\SocialServerPostResponse",
        "version": 2,
        "post": {
            "id": "100000000000001_200000000000001",
            "headline": "",
            "message": "Summertime! ...<br />\n... <a href='...'>#recipe</a>",
            "post_url": "https://social.example.com/100000000000001/posts/200000000000001",
            "update_time": {
                "date": "2026-06-27 08:00:38.000000",
                "timezone_type": 1,
                "timezone": "+00:00"
            },
            "link": "https://social.example.com/100000000000001/posts/200000000000001",
            "hashtags": ["recipe", "summer", "drinks", "tip"],
            "mentions": [],
            "pictures": ["https://cdn.example.com/media/image-1.jpg"]
        }
    }

Client side – evaluate:

..  code-block:: php

    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerPostResponse;
    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerResponse;

    $response = SocialServerResponse::fromJson($json);

    if ($response instanceof SocialServerPostResponse) {
        $post = $response->getPost();
        echo $post->getMessage();

        foreach ($post->getHashtags() as $hashtag) {
            echo '#' . $hashtag; // the leading # is not part of the value
        }
    }

..  note::

    If a post contains mentions, the ``mentions`` field looks like this:

    ..  code-block:: json

        "mentions": [
            {"displayName": "Example Organization", "systemName": "300000000000001"}
        ]

..  _usage-posts-response:

Example 2: Multiple posts with pagination (SocialServerPostsResponse)
--------------------------------------------------------------------

This is the most common response: a list of posts plus the cursors for paging.
``previousPage`` is empty on the first page; ``nextPage`` contains the full URL
for the next fetch (or is empty when no further page exists).

Server side – create and output as JSON:

..  code-block:: php

    use Fixpunkt\FpSocialBridge\v2\Data\Post;
    use Fixpunkt\FpSocialBridge\v2\Data\Posts;
    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerPostsResponse;
    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerResponse;

    $posts = new Posts([
        new Post(
            id: '100000000000001_200000000000001',
            headline: '',
            message: 'Summertime! Here is our favourite recipe for hot days.',
            post_url: 'https://social.example.com/100000000000001/posts/200000000000001',
            update_time: new \DateTime('2026-06-27 08:00:38+00:00'),
            link: 'https://social.example.com/100000000000001/posts/200000000000001',
            hashtags: ['recipe', 'summer', 'drinks', 'tip'],
            mentions: [],
            pictures: ['https://cdn.example.com/media/image-1.jpg'],
        ),
        new Post(
            id: '100000000000001_200000000000002',
            headline: '',
            message: 'We will soon present our new project – stay tuned!',
            post_url: 'https://social.example.com/100000000000001/posts/200000000000002',
            update_time: new \DateTime('2026-06-24 17:00:14+00:00'),
            link: 'https://social.example.com/100000000000001/posts/200000000000002',
            hashtags: ['project', 'news', 'outlook'],
            mentions: [],
            pictures: ['https://cdn.example.com/media/image-2.jpg'],
        ),
    ]);

    $response = new SocialServerPostsResponse(
        SocialServerResponse::version,
        $posts,
        previous: '',
        next: 'https://social-server.example.com/networks/example/posts?tx_fpsocialserver_show%5Bafter%5D=QVFI...&tx_fpsocialserver_show%5Bversion%5D=2&cHash=0123456789abcdef0123456789abcdef',
    );

    echo json_encode($response->toArray());

The resulting JSON (posts and ``nextPage`` shortened):

..  code-block:: json

    {
        "type": "Fixpunkt\\FpSocialBridge\\v2\\Response\\SocialServerPostsResponse",
        "version": 2,
        "posts": [
            {
                "id": "100000000000001_200000000000001",
                "headline": "",
                "message": "Summertime! ...",
                "post_url": "https://social.example.com/100000000000001/posts/200000000000001",
                "update_time": {
                    "date": "2026-06-27 08:00:38.000000",
                    "timezone_type": 1,
                    "timezone": "+00:00"
                },
                "link": "https://social.example.com/100000000000001/posts/200000000000001",
                "hashtags": ["recipe", "summer", "drinks", "tip"],
                "mentions": [],
                "pictures": ["https://cdn.example.com/media/image-1.jpg"]
            },
            {
                "id": "100000000000001_200000000000002",
                "headline": "",
                "message": "We will soon present our new project – stay tuned!",
                "...": "..."
            }
        ],
        "requests": {
            "previousPage": "",
            "nextPage": "https://social-server.example.com/networks/example/posts?tx_fpsocialserver_show%5Bafter%5D=QVFI...&tx_fpsocialserver_show%5Bversion%5D=2&cHash=0123456789abcdef0123456789abcdef"
        }
    }

Client side – evaluate and iterate over the collection:

..  code-block:: php

    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerPostsResponse;
    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerResponse;

    $response = SocialServerResponse::fromJson($json);

    if ($response instanceof SocialServerPostsResponse) {
        foreach ($response->getPosts() as $post) {
            echo $post->getMessage();
        }

        // Cursors (URLs) for the next and previous page
        $next = $response->getNext();
        $previous = $response->getPrevious();
    }

..  note::

    :ref:`Posts <reference-posts>` implements ``Iterator`` and ``Countable`` and
    can therefore be iterated directly with ``foreach`` and counted with
    ``count()``.

..  _usage-error-response:

Example 3: Error response (SocialServerErrorResponse)
----------------------------------------------------

If an error occurs on the social server, it creates a
:ref:`SocialServerErrorResponse <reference-error-response>` instead of a data
response.

Server side – create and output as JSON:

..  code-block:: php

    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerErrorResponse;
    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerResponse;

    $response = new SocialServerErrorResponse(
        SocialServerResponse::version,
        code: 42,
        message: 'The requested account does not exist.',
    );

    echo json_encode($response->toArray());

The resulting JSON:

..  code-block:: json

    {
        "type": "Fixpunkt\\FpSocialBridge\\v2\\Response\\SocialServerErrorResponse",
        "version": 2,
        "code": 555042,
        "message": "The requested account does not exist."
    }

..  note::

    The supplied error code is combined with the prefix ``5550`` in the
    constructor and stored as an ``int``. So ``code: 42`` becomes ``555042``.

Client side – evaluate:

..  code-block:: php

    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerErrorResponse;
    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerResponse;

    $response = SocialServerResponse::fromJson($json);

    if ($response instanceof SocialServerErrorResponse) {
        throw new \RuntimeException(
            $response->getMessage(),
            $response->getCode()
        );
    }

..  _usage-accounts-response:

Example 4: The available accounts (SocialServerAccountsResponse)
---------------------------------------------------------------

The endpoint ``POST /api/accounts`` returns every account the authenticated user
has connected on the social server, grouped by network. The client side uses it
to prefill the account, channel and label fields of an account record instead of
having editors type page ids by hand.

Only networks whose access is established on the server appear here – Facebook,
Instagram, Instagram (direct login) and LinkedIn. Wordpress, Youtube and Bluesky
are configured entirely on the client side and are therefore absent.

Server side – create and output as JSON:

..  code-block:: php

    use Fixpunkt\FpSocialBridge\v2\Data\Account;
    use Fixpunkt\FpSocialBridge\v2\Data\Accounts;
    use Fixpunkt\FpSocialBridge\v2\Data\Channel;
    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerAccountsResponse;
    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerResponse;

    $accounts = new Accounts([
        'Fixpunkt\FpSocialServer\Networks\Facebook\Connector' => [
            new Account(
                uid: 12,
                network: 'Fixpunkt\FpSocialServer\Networks\Facebook\Connector',
                networkKey: 'facebook',
                networkName: 'Facebook',
                displayName: 'Example Organization',
                username: '100000000000001',
                email: 'office@example.com',
                expires: null,
                expired: false,
                channels: [
                    new Channel('200000000000001', 'Example Page'),
                    new Channel('200000000000002', 'Example Campaign'),
                ],
            ),
        ],
    ]);

    $response = new SocialServerAccountsResponse(SocialServerResponse::version, $accounts);

    echo json_encode($response->toArray());

The resulting JSON:

..  code-block:: json

    {
        "type": "Fixpunkt\\FpSocialBridge\\v2\\Response\\SocialServerAccountsResponse",
        "version": 2,
        "accounts": {
            "Fixpunkt\\FpSocialServer\\Networks\\Facebook\\Connector": [
                {
                    "uid": 12,
                    "network": "Fixpunkt\\FpSocialServer\\Networks\\Facebook\\Connector",
                    "networkKey": "facebook",
                    "networkName": "Facebook",
                    "displayName": "Example Organization",
                    "username": "100000000000001",
                    "email": "office@example.com",
                    "expires": null,
                    "expired": false,
                    "channels": [
                        {"id": "200000000000001", "name": "Example Page"},
                        {"id": "200000000000002", "name": "Example Campaign"}
                    ]
                }
            ],
            "Fixpunkt\\FpSocialServer\\Networks\\LinkedIn\\Connector": [
                {
                    "uid": 15,
                    "network": "Fixpunkt\\FpSocialServer\\Networks\\LinkedIn\\Connector",
                    "networkKey": "linkedin",
                    "networkName": "LinkedIn",
                    "displayName": "Alex Example",
                    "username": "AbC123dEf",
                    "email": "alex@example.com",
                    "expires": {
                        "date": "2026-12-24 09:15:00.000000",
                        "timezone_type": 3,
                        "timezone": "Europe/Berlin"
                    },
                    "expired": false,
                    "channels": [
                        {"id": "urn:li:organization:300001", "name": "Example GmbH"}
                    ]
                }
            ]
        }
    }

Client side – evaluate:

..  code-block:: php

    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerAccountsResponse;
    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerResponse;

    $response = SocialServerResponse::fromJson($json);

    if ($response instanceof SocialServerAccountsResponse) {
        foreach ($response->getAccounts() as $network => $accounts) {
            foreach ($accounts as $account) {
                foreach ($account->getChannels() as $channel) {
                    // channel field ← getId(), label field ← getName()
                    echo $account->getDisplayName() . ': ' . $channel->getName();
                }
            }
        }
    }

..  note::

    ``expires`` is ``null`` for networks whose access keys do not expire
    (Facebook, Instagram). For LinkedIn and Instagram (direct login) it is a
    serialized ``\DateTime`` in the same format as ``update_time`` on a post.

..  note::

    Instagram reuses the Facebook access key. The same account therefore shows up
    under both networks with the same ``uid`` but different ``channels`` –
    Facebook pages in one case, Instagram business accounts in the other.

..  _usage-rate-limit-response:

Example 5: A reached request limit (SocialServerRateLimitResponse)
-----------------------------------------------------------------

The social server limits how often the API may be called: requests to the same
social media profile and the overall number of requests are each capped per API
user. Once a cap is reached the server answers with HTTP ``429``, a
``Retry-After`` header and a
:ref:`SocialServerRateLimitResponse <reference-rate-limit-response>`.

Server side – create and output as JSON:

..  code-block:: php

    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerRateLimitResponse;
    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerResponse;

    $response = new SocialServerRateLimitResponse(
        SocialServerResponse::version,
        message: 'Das Anfragelimit für dieses Social-Media-Profil ist erreicht '
            . '(max. 10 Anfragen pro 1 minute). Weitere Anfragen sind ab '
            . '02.09.2026 14:23:45 möglich, also in 37 Sekunden.',
        retryAfter: 1788351825,
        retryAfterSeconds: 37,
        limit: 10,
        interval: '1 minute',
        scope: SocialServerRateLimitResponse::scopeProfile,
    );

    echo json_encode($response->toArray());

The resulting JSON:

..  code-block:: json

    {
        "type": "Fixpunkt\\FpSocialBridge\\v2\\Response\\SocialServerRateLimitResponse",
        "version": 2,
        "code": 55501788307200,
        "message": "Das Anfragelimit für dieses Social-Media-Profil ist erreicht (max. 10 Anfragen pro 1 minute). Weitere Anfragen sind ab 02.09.2026 14:23:45 möglich, also in 37 Sekunden.",
        "retryAfter": 1788351825,
        "retryAfterSeconds": 37,
        "limit": 10,
        "interval": "1 minute",
        "scope": "profile"
    }

Client side – wait instead of giving up:

..  code-block:: php

    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerRateLimitResponse;
    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerResponse;

    $response = SocialServerResponse::fromJson($json);

    if ($response instanceof SocialServerRateLimitResponse) {
        // Short blocks can be waited out, longer ones belong in the next run.
        if ($response->getRetryAfterSeconds() <= 10) {
            sleep($response->getRetryAfterSeconds() + 1);
            // ... retry the request
        }

        // Otherwise remember the point in time and try again later.
        $retryAt = $response->getRetryAfter();
    }

..  warning::

    ``SocialServerRateLimitResponse`` **extends**
    :ref:`SocialServerErrorResponse <reference-error-response>`. An
    ``instanceof SocialServerErrorResponse`` check therefore matches it as well,
    which is intentional: clients that do not know the type yet still see a
    readable error. If you want to handle a throttled request differently, check
    for ``SocialServerRateLimitResponse`` **first**.

..  _usage-unusable-response:

Example 6: An answer that cannot be used (version and unknown type)
-------------------------------------------------------------------

Two cases are not down to the content of the answer but to the answer itself not
fitting: its protocol version differs from the expected one, or its type is
unknown. For both, ``fromJson()`` returns a response object instead of throwing
-- so a single error branch on the client side is enough.

..  code-block:: php

    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerResponse;
    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerUnrecognizedResponse;
    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerVersionMismatchResponse;

    // Answer of a server that already speaks version 3
    $response = SocialServerResponse::fromJson($json);

    if ($response instanceof SocialServerVersionMismatchResponse) {
        // "Version of answer (3) does not fit request version (2)."
        $logger->warning($response->getMessage(), [
            'expected' => $response->getExpectedVersion(),
            'received' => $response->getReceivedVersion(),
        ]);
    }

    if ($response instanceof SocialServerUnrecognizedResponse) {
        // "The received response is not recognized: \"...\SocialServerStoryResponse\"."
        $logger->warning($response->getMessage(), [
            'type' => $response->getReceivedType(),
            'payload' => $response->getPayload(),
        ]);
    }

The server can send both types itself as well, e.g. if it recognizes the version
of a request as unsupported:

..  code-block:: php

    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerResponse;
    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerVersionMismatchResponse;

    $response = new SocialServerVersionMismatchResponse(
        version: 3,
        message: 'Version of answer (3) does not fit request version (2).',
        expectedVersion: SocialServerResponse::version,
    );

    echo json_encode($response->toArray());

The resulting JSON:

..  code-block:: json

    {
        "type": "Fixpunkt\\FpSocialBridge\\v2\\Response\\SocialServerVersionMismatchResponse",
        "version": 3,
        "code": 55501652117309,
        "message": "Version of answer (3) does not fit request version (2).",
        "expectedVersion": 2
    }

..  note::

    Both types are checked **before** the version check, just like
    :ref:`SocialServerRateLimitResponse <reference-rate-limit-response>`: an
    answer that reports a version problem has to be understood even when the
    versions are exactly what is diverging.

Handling all types together
===========================

In practice the client side does not know in advance which type will come back.
The factory always returns the matching object; ``instanceof`` is used to tell
them apart:

..  code-block:: php

    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerResponse;
    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerPostsResponse;
    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerPostResponse;
    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerAccountsResponse;
    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerErrorResponse;
    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerRateLimitResponse;
    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerUnrecognizedResponse;
    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerVersionMismatchResponse;

    $response = SocialServerResponse::fromJson($json);

    // Before the error check: these three are subclasses of it.
    if ($response instanceof SocialServerRateLimitResponse) {
        // ... wait or postpone, see above
    }

    if (
        $response instanceof SocialServerVersionMismatchResponse
        || $response instanceof SocialServerUnrecognizedResponse
    ) {
        // ... log the answer, see above
    }

    if ($response instanceof SocialServerErrorResponse) {
        throw new \RuntimeException(
            $response->getMessage(),
            $response->getCode()
        );
    }

    if ($response instanceof SocialServerPostsResponse) {
        foreach ($response->getPosts() as $post) {
            echo $post->getMessage();
        }
        $next = $response->getNext(); // URL for the next page
    }

    if ($response instanceof SocialServerPostResponse) {
        $post = $response->getPost();
    }

    if ($response instanceof SocialServerAccountsResponse) {
        foreach ($response->getAccounts() as $network => $accounts) {
            // ...
        }
    }

..  note::

    ``fromJson()`` only throws an ``\Exception`` if the data is corrupted (code
    ``1684785549``) -- a mismatching version and an unknown type each come back
    as a response object. See :ref:`SocialServerResponse
    <reference-social-server-response>`.
