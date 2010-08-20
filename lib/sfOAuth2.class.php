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

    $token = new Token();
    $token->setTokenKey($params['access_token']);
    $token->setStatus(Token::STATUS_ACCESS);
    $token->setName($this->getName());
    $token->setOauthVersion($this->getVersion());


    unset($params['access_token']);

    if(count($params) > 0)
    {
      $token->setParams($params);
    }

    $this->setExpire($token);

    $this->setToken($token);

    //get identifier maybe need the access token
    $token->setIdentifier($this->getIdentifier());
    $this->setToken($token);

    return $token;
  }

  public function connect($user)
  {
    $this->requestAuth($this->getController());
  }

  /**
   * TODO a refaire method etc...
   * @param unknown_type $action
   * @param unknown_type $params
   * @param unknown_type $method
   *
   * Enter description here ...
   *
   * @author Maxime Picaud
   * @since 19 août 2010
   */
  public function get($action,$url_params = null, $params = array(), $method = 'GET')
  {
    $base_url = $this->getNamespace($this->getCurrentNamespace());

    $params = array_merge($params, $this->getDefaultParamaters());
    $url = $base_url.'/'.$action;

    if(is_string($url_params))
    {
      $url .= '/'.$url_params;
    }
    elseif(is_array($url_params))
    {
      foreach($url_params as $key => $param)
      {
        $url = preg_replace('/\/'.$key.'(\/|$)/', '/'.$param.'$1', $url);
      }
    }

    $url .= '?access_token='.$this->getToken()->getTokenKey();

    $params = http_build_query($params);
    if(strlen($params) > 0)
    {
      $url .= '&'.$params;
    }

    return json_decode($this->call($url, null, 'GET'));
  }
}
