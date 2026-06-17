# fp_social_bridge

Schlanke Bibliotheks-Extension mit den **Datentransfer- und Response-Objekten**,
die zwischen dem fixpunkt Social-Server und TYPO3 ausgetauscht werden. Sie
kapselt das Protokoll (aktuell Version 2) als typisierte PHP-Objekte und wird
u. a. von [`fp_social`](https://github.com/fixpunkt-com/fp-social) genutzt.

Die Extension enthält bewusst **keine** eigene Geschäftslogik, kein TCA und
keine Plugins – nur die gemeinsam genutzten Klassen, damit Server- und
Client-Seite dasselbe Format verwenden.

## Inhalt

| Klasse | Zweck |
|---|---|
| `SerializableInterface` | Vertrag: `fromJson()`, `fromArray()`, `toArray()` |
| `v2\Data\Post` | Einzelner Post (id, headline, message, url, Datum, Hashtags, Mentions, Bilder) |
| `v2\Data\Posts` | Iterierbare, zählbare Sammlung von `Post`-Objekten |
| `v2\Response\SocialServerResponse` | Abstrakte Basis inkl. Versionsprüfung und Factory `fromJson()` |
| `v2\Response\SocialServerPostResponse` | Antwort mit einem einzelnen Post |
| `v2\Response\SocialServerPostsResponse` | Antwort mit mehreren Posts + Pagination (`previousPage`/`nextPage`) |
| `v2\Response\SocialServerErrorResponse` | Fehlerantwort (Code mit Präfix `5550`, Meldung) |

## Installation

```bash
composer require fixpunkt/fp-social-bridge
```

## Verwendung

Eine vom Social-Server empfangene JSON-Antwort wird über die Factory der
Basisklasse in das passende Response-Objekt überführt:

```php
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
        echo $post->getHeadline() . ': ' . $post->getMessage();
    }
    $next = $response->getNext(); // Cursor für die nächste Seite
}

if ($response instanceof SocialServerPostResponse) {
    $post = $response->getPost();
}
```

`fromJson()` prüft die Protokollversion und wirft eine `\Exception`, wenn die
Daten beschädigt sind oder die Version nicht passt.

## Voraussetzungen

* TYPO3 **12.4 – 13.4**
* PHP **8.1 – 8.4**

## Entwicklung

Coding Standards und statische Analyse laufen über isolierte Toolchains in
`.build/` (TYPO3 13.4) bzw. `.build-v12/` (TYPO3 12.4) – siehe
[`DEVELOPMENT.md`](DEVELOPMENT.md).

## Lizenz

GPL-2.0-or-later

## Changelog

### 1.2.1
* [FIX] `SocialServerErrorResponse`: zusammengesetzter Fehlercode wird nun
  korrekt als `int` gespeichert (verhindert einen TypeError unter
  `declare(strict_types=1)`).
* [TASK] `declare(strict_types=1)` und PSR-12 in allen Klassen, ungenutzten
  Import entfernt, korrigierter PHPDoc-Rückgabetyp in `getPost()`.
* [TASK] Release-Metadaten (`ext_emconf.php`, Lizenz) sowie CI mit PHPStan und
  php-cs-fixer ergänzt.
