#!/usr/bin/env bash
set -euo pipefail
profile="${1:?Choose a compatibility profile (see README)}"
case "$profile" in
  lower-orm2) symfony='5.4.*'; orm='2.11.0'; dbal='^2.13'; bundle='2.7.2'; paginator='^5' ;;
  sf64-orm2) symfony='6.4.*'; orm='^2.20'; dbal='^3'; bundle='^2'; paginator='^6' ;;
  lower-orm3) symfony='5.4.*'; orm='3.0.0'; dbal='^3'; bundle='2.13.*'; paginator='^5' ;;
  sf64-orm3) symfony='6.4.*'; orm='^3'; dbal='^3'; bundle='^2'; paginator='^6' ;;
  sf7-orm2) symfony='^7'; orm='^2.20'; dbal='^3'; bundle='^2'; paginator='^6' ;;
  sf7-orm3) symfony='^7'; orm='^3'; dbal='^4'; bundle='^2'; paginator='^6' ;;
  sf8-orm3) symfony='^8'; orm='^3'; dbal='^4'; bundle='^3'; paginator='^6' ;;
  *) echo "Unknown compatibility profile: $profile" >&2; exit 2 ;;
esac
constraints=(
  --with "doctrine/orm:$orm" --with "doctrine/dbal:$dbal"
  --with "doctrine/doctrine-bundle:$bundle" --with "knplabs/knp-paginator-bundle:$paginator"
)
# Pin framework APIs, not independently versioned contracts or polyfills.
for component in framework-bundle security-bundle twig-bundle phpunit-bridge browser-kit css-selector yaml routing http-kernel http-foundation config dependency-injection security-core security-http security-csrf event-dispatcher; do
  constraints+=(--with "symfony/$component:$symfony")
done
# ORM 3.0 requires var-exporter 6 even when the framework itself is 5.4.
case "$symfony" in
  '5.4.*'|'6.4.*') constraints+=(--with 'symfony/var-exporter:^6.4') ;;
esac
composer update --no-interaction --prefer-dist --no-progress "${constraints[@]}"
composer show
