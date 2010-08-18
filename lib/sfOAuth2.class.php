<?php
class sfOAuth2 extends sfOAuth
{
  public function __construct($key, $secret, OAuthToken $token = null, $config = array())
  {
    $this->version = 2;

    parent::__construct($key, $secret, $token, $config);
  }

  protected function initialize($config) {}

  public function requestAuth()
  {
    if($this->getController())
    {
      $this->getController()->redirect($this->getRequestAuthUrl().sprintf('?client_id=%s&redirect_uri=%s', $this->getKey(), $this->getCallback()));
    }
  }

  public function getAccessToken($verifier)
  {
    $url = $this->getAccessTokenUrl().sprintf('?client_id=%s&redirect_uri=%s&client_secret=%s&code=%s', $this->getKey(), $this->getCallback(), $this->getSecret(), $verifier);

    $params = $this->call($url, null, 'GET');
    $params = OAuthUtil::parse_parameters($params);
    $this->setToken(new OAuthToken($params['access_token'], null));

    return $params;
  }

  public function connect($user)
  {
    $user->setAttribute('provider', $this->getName());
    $this->requestAuth($this->getController());
  }


}
