# Correct the ORM 2 minimum to 2.11

Runtime verification found that DoctrineBundle 2.7.0 calls an ORM configuration method absent from ORM 2.10, including its final 2.10.5 patch; later DoctrineBundle 2.7 patches explicitly require ORM 2.11 or newer. The user approved correcting the bundle requirement to ORM 2.11 or ORM 3 rather than retaining an installable but nonfunctional lower bound or adding a vendor shim. This supersedes the earlier requirement to preserve the ORM 2.10 minimum; PHP and Symfony ranges, public APIs, and the database schema remain unchanged.

The lower-bound CI profile exercises ORM 2.11.0 with DoctrineBundle 2.7.2 and DBAL 2.13.9. See the upstream [DoctrineBundle 2.7.0 integration](https://github.com/doctrine/DoctrineBundle/blob/2.7.0/DependencyInjection/DoctrineExtension.php) and [2.7.2 constraints](https://github.com/doctrine/DoctrineBundle/blob/2.7.2/composer.json).
