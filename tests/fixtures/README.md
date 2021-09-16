## All Sample files

# Short sample

Can be imported via:

    php local/cveteval/cli/setup_dev.php -p -s=short

# Bigger Sample

    php local/cveteval/cli/setup_dev.php -p

# Workign with CAS

See    https://github.com/moodlehq/moodle-docker-cas

    docker -vvv run --rm --name cas -p 8443:8443 -dt -v $(pwd)/local/cveteval/tests/fixtures/ShortSampleCASusers.txt:/cas-overlay/etc/cas/users/list  moodlehq/moodle-docker-cas:v5.2.2-0