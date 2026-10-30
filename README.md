ShayganAffiliateBundle
===================
A simple **Symfony Affiliate Bundle** compatible with **Symfony 5.4, 6.4, 7.x, and 8.x**.

This ~~FOSUserBundle compatible~~ Bundle tracks referrals with query string
parameter and detect referred registrations via cookie. You can easily ask
the bundle for commission amount (if there is any referrer) and apply the amount
to referrer's user account.

Your Order Object needs to implements \Shaygan\AffiliateBundle\Model\PurchaseInterface.

## Requirements

- PHP 8.1 or higher
- Symfony 5.4, 6.4, 7.x, or 8.x on a compatible PHP runtime
- Doctrine ORM 2.11+ or 3.x

## Install

Via Composer

``` bash
$ composer require shaygan/affiliate-bundle
```

### Symfony 5.4 / 6.4 / 7.x / 8.x

If you're using Symfony Flex, the bundle will be automatically registered. Otherwise, register it manually in `config/bundles.php`:

```php
return [
    // ...
    Shaygan\AffiliateBundle\ShayganAffiliateBundle::class => ['all' => true],
];
```

Import the bundle's route resource in the application's routing configuration:

```yaml
shaygan_affiliate:
    resource: '@ShayganAffiliateBundle/Resources/config/routing.yml'
```

This preserves the existing `/affiliate/` and `/affiliate/report` paths and route names across Symfony versions without depending on version-specific controller attribute loaders.

### Older Symfony Versions

For Symfony versions below 5.4, use an earlier release of this bundle.

## Configure the bundle

This bundle was designed to just work out of the box. The only thing you have to configure in order to get this bundle up and running is your commission type amount and count.

```yaml
# config/packages/shaygan_affiliate.yaml

shaygan_affiliate:
    programs:
        membership_upgrade:
            type: fixed-amount
            first_commission_amount: 3
            commission_amount: 1
            max_count: 10
        purchase:
            type: percentage
            first_commission_percent: 15
            commission_percent: 10
            max_count: 10
```
By default it look for ?ref=REFERRER_ID in URLs the *ref* is configurable

## Usage

Your application's Doctrine-mapped purchase entity must implement
`Shaygan\AffiliateBundle\Model\PurchaseInterface`: provide its identifier, referred
user, and purchase price through `getId()`, `getReferredUser()`, and
`getPurchasePrice()`. Create and populate that entity in your application, then
pass it to a service with injected dependencies:

```php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Shaygan\AffiliateBundle\Entity\Purchase;
use Shaygan\AffiliateBundle\Model\Affiliate;
use Shaygan\AffiliateBundle\Model\PurchaseInterface;

final class PurchaseCommissionHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Affiliate $affiliate
    ) {
    }

    public function record(PurchaseInterface $order): ?Purchase
    {
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $this->affiliate->getPurchaseCommission($order, 'purchase');
    }
}
```

Symfony can autowire this application service. The explicit `purchase` program
matches the percentage configuration above. The result is `null` when the purchase
is ineligible; otherwise use the returned commission's `getCommissionAmount()` and
`getReferrer()` to apply payment through your application's own account logic.

## Testing

The integration suite boots a small consumer application and tests public reporting
services and routes with real Doctrine persistence. Install the shared Symfony 6.4 /
DBAL 3 baseline for either ORM major using PHP 8.1:

```bash
ORM_CONSTRAINT='^2.20' bash Tests/resolve-baseline.sh
# Or: ORM_CONSTRAINT='^3.0' bash Tests/resolve-baseline.sh
composer test
```

Without `DATABASE_URL`, tests use a temporary SQLite database. To exercise MySQL:

```bash
DATABASE_URL='mysql://root:test@127.0.0.1:3306/affiliate_test?serverVersion=8.4&charset=utf8mb4' composer test
```

Use a dedicated disposable database: the integration suite drops and recreates the
bundle tables before each test. GitHub Actions runs both databases for every compatibility profile. The full compatibility profiles below run both databases.

