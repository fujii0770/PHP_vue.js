<?php
/**
 * This file is part of php-saml.
 *
 * (c) OneLogin Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package OneLogin
 * @author  OneLogin Inc <saml-info@onelogin.com>
 * @license MIT https://github.com/onelogin/php-saml/blob/master/LICENSE
 * @link    https://github.com/onelogin/php-saml
 */

namespace App\Saml;
use Aacotroneo\Saml2\Saml2Auth;
use Illuminate\Support\Facades\URL;
use OneLogin\Saml2\Auth;

/**
 * Configuration of the OneLogin PHP Toolkit
 */
class AuthUtils
{
    /**
     * Load the IDP config file and construct a OneLogin\Saml2\Auth (aliased here as OneLogin_Saml2_Auth).
     * Pass the returned value to the Saml2Auth constructor.
     *
     * @param string    $idpName        The target IDP name, must correspond to config file 'config/saml2/${idpName}_idp_settings.php'
     * @return OneLogin_Saml2_Auth Contructed OneLogin Saml2 configuration of the requested IDP
     * @throws \InvalidArgumentException if $idpName is empty
     * @throws \Exception if key or certificate is configured to a file path and the file is not found.
     */
    public static function loadOneLoginAuthFromIpdConfig($idpName, $entityId = null, $ssoUrl = null, $sloUrl = null, $cert = null, $spEntityId = null)
    {
        if (empty($idpName)) {
            throw new \InvalidArgumentException("IDP name required.");
        }

        $config = config('saml2.'.$idpName.'_idp_settings');

        if (empty($config['sp']['entityId'])) {
            $config['sp']['entityId'] = URL::route('saml2_metadata', $idpName);
        }
        if (empty($config['sp']['assertionConsumerService']['url'])) {
            $config['sp']['assertionConsumerService']['url'] = URL::route('saml2_acs', $idpName);
        }
        if (!empty($config['sp']['singleLogoutService']) &&
            empty($config['sp']['singleLogoutService']['url'])) {
            $config['sp']['singleLogoutService']['url'] = URL::route('saml2_sls', $idpName);
        }
        if (strpos($config['sp']['privateKey'], 'file://')===0) {
            $config['sp']['privateKey'] = static::extractPkeyFromFile($config['sp']['privateKey']);
        }
        if (strpos($config['sp']['x509cert'], 'file://')===0) {
            $config['sp']['x509cert'] = static::extractCertFromFile($config['sp']['x509cert']);
        }
        if (strpos($config['idp']['x509cert'], 'file://')===0) {
            $config['idp']['x509cert'] = static::extractCertFromFile($config['idp']['x509cert']);
        }
        if ($entityId) {
            $config['idp']['entityId'] = $entityId;
        }
        if ($ssoUrl) {
            $config['idp']['singleSignOnService']['url'] = $ssoUrl;
        }
        if ($sloUrl) {
            $config['idp']['singleLogoutService']['url'] = $sloUrl;
        }
        if ($cert) {
            $config['idp']['x509cert'] = $cert;
        }
        if ($spEntityId) {
            $config['sp']['entityId'] = $spEntityId;
        }

        return new Saml2Auth(new Auth($config));
    }
}
