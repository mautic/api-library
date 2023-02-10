#!/bin/bash

set -e

cd /var/www/html
php bin/console mautic:install http://localhost/ \
  --force --mailer_from_name="GitHub Actions" --mailer_from_email="github-actions@mautic.org" --mailer_transport="smtp" --mailer_host="mailhog" --mailer_port="1025" --env=dev
php bin/console cache:warmup --no-interaction --env=dev
mysql -uroot -proot -hmysql -e "USE mautictest; INSERT INTO plugin_integration_settings (plugin_id, name, is_published, supported_features, api_keys, feature_settings) VALUES (NULL, 'Twilio', 1, 'a:0:{}', 'a:2:{s:8:\"username\";s:169:\"bzFmNlIydWRSZXlIN2lQVkdpanJ4aTQ2NUh6RVdDbHlLRVhsWGZ4b0kyZVNxLzYrQ1J6V1RvMnlhVEp0c245TEp6eStQekx5ZVhLWjB1YVdoR3RnR2dHQ3k1emVVdGt5NzZKUmtjUnJ3c1E9|L8tbZRIYhwatT7Mq+HAdYA==\";s:8:\"password\";s:169:\"T2d2cFpXQWE5YVZnNFFianJSYURRYUtGRHBNZGZjM1VETXg2Wm5Va3NheW43MjVWUlJhTVlCL2pYMDBpbElONStiVVBNbEM3M3BaeGJMNkFKNUFEN1pTNldSRjc4bUM4SDh1SE9OY1k5MTg9|TeuSvfx4XSUOvp0O7T49Cg==\";}', 'a:4:{s:20:\"sending_phone_number\";N;s:22:\"disable_trackable_urls\";i:0;s:16:\"frequency_number\";N;s:14:\"frequency_time\";N;}');"
php bin/console mautic:plugins:reload --env=dev
