#!/bin/sh
set -eu

export PATH="${PATH}:/proc/self/cwd/vendor/bin"

php -d max_execution_time=0 -d memory_limit=512M "$(command -v infection)" \
    --configuration=infection.json5 \
    --coverage=tests/_output \
    --skip-initial-tests \
    --no-interaction \
    --no-progress \
    --show-mutations=0 \
    --logger-github=false
