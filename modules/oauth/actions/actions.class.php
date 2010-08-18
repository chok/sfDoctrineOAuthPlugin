<?php
class oauthActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
    $oauth = $this->getOAuth();
    var_dump(sfConfig::get('app_oauth'));die();
    $oauth->setRequestAuthUrl(sfConfig::get('app_oauth_request_auth_url'));
    $oauth->setCallback(sfConfig::get('app_oauth_callback'));

    $oauth->connect($this->getUser());
  }

  public function executeCallback(sfWebRequest $request)
  {
    //stored in connect
    $token = $this->getUser()->getAttribute('token');

    $oauth = $this->getOAuth();
    $oauth->setToken($token);
    $oauth->setAccessTokenUrl(sfConfig::get('app_oauth_access_token_url'));

    if($oauth->getVersion() == 1)
    {
      $code = $request->getParameter('oauth_verifier');
    }
    else
    {
      $code = $request->getParameter('code');
    }

    $this->result = $oauth->getAccessToken($code);
    $this->oauth = $oauth;

    $this->saveToken($oauth->getToken(), $this->getUser());
  }

  protected function getOAuth()
  {
    $key = sfConfig::get('app_oauth_key');
    $secret = sfConfig::get('app_oauth_secret');
    $version = sfConfig::get('app_oauth_version');

    if(intval($version) == 2)
    {
      $oauth = new sfOAuth2($key, $secret);
    }
    else
    {
      $oauth = new sfOAuth1($key, $secret);
      $oauth->setRequestTokenUrl(sfConfig::get('app_oauth_request_token_url'));
    }

    return $oauth;
  }

  protected function saveToken(OAuthToken $token, $user)
  {
    if($user->isAuthenticated())
    {
      $db_token = new Token();
      $db_token->setTokenKey($token->key);
      $db_token->setTokenSecret($token->secret);
      $db_token->setUser($user->getGuardUser());
      $db_token->setName('test_token');

      $db_token->save();
    }
    else
    {
      $user->setAttribute('test_token', $token);
    }
  }

  public function executeApi(sfWebRequest $request)
  {
    if($user->hasAttribute('test_token'))
    {
      $access_token = $this->getUser()->getAttribute('test_token');
    }
    else
    {
      $access_token = Doctrine::getTable('Token')->findOneByNameAndUser('test_token', $this->getUser()->getGuardUser());
    }

    if($access_token)
    {
      $oauth = $this->getOAuth();
      $oauth->setToken($access_token);

      $this->result = $oauth->api(sfConfig::get('app_oauth_api'), sfConfig::get('app_oauth_params') );

    }
  }
}
