# Existing-data ORM upgrade check

This check creates representative affiliate history with ORM 2 and reads/updates it with ORM 3 on the **same disposable MySQL database**, without recreating the schema or applying a migration in the verification stage. It uses the bundle's public entity/repository interfaces through the host application fixture. Fixed commission records are persisted directly; this does not certify the deferred fixed commission calculation workflow.

Use PHP 8.1 with PDO MySQL and a running MySQL 8.4 test database. **The seed command replaces the affiliate tables. Never point it at a production database.** Set `DATABASE_URL` to a dedicated disposable database, then run:

```bash
export DATABASE_URL='mysql://root:test@127.0.0.1:3306/affiliate_upgrade?serverVersion=8.4&charset=utf8mb4'
ORM_CONSTRAINT='^2.20' bash Tests/resolve-baseline.sh
composer test:upgrade:seed
ORM_CONSTRAINT='^3.0' bash Tests/resolve-baseline.sh
composer test:upgrade:verify
```

Both dependency stages retain Symfony 6.4 and DBAL 3. The verifier requires unchanged PHP, Symfony, and DBAL versions, uses a separate host application cache from the seed stage, and requires the seed to use ORM 2 and verification to use ORM 3. The manifest records identifiers, lifecycle timestamps, and meaningful database/mapping schema contracts. It lives in ignored PHPUnit cache storage and survives the dependency switch.

The verifier checks preserved rows and relationships for all five entity types, percentage and fixed purchase values, registration queries and program counts, then updates/reloads an existing referral through ORM 3 and confirms the schema is unchanged. CI executes these commands in one job so the MySQL service and manifest remain available across the switch. A command that was not executed is unverified; a required command failure fails the job.
