#!/bin/bash
#
# Run linting on yaml files.
#
set -e

docker run -v "$(pwd)":/code dcycle/yaml-lint .