## Upgrade to ORM 3

1. Applications on ORM 2.10 must first update to ORM 2.11 or newer. The earlier 2.10 lower bound could resolve with DoctrineBundle 2.7.0 but could not boot because of an upstream configuration API mismatch. This correction is recorded in [ADR 0002](docs/adr/0002-correct-orm2-minimum.md).
2. Update this bundle while the application still uses ORM 2, then run the application's integration checks.
3. Update to a compatible ORM 3 / DoctrineBundle / PHP combination and rerun those checks. This bundle introduces no table, column, identifier, or foreign-key changes and requires no bundle schema migration. Review any migrations caused by other upgraded dependencies independently.

The [existing-data check](Tests/Compatibility/README.md) reproduces an ORM 2 seed followed by ORM 3 reads and updates on the same disposable MySQL database without migrations.

## Compatibility profiles

The matrix checks public reporting routes, actual HTTP referral requests, registration events, percentage commission persistence and eligibility, entity mappings, and round-trip data. These are valid dependency combinations, not every Cartesian combination of the advertised version ranges. PHP 8.1 is retained; Symfony 7 profiles use PHP 8.2, and Symfony 8 / DoctrineBundle 3 use PHP 8.4.

| Profile | PHP | Symfony | ORM | DBAL | DoctrineBundle |
| --- | --- | --- | --- | --- | --- |
| lower-orm2 | 8.1 | 5.4 | 2.11.0 | 2 | 2.7.2 |
| sf64-orm2 | 8.1 | 6.4 | Compatible 2.x | 3 | Compatible 2.x |
| lower-orm3 | 8.1 | 5.4 | 3.0.0 | 3 | 2.13.x |
| sf64-orm3 | 8.1 | 6.4 | Compatible 3.x | 3 | Compatible 2.x |
| sf7-orm2 | 8.2 | 7.x | Compatible 2.x | 3 | Compatible 2.x |
| sf7-orm3 | 8.2 | 7.x | Compatible 3.x | 4 | Compatible 2.x |
| sf8-orm3 | 8.4 | 8.x | Compatible 3.x | 4 | Compatible 3.x |

Run a profile using its listed PHP runtime, PDO SQLite/MySQL extensions, and Composer:

```bash
bash Tests/resolve-matrix.sh lower-orm3
composer test
DATABASE_URL='mysql://root:test@127.0.0.1:3306/affiliate_test?serverVersion=8.4&charset=utf8mb4' composer test
```

The resolver temporarily constrains dependencies for the selected profile without rewriting the package's declared production ranges. It prints resolved package versions; CI logs preserve that evidence. MySQL 8.4 is the integration target, with SQLite for fast checks. MariaDB compatibility is not certified. Tests replace their configured database tables, so always use a dedicated disposable database.

PHPUnit 10 owns error handling on every profile; the test extension uses Symfony's project/vendor classifier to turn project self/direct deprecations into failing PHPUnit runner warnings. This includes suppressed and unsuppressed notices. Vendor-to-vendor notices remain visible in PHPUnit's deprecation report and do not fail the command. The Bridge's automatic handler is explicitly disabled because Symfony 7/8 skips it with PHPUnit 10. A subprocess regression exercises the actual configured PHPUnit command for clean runs and each allowed/failing deprecation category on every compatibility profile.

## Known business limitations

- Fixed commission configuration uses `fixed-amount`, while the existing calculation branches check `fixed-price`; repeat fixed commissions also read the first commission amount. Tests certify fixed-record persistence, not this deferred calculation workflow.
- A registration event dispatched inside a controller clears tracking immediately at the public registration boundary and expires the response cookie. The later response recorder can restore session tracking from the incoming referral cookie. This existing request-end behavior is preserved and documented rather than silently changed by the ORM compatibility work; normal HTTP attribution and one-registration behavior are tested with the incoming cookie intact.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
