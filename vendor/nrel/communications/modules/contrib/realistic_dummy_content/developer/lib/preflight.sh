#!/bin/bash
#
# Preflight checks.
#
set -e

echo -e '[preflight] checking whether Docker and Docker Compose are installed.'
docker -v
docker-compose -v
