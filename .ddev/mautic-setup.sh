#!/bin/bash

setup_mautic() {
    cp ./.ddev/local.config.php.dist ./tests/local.config.php

    printf "Cloning the \"features\" branch from mautic/mautic...\n"
    git clone -b features --single-branch --depth 1 https://github.com/mautic/mautic.git mautic
    cp ./.ddev/mautic-local.php.dist ./mautic/app/config/local.php
    cd mautic

    # Need to downgrade to Composer v1 until Mautic 4 is out
    printf "Installing Mautic Composer dependencies...\n"
    composer self-update --1
    composer install --prefer-dist --no-progress

    printf "Installing Mautic...\n"
    php bin/console mautic:install http://localhost/mautic \
        --mailer_from_name="DDEV" --mailer_from_email="mautic@ddev.local" \
        --mailer_transport="smtp" --mailer_host="localhost" --mailer_port="1025"
    php bin/console cache:warmup --no-interaction --env=dev

    tput setaf 2
    printf "All done! Run \"ddev exec composer test\" to run PHPUnit tests.\n"
    tput sgr0
}

# Check if the user has indicated their preference for the Mautic installation
# already (DDEV-managed or self-managed)
if ! test -f ./.ddev/mautic-preference
then
    printf "Installing the API library Composer dependencies...\n"
    composer install
    tput setab 3
    tput setaf 0
    printf "Do you want us to set up a Mautic instance for you to test against?\n"
    printf "If you answer \"no\", you will have to set up a Mautic instance yourself."
    tput sgr0
    printf "\nAnswer [yes/no]: "
    read MAUTIC_PREF

    if [[ $MAUTIC_PREF == "yes" ]]
    then
        printf "Okay, setting up a Mautic instance...\n"
        echo "ddev-managed" > ./.ddev/mautic-preference
        setup_mautic
    else
        printf "Okay, you'll have to set up a Mautic instance yourself. "
        printf "Copy /tests/local.config.php.dist to /tests/local.config.php and add your Mautic instance settings.\n"
        echo "unmanaged" > ./.ddev/mautic-preference
    fi
else
    # Ensure our mautic/mautic clone is up-to-date
    cd mautic
    git pull
fi
