..  include:: /Includes.rst.txt

..  _start:

======================
Fixpunkt Social Bridge
======================

:Extension key:
    fp_social_bridge

:Package name:
    fixpunkt/fp-social-bridge

:Version:
    |release|

:Language:
    en

:Author:
    Yannik Börgener & the TYPO3 community

:License:
    This document is published under the
    `Creative Commons BY 4.0 <https://creativecommons.org/licenses/by/4.0/>`__
    license.

:Rendered:
    |today|

----

Lean library extension providing the **data-transfer and response objects**
exchanged between the fixpunkt social server and TYPO3. It encapsulates the
protocol (currently version 2) as typed PHP objects and is used, among others,
by `fp_social <https://github.com/fixpunkt-com/fp-social>`__.

The extension deliberately contains **no** business logic of its own, no TCA and
no plugins – only the shared classes, so that the server and client side use the
same format.

----

**Table of Contents:**

..  toctree::
    :maxdepth: 2
    :titlesonly:

    Introduction/Index
    Installation/Index
    Usage/Index
    Reference/Index
    Changelog/Index
