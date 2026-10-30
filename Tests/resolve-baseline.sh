#!/usr/bin/env bash
set -euo pipefail
# Both baseline jobs intentionally share Symfony 6.4 and DBAL 3.
composer update --no-interaction --prefer-dist --no-progress \
  --with "doctrine/orm:${ORM_CONSTRAINT:-^3.0}" \
  --with 'doctrine/dbal:^3.6' --with 'doctrine/doctrine-bundle:^2.13' \
  --with 'symfony/framework-bundle:^6.4' --with 'symfony/security-bundle:^6.4' \
  --with 'symfony/twig-bundle:^6.4' --with 'symfony/phpunit-bridge:^6.4' \
  --with 'symfony/browser-kit:^6.4' --with 'symfony/css-selector:^6.4' \
  --with 'symfony/yaml:^6.4' --with 'symfony/var-exporter:^6.4'
