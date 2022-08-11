### Instalation in 3 steps
Get source code from Git Repository
```bash
cd /path/to/pac_user
git init
git config core.sparsecheckout true
echo pac_user >> .git/info/sparse-checkout
git remote add -f origin https://git-codecommit.ap-northeast-1.amazonaws.com/v1/repos/pac
composer update
# Run `npm update` on Development
cp .env.example .env
php artisan key:generate
```
- You have to setup API_HOST, API_BASE paste this to your .env file

```bash
API_HOST="http://localhost/app-api"
API_BASE="/api/v1"
```
- You have to setup APP_NAME, paste this to your .env file

```bash
 APP_NAME=Laravel_user
```

##### That's all. Enjoy.

### Change log
##### v 1.0.0

## Screenshots