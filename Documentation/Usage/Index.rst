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

#.  The client side (e.g. ``fp_social``) requests posts from the social server.
#.  The social server gathers the data and **creates a response object**. Which
    of the three types is created depends on the result:

    *   a single post → :ref:`SocialServerPostResponse <reference-post-response>`
    *   multiple posts (with pagination) → :ref:`SocialServerPostsResponse
        <reference-posts-response>`
    *   an error → :ref:`SocialServerErrorResponse <reference-error-response>`

#.  The server serializes the object via ``toArray()`` and ``json_encode()`` and
    sends the JSON to the client.
#.  The client passes the JSON to the factory :ref:`SocialServerResponse\:\:fromJson()
    <reference-social-server-response>` and receives the matching, typed object
    back.

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

Handling all types together
===========================

In practice the client side does not know in advance which type will come back.
The factory always returns the matching object; ``instanceof`` is used to tell
them apart:

..  code-block:: php

    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerResponse;
    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerPostsResponse;
    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerPostResponse;
    use Fixpunkt\FpSocialBridge\v2\Response\SocialServerErrorResponse;

    $response = SocialServerResponse::fromJson($json);

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

..  note::

    ``fromJson()`` checks the protocol version and throws an ``\Exception`` if
    the data is corrupted or the version does not match. See
    :ref:`SocialServerResponse <reference-social-server-response>`.
