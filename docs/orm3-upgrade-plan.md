# Doctrine ORM 3 upgrade preparation

The accepted scope is recorded in [ADR 0001](adr/0001-doctrine-orm-upgrade-compatibility.md). This plan prepares applications to install the bundle while still using ORM 2, then upgrade Doctrine independently.

The implementation specification is published as [GitHub issue #6](https://github.com/Shaygan/AffiliateBundle/issues/6), labeled `ready-for-agent`.

## Compatibility commitments

- Retain the declared PHP, Symfony, and DoctrineBundle ranges, with the user-approved ORM 2 minimum corrected to 2.11 as recorded in [ADR 0002](adr/0002-correct-orm2-minimum.md). Verify valid dependency combinations; the ranges do not imply that every possible combination is supported upstream.
- Preserve public service names, methods, interfaces, entity names, tables, columns, identifiers, and foreign keys.
- Use MySQL for integration coverage and SQLite for fast checks. Do not introduce a MariaDB compatibility claim.
- Include minimal fixes needed to boot and use the bundle on the advertised Symfony versions. Keep unrelated business behavior changes separate.

## Implementation

1. Replace the two `$this->_em` accesses in `ReferralRegistrationRepository` with `getEntityManager()`.
2. Replace all four bundle-style DQL entity aliases with fully qualified entity names: two referral counter updates and two registration repository queries.
3. Remove the incorrect `inversedBy: 'referrals'` from `ReferralRegistration::$referrer`. Keep its owning foreign key and existing accessors. Keep `Referrer::$referrals` as a collection of `Referral` entities.
4. Add a minimal Symfony application fixture and fix any necessary routing, service wiring, or startup incompatibilities revealed by the supported-version checks.
5. Replace placeholder tests with the workflow coverage below, update PHPUnit configuration for the declared PHPUnit version, and provide reproducible test commands.
6. Replace the obsolete Travis configuration with GitHub Actions, matching the repository's existing GitHub hosting. Align README support statements with the verified matrix and document the upgrade path.

## Required verification

- Load metadata for all five entities and check mapping consistency. Explicitly assert association target types; the initial isolated mapping validator did not flag the incorrect inverse target.
- Compare mapping-derived MySQL schema definitions before and after the compatibility changes under the same dependency versions. Require no bundle-induced schema change.
- Create representative rows under ORM 2 and read/update them under ORM 3 against the same disposable MySQL database. Check relationships, identifiers, timestamps, and commission values without applying a migration.
- Execute both registration repository methods and verify filtering, ordering, counts, and returned query usability.
- Exercise referral recording with and without an existing referrer and referring URL so both DQL updates execute. Verify persisted relationships and session/cookie tracking.
- Use an actual HTTP fixture for the query-string referral path: production code uses `filter_input(INPUT_GET)`, which a synthetic CLI `Request` does not populate.
- Persist and reload registrations and purchases, including lifecycle callbacks and program-based purchase counts. Exercise the public percentage commission workflow and eligibility limits.
- Boot the bundle, resolve its public services and repository, load its routes, and smoke-test the authenticated report path with the paginator. Supply the host registration event in test fixtures rather than inventing a new production event contract.
- Run SQLite checks for quick feedback and MySQL integration checks for both ORM majors. Fail CI on required test failures rather than masking compatibility failures with skips.

## CI coverage

Use explicit Composer constraints in isolated jobs and verify dependency resolution before calling a lane supported. Cover the corrected ORM lower bounds as well as current compatible releases, every advertised Symfony line, DoctrineBundle 2 and 3 where compatible, and DBAL 3 and 4 where compatible. Include DBAL 2 if the retained ORM 2/DoctrineBundle constraints resolve to it in the lower-bound lane.

The original candidate lanes were refined during runtime verification; ORM 2.11 replaces the nonfunctional ORM 2.10 lower pairing with explicit user approval. "Compatible" means the newest release satisfying the other constraints in that job. These are targeted compatibility checks, not a full Cartesian product of dependency versions.

| PHP | Symfony | ORM | DBAL | DoctrineBundle | KnpPaginator |
| --- | --- | --- | --- | --- | --- |
| 8.1 | 5.4 | 2.11.0 | 2 | 2.7.2 | 5 |
| 8.1 | 6.4 | Compatible 2.x | 3 | Compatible 2.x | 6 |
| 8.1 | 5.4 | 3.0.0 | 3 | 2.13.x | 5 |
| 8.1 | 6.4 | Compatible 3.x | 3 | Compatible 2.x | 6 |
| 8.2 | 7.x | Compatible 2.x | 3 | Compatible 2.x | 6 |
| 8.2 | 7.x | Compatible 3.x | 4 | Compatible 2.x | 6 |
| 8.4 | 8.x | Compatible 3.x | 4 | Compatible 3.x | 6 |

Run mapping, SQLite workflow, kernel startup, and MySQL integration checks in every lane. Run the ORM 2-to-3 existing-data check on a shared PHP/Symfony/DBAL baseline to distinguish the ORM upgrade from unrelated dependency changes. Composer resolution failures must be explained and the lane corrected; do not silently drop retained compatibility promises.

The corrected lower-bound job uses [DoctrineBundle 2.7.2 with ORM 2.11](https://raw.githubusercontent.com/doctrine/DoctrineBundle/2.7.2/composer.json); [2.13.3 excludes ORM below 2.17](https://raw.githubusercontent.com/doctrine/DoctrineBundle/2.13.3/composer.json) and serves the lower ORM 3 profile instead. [ORM 3.0.0](https://raw.githubusercontent.com/doctrine/orm/3.0.0/composer.json) supports DBAL 3/4. [DoctrineBundle 3.3.2](https://raw.githubusercontent.com/doctrine/DoctrineBundle/3.3.2/composer.json) permits Symfony 8 and requires PHP 8.4, DBAL 4, and ORM 3; [Symfony 8 also requires PHP 8.4](https://raw.githubusercontent.com/symfony/framework-bundle/v8.0.0/composer.json). [KnpPaginator 5.9](https://raw.githubusercontent.com/KnpLabs/KnpPaginatorBundle/v5.9.0/composer.json) covers Symfony 5.4, while [6.10](https://raw.githubusercontent.com/KnpLabs/KnpPaginatorBundle/v6.10.0/composer.json) covers Symfony 6.4/7/8.

## Deferred business defects

Configuration and `Purchase::TYPE_FIXED_AMOUNT` use `fixed-amount`, while `Affiliate` checks `fixed-price` in its calculation methods. The repeat fixed commission branch also reads `first_commission_amount`. Track these separately; ORM readiness does not establish that fixed commission calculation is correct. A preexisting response-recorder behavior can also restore referral session tracking from an incoming cookie after registration clears it at the public registration boundary; this request-end behavior is documented in the README and remains deferred.

## Completion criteria

The concrete dependency lanes resolve and all required checks pass. Existing data remains usable without a bundle schema migration, the public API is preserved, both ORM majors are covered in CI, and the README states the verified compatibility and remaining business limitations. Report any verification that could not run; unexecuted checks do not count as passing.
