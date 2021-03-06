<?php
/**
 * PHPMailer - PHP email creation and transport class.
 * PHP Version 5.4
 * @package PHPMailer
 * @link https://github.com/PHPMailer/PHPMailer/ The PHPMailer GitHub project
 * @author Marcus Bointon (Synchro/coolbru) <phpmailer@synchromedia.co.uk>
 * @author Jim Jagielski (jimjag) <jimjag@gmail.com>
 * @author Andy Prevost (codeworxtech) <codeworxtech@users.sourceforge.net>
 * @author Brent R. Matzelle (original founder)
 * @copyright 2012 - 2015 Marcus Bointon
 * @copyright 2010 - 2012 Jim Jagielski
 * @copyright 2004 - 2009 Andy Prevost
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace PHPMailer\PHPMailer\OAuthProvider;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

/**
 * Wrapper for League Google OAuth2 provider.
 * @package PHPMailer
 * @author @sherryl4george
 * @author Marcus Bointon (@Synchro) <phpmailer@synchromedia.co.uk>
 * @link https://github.com/thephpleague/oauth2-client
 */

class Google extends Base
{
	use BearerAuthorizationTrait;

    const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'id';
		
    public function getProvider()
    {
        if (is_null($this->provider)) {
            $this->provider = new Google([
                'clientId' => $this->oauthClientId,
                'clientSecret' => $this->oauthClientSecret
            ]);
        }
        return $this->provider;
    }
	
	/**
     * @param array $options
     * @return string
	 * All Options that are to be passed to the Google Server can be set here
     */
    public function getOptions()
    {
        return [
            'scope' => ['https://mail.google.com/'],
            'approval_prompt' => 'force',
			'hd'          => '',
            'access_type' => 'offline',            
            // if the user is logged in with more than one account ask which one to use for the login!
            'authuser'    => '-1'
        ];
    }
	
	public function getBaseAuthorizationUrl()
    {		
        return 'https://accounts.google.com/o/oauth2/auth';
    }
	
	/**
     * @param array $options
     * @return string
     */

    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://accounts.google.com/o/oauth2/token';
    }

    protected function getAuthorizationParameters(array $options)
    {
		$tmp_options = $this->getOptions();
		if (is_array($tmp_options['scope'])) {		
            $separator = $this->getScopeSeparator();
            $tmp_options['scope'] = implode($separator, $tmp_options['scope']);
        }
		
        $params = array_merge(
            parent::getAuthorizationParameters($options),
            array_filter($tmp_options)
        );      
        return $params;
    }

    protected function getDefaultScopes()
    {
       return [
           'https://mail.google.com/'           
        ];		
    }

    protected function getScopeSeparator()
    {
        return ' ';
    } 
		
	private function checkResponseUtility(ResponseInterface $response, $data)
    {   var_dump($response);    
        if (!empty($data['error'])) {
            $code  = 0;
            $error = $data['error'];
            
            if (is_array($error)) {
                $code  = $error['code'];
                $error = $error['message'];
            }
            
            throw new IdentityProviderException($error, $code, $data);
        }
    }
}
