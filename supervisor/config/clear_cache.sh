echo 'Clear PAC Admin caching'
php pac_admin/artisan config:clear
php pac_admin/artisan cache:clear
php pac_admin/artisan view:clear

echo 'Clear PAC User caching'
php pac_user/artisan config:clear
php pac_user/artisan cache:clear
php pac_user/artisan view:clear

echo 'Clear PAC User Api caching'
php pac_user_api/artisan config:clear
php pac_user_api/artisan cache:clear
php pac_user_api/artisan view:clear

echo 'Clear PAC Log caching'
php pac_api_log/artisan config:clear
php pac_api_log/artisan cache:clear
php pac_api_log/artisan view:clear

echo 'Change Owner'
sudo chown -R apache:apache /var/www/pac