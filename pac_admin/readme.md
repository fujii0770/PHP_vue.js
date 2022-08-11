### Instalation in 3 steps
Get source code from Git Repository
```bash
cd /path/to/pac_admin
git init
git config core.sparsecheckout true
echo pac_admin >> .git/info/sparse-checkout
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
DB_DATABASE=pac_id_management
DB_USERNAME=root
DB_PASSWORD=
```
- You have to setup APP_NAME, APP_SLOGAN, URL_APP_USER, URL_LOGO, STAMP_API_BASE_URL ... paste this to your .env file

```bash
 APP_NAME=Laravel_admin
 APP_SLOGAN="管理】<br>パソコン決裁Cloud <br>契約edition"
 URL_APP_USER="http://localhost/app-user"
 URL_LOGO="https://estamp.dstmp.com/app/web/images/default_logo.png"

 STAMP_API_BASE_URL="http://52.69.60.101"
 DEPARTMENT_STAMP_API_URL="http://biz.shachihata.co.jp/preview/preview_p.asp"

 ENABLE_LOG_OPERATION=true
```

##### That's all. Enjoy.

#### Change log
##### v 1.0.2

## Screenshots
