[![Build Status](https://travis-ci.org/Sqon/app.svg?branch=master)](https://travis-ci.org/Sqon/app)
[![Coverage Status](https://coveralls.io/repos/github/Sqon/app/badge.svg?branch=master)](https://coveralls.io/github/Sqon/app?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/4f904a31-2a1e-4d65-be36-4c7530ed5c11/analyses/1/mini.png)](https://insight.sensiolabs.com/projects/4f904a31-2a1e-4d65-be36-4c7530ed5c11/analyses/1)

![Sqon](misc/media/logo.png)
============================

- [Usage](#usage)
- [Requirements](#requirements)
- [Install](#install)
- [Documentation](#documentation)
- [License](#license)
- [Logo](#logo)

Usage
-----

> The following uses a Symfony 3 project as an example. Sqon itself is project agnostic, so it will work on any PHP project regardless of how it is implemented.

First, you will need to create a build configuration file.

```yaml
# The build configuration settings.
sqon:

    # Enables GZIP compression for all files set.
    compression: GZIP

    # Run the console with the Sqon is executed.
    main: 'bin/console'

    # The paths to set in the Sqon.
    paths: ['.']

    # Name the resulting file "symfony.sqon"
    output: 'symfony.sqon'

    # Make sure the Sqon starts with a shebang line.
    shebang: '#!/usr/bin/env php'
```

Then, you run the command to create a new Sqon.

    $ sqon create

> You can use different verbosity levels (i.e. `-v`, `-vv`, and `-vvv`) to more information about the build process. Please note that this can slow down your build time but will not impact the performance of the built Sqon.

When you run `php symfony.sqon` you will see the Symfony 3 console.

Requirements
------------

- Current [PHP][] or [HHVM][] long term support release.
    - `pdo_sqlite`
    - `bz2` (if using bzip2 compression)
    - `zlib` (if using gzip compression)

[HHVM]: https://docs.hhvm.com/hhvm/installation/introduction#prebuilt-packages__lts-releases
[PHP]: https://secure.php.net/supported-versions.php

Install
-------

You will need to download the executable from the [releases][] page.

This project is intentionally not available on Packagist. This library
requirements for this project may conflict with other requirements for
your project. By using a standalone application, these conflicts are
avoided.

[releases]: https://github.com/Sqon/app/releases

Documentation
-------------

All of the documentation has been written into the commands themselves. To access this documentation, please run the desired command followed by the `-h` or `--help` option:

    $ ./sqon create -h

License
-------

This project is released under both the [MIT][] and [Apache 2.0][] licenses.

[MIT]: misc/licenses/MIT.txt
[Apache 2.0]: misc/licenses/Apache%202.0.txt

Logo
----

The scone in the logo was created by [anbileru adaleru][] from the [Noun Project][].

[anbileru adaleru]: https://thenounproject.com/pronoun/
[Noun Project]: https://thenounproject.com/
