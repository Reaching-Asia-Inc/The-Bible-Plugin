#!/bin/bash

set -e

cd "$(dirname "${BASH_SOURCE[0]}")/../"

vendor/bin/phpcs bible-plugin.php
