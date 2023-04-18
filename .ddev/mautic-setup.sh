#!/bin/bash

setup_mautic() {
    cp ./.ddev/local.config.php.dist ./tests/local.config.php

    printf "Cloning the \"features\" branch from mautic/mautic...\n"
    git clone -b features --single-branch --depth 1 https://github.com/mautic/mautic.git mautic
    cd mautic

    composer install --prefer-dist --no-progress
    cp ../.ddev/mautic-local.php.dist ./app/config/local.php

    printf "Installing Mautic...\n"
    php bin/console mautic:install --force http://localhost/mautic
    php bin/console cache:warmup --no-interaction --env=dev

    printf "Enabling Twilio plugin for tests...\n"
    mysql -udb -pdb -P3306 -hdb -e "USE db; INSERT INTO plugin_integration_settings (id, plugin_id, name, is_published, supported_features, api_keys, feature_settings) VALUES (2, NULL, 'Twilio', 1, 'a:0:{}', 'a:2:{s:8:\"username\";s:169:\"bzFmNlIydWRSZXlIN2lQVkdpanJ4aTQ2NUh6RVdDbHlLRVhsWGZ4b0kyZVNxLzYrQ1J6V1RvMnlhVEp0c245TEp6eStQekx5ZVhLWjB1YVdoR3RnR2dHQ3k1emVVdGt5NzZKUmtjUnJ3c1E9|L8tbZRIYhwatT7Mq+HAdYA==\";s:8:\"password\";s:169:\"T2d2cFpXQWE5YVZnNFFianJSYURRYUtGRHBNZGZjM1VETXg2Wm5Va3NheW43MjVWUlJhTVlCL2pYMDBpbElONStiVVBNbEM3M3BaeGJMNkFKNUFEN1pTNldSRjc4bUM4SDh1SE9OY1k5MTg9|TeuSvfx4XSUOvp0O7T49Cg==\";}', 'a:4:{s:20:\"sending_phone_number\";N;s:22:\"disable_trackable_urls\";i:0;s:16:\"frequency_number\";N;s:14:\"frequency_time\";N;}');"
    php bin/console mautic:plugins:reload

    tput setaf 2
    printf "All done! Run \"ddev exec composer test\" to run PHPUnit tests.\n"
    printf "If you want to open the Mautic instance, go to https://api-library.ddev.site/mautic in your browser.\n"
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
    echo "Updating the cloned Mautic instance..."
    cd mautic
    git pull
    # Need to downgrade to Composer v1 until Mautic 4 is out
    composer self-update --1
    composer install --prefer-dist --no-progress
    tput setaf 2
    printf "Run \"ddev exec composer test\" to run PHPUnit tests.\n"
    printf "If you want to open the Mautic instance, go to https://api-library.ddev.site/mautic in your browser.\n"
    tput sgr0
fi
