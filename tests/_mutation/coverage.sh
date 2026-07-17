#!/bin/sh
set -eu

export PATH="${PATH}:/proc/self/cwd/vendor/bin"

output_dir=/proc/self/cwd/tests/_output

php -d max_execution_time=0 "$(command -v codecept)" run \
    -c codeception.yml unit \
    --no-redirect \
    --no-colors \
    --fail-fast \
    --coverage-phpunit coverage-xml \
    --coverage-xml coverage.xml \
    --xml junit.xml \
    -o "paths: output: ${output_dir}" \
    -o 'coverage: enabled: true' \
    -o 'coverage: include: [src/*]' \
    --disable-coverage-php
