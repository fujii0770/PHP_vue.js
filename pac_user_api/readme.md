### Instalation in 4 steps
Get source code from Git Repository
```bash
cd /path/to/pac_user_api
git init
git config core.sparsecheckout true
echo pac_user_api >> .git/info/sparse-checkout
git remote add -f origin https://git-codecommit.ap-northeast-1.amazonaws.com/v1/repos/pac
composer update
cp .env.example .env
php artisan key:generate
```
- You have to setup database connection, paste this to your .env file

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pac
DB_USERNAME=root
DB_PASSWORD=
```
- Run command

```bash
php artisan migrate
php artisan passport:install
```
- You have to setup APP_NAME, paste this to your .env file

```bash
 APP_NAME=Laravel_user_api
 MAIL_PREFIX_SUBJECT=[パソコン決裁Cloud]
```

- Setup crontab
```bash
crontab -u apache -e
* * * * * cd /var/www/pac/pac_user_api && php artisan schedule:run >> /dev/null 2>&1
```

##### That's all. Enjoy.

### Change log
##### v 1.0.2

## Screenshots