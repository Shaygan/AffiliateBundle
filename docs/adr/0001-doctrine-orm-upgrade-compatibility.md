# Prepare for Doctrine ORM 3 while retaining ORM 2 compatibility

Applications must be able to update this bundle before upgrading Doctrine. Retain support for both ORM 2 and ORM 3 and preserve the Symfony versions currently declared in Composer; verify valid dependency combinations rather than assuming every version can be combined with every other version.

Preserve the public API and database schema. Include ORM compatibility fixes, the inconsistent referral registration association, and minimal compatibility fixes required for the advertised Symfony versions to run. Defer the existing fixed commission calculation defects to separate work.

Readiness requires mapping checks, Symfony kernel startup tests, and database integration tests covering referral recording, registration queries, and commission persistence, with CI covering both retained ORM majors. Use MySQL integration tests to exercise the existing platform-specific mappings and SQLite for fast checks. MariaDB coverage is outside this preparation work. Placeholder tests and permissive Composer constraints do not establish compatibility.

The implementation and verification plan is recorded in [the ORM 3 upgrade plan](../orm3-upgrade-plan.md). Implementation follows confirmation of the shared understanding reached during the design interview.
