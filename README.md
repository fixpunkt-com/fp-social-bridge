# fp_social_bridge

Lean library extension providing the **data-transfer and response objects**
exchanged between the fixpunkt social server and TYPO3. It encapsulates the
protocol (currently version 2) as typed PHP objects and is used, among others,
by [`fp_social`](https://packagist.org/packages/fixpunkt/fp-social).

The extension deliberately contains **no** business logic of its own, no TCA and
no plugins – only the shared classes.

## Installation

```bash
composer require fixpunkt/fp-social-bridge
```

## Documentation

The full documentation (introduction, installation, usage and class reference)
is available at:

**<https://docs.typo3.org/p/fixpunkt/fp-social-bridge/main/en-us/>**

## License

GPL-2.0-or-later
