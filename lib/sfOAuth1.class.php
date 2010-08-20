<?php
class sfOAuth1 extends sfOAuth
{
  protected $consumer;

  public function __construct($key, $secret, OAuthToken $token = null, $config = array())
  {
    $this->version = 1;

    parent::__construct($key, $secret, $token, $config);
  }

  protected function initialize($config) {}

  public function getConsumer()
  {
    if(is_null($this->consumer))
    {
      $this->consumer = new OAuthConsumer($this->getKey(), $this->getSecret());
    }

    return $this->consumer;
  }

  public function setConsumer(OAuthConsumer $consumer)
  {
    $this->consumer = $consumer;
  }

  public function getRequestToken()
  {
    $request = OAuthRequest::from_consumer_and_token($this->getConsumer(), $this->getToken('oauth'), 'POST', $this->getRequestTokenUrl(), $this->getParameters());
    $request->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $this->getConsumer(), $this->getToken('oauth'));

    $params = OAuthUtil::parse_parameters($this->call($this->getRequestTokenUrl(), $request));

    $token = new Token();
    $token->setTokenKey($params['oauth_token']);
    $token->setTokenSecret($params['oauth_token_secret']);
    $token->setStatus(Token::STATUS_REQUEST);
    $token->setName($this->getName());
    $token->setOauthVersion($this->getVersion());

    unset($params['oauth_token'], $params['oauth_token_secret']);
    if(count($params) > 0)
    {
      $token->setParams($params);
    }

    $this->setToken($token);

    return $token;
  }

  public function requestAuth()
  {
    if($this->getController())
    {
      $this->getController()->redirect($this->getRequestAuthUrl().'?oauth_token='.$this->getToken()->getTokenKey());
    }
  }

  public function getAccessToken($verifier)
  {
    $this->setParameter('oauth_verifier', $verifier);

    $request = OAuthRequest::from_consumer_and_token($this->getConsumer(), $this->getToken('oauth'), 'POST', $this->getAccessTokenUrl(), $this->getParameters());
    $request->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $this->getConsumer(), $this->getToken('oauth'));

    $params = OAuthUtil::parse_parameters($this->call($this->getAccessTokenUrl(), $request));

    $token = new Token();
    $token->setTokenKey($params['oauth_token']);
    $token->setTokenSecret($params['oauth_token_secret']);
    $token->setStatus(Token::STATUS_ACCESS);
    $token->setName($this->getName());
    $token->setOauthVersion($this->getVersion());

    unset($params['oauth_token'], $params['oauth_token_secret']);
    if(count($params) > 0)
    {
      $token->setParams($params);
    }

    $this->setExpire($token);

    //override request_token
    $this->setToken($token);

    $token->setIdentifier($this->getIdentifier());
    $this->setToken($token);

    return $token;
  }

  public function connect($user)
  {
    $token = $this->getRequestToken();

    //store token in the user session
    $user->setAttribute($this->getName().'_request_token', serialize($token));

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

    $params = array_merge($this->getDefaultParamaters(), $params);

    $url = $base_url.'/'.$action;

    if(is_string($url_params))
    {
      $url .= '/'.$url_params;
    }
    elseif(is_array($url_params))
    {
      $url_params = array_merge($this->getDefaultUrlParamaters(), $url_params);
    }

    if(!is_array($url_params))
    {
      $url_params = $this->getDefaultUrlParamaters();
    }

    $url = $this->applyUrlParams($url, $url_params);

    $request = OAuthRequest::from_consumer_and_token($this->getConsumer(), $this->getToken('oauth'), 'GET', $url, $params);
    $request->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $this->getConsumer(), $this->getToken('oauth'));

    $url = $request->to_url();

    //json !!
    return json_decode($this->call($url, null, 'GET'));
  }
}
