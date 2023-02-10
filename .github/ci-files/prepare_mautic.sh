#!/bin/bash

set -e

cd /var/www/html

cat << 'EOF' > app/config/parameters_local.php
<?php
/**
 * Parameter overrides for GitHub Actions.
 */
$parameters = [
    'api_enabled'           => true,
    'api_enable_basic_auth' => true,
];
EOF

#cat << 'EOF' > app/config/security_local.php
#<?php
#
#$this->import('security.php');
#
#// Support HTTP basic auth for test logins
#$container->loadFromExtension('security',
#    [
#        'firewalls' => [
#            'main' => [
#                // Support HTTP basic auth for test logins
#                'http_basic' => true,
#            ],
#        ],
#        'encoders'  => [
#          'Symfony\Component\Security\Core\User\User' => [
#            'algorithm'        => 'md5',
#            'encode_as_base64' => false,
#            'iterations'       => 0,
#          ],
#          'Mautic\UserBundle\Entity\User' => [
#            'algorithm'        => 'md5',
#            'encode_as_base64' => false,
#            'iterations'       => 0,
#          ],
#        ],
#    ]
#);

php bin/console mautic:install http://localhost/ --force --mailer_from_name="GitHub Actions" --mailer_from_email="github-actions@mautic.org" --mailer_transport="smtp" --mailer_host="mailhog" --mailer_port="1025" --admin_username=admin --admin_password=mautic --admin_email="bla@bla.be"
php bin/console cache:clear --no-interaction

mysql -uroot -proot -hmysql -e "USE mautictest; INSERT INTO plugin_integration_settings (plugin_id, name, is_published, supported_features, api_keys, feature_settings) VALUES (NULL, 'Twilio', 1, 'a:0:{}', 'a:2:{s:8:\"username\";s:169:\"bzFmNlIydWRSZXlIN2lQVkdpanJ4aTQ2NUh6RVdDbHlLRVhsWGZ4b0kyZVNxLzYrQ1J6V1RvMnlhVEp0c245TEp6eStQekx5ZVhLWjB1YVdoR3RnR2dHQ3k1emVVdGt5NzZKUmtjUnJ3c1E9|L8tbZRIYhwatT7Mq+HAdYA==\";s:8:\"password\";s:169:\"T2d2cFpXQWE5YVZnNFFianJSYURRYUtGRHBNZGZjM1VETXg2Wm5Va3NheW43MjVWUlJhTVlCL2pYMDBpbElONStiVVBNbEM3M3BaeGJMNkFKNUFEN1pTNldSRjc4bUM4SDh1SE9OY1k5MTg9|TeuSvfx4XSUOvp0O7T49Cg==\";}', 'a:4:{s:20:\"sending_phone_number\";N;s:22:\"disable_trackable_urls\";i:0;s:16:\"frequency_number\";N;s:14:\"frequency_time\";N;}');"
php bin/console mautic:plugins:reload

