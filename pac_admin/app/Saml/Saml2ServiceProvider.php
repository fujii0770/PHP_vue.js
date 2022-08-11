<?php
namespace App\Saml;

use Aacotroneo\Saml2\Saml2Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use OneLogin\Saml2\Utils as OneLogin_Saml2_Utils;

class Saml2ServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        if(config('saml2_settings.useRoutes', false) == true ){
            include base_path('vendor/aacotroneo/laravel-saml2/src/routes.php');
        }

        $this->publishes([
            base_path('vendor/aacotroneo/laravel-saml2/src/config/saml2_settings.php') => config_path('saml2_settings.php'),
            base_path('vendor/aacotroneo/laravel-saml2/src/config/test_idp_settings.php') => config_path('saml2'.DIRECTORY_SEPARATOR.'test_idp_settings.php'),
        ]);

        if (config('saml2_settings.proxyVars', false)) {
            OneLogin_Saml2_Utils::setProxyVars(true);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $encrypter = app(\Illuminate\Contracts\Encryption\Encrypter::class);

        // decrypt
        try{

            $entityId = null; $ssoUrl = null; $logoutUrl = null; $cert = null; $spEntityId = null;
            if (Cookie::has('sso_company_1')){
                $cookie = $encrypter->decrypt(Cookie::get('sso_company_1'), false);
                $cookie = explode('|', $cookie);
                if (count($cookie) > 1){
                    $entityId = $cookie[1];
                }
            }
            if (Cookie::has('sso_company_2')){
                $cookie = $encrypter->decrypt(Cookie::get('sso_company_2'), false);
                $cookie = explode('|', $cookie);
                if (count($cookie) > 1){
                    $ssoUrl = $cookie[1];
                }
            }
            if (Cookie::has('sso_company_3')){
                $cookie = $encrypter->decrypt(Cookie::get('sso_company_3'), false);
                $cookie = explode('|', $cookie);
                if (count($cookie) > 1){
                    $logoutUrl = $cookie[1];
                }
            }
            if (Cookie::has('sso_company_4')){
                $cookie = $encrypter->decrypt(Cookie::get('sso_company_4'), false);
                $cookie = explode('|', $cookie);
                if (count($cookie) > 1){
                    $cert = $cookie[1];
                }
            }
            if (Cookie::has('sso_company_5')){
                $cookie = $encrypter->decrypt(Cookie::get('sso_company_5'), false);
                $cookie = explode('|', $cookie);
                if (count($cookie) > 1){
                    $spEntityId = $cookie[1];
                }
            }

            $this->app->singleton(Saml2Auth::class, function ($app) use ($entityId, $ssoUrl, $logoutUrl, $cert, $spEntityId){
                $idpName = $app->request->route('idpName');
                if ($entityId && $ssoUrl && $cert && $spEntityId){
                    return AuthUtils::loadOneLoginAuthFromIpdConfig('sso', $entityId, $ssoUrl, $logoutUrl, $cert, $spEntityId);
                }else{
                    $auth = Saml2Auth::loadOneLoginAuthFromIpdConfig($idpName);
                    return new Saml2Auth($auth);
                }
            });

        }catch(\Exception $e){
            Log::warning("Saml2ServiceProvider Error");
            Log::warning($e->getMessage().$e->getTraceAsString());
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [Saml2Auth::class];
    }

}
