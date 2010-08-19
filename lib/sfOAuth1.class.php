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

    $this->setToken($token);

    return $params;
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

    //override request_token
    $this->setToken($token);

    //return $params for extra params - bof bof ca s'appelle getAccessToken donc -> Token
    return $params;
  }

  public function connect($user)
  {
    $this->getRequestToken();

    //store token in the user session
    $token = $this->getToken();
    $token->setUser($user);

    $token->save();

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

    $request = OAuthRequest::from_consumer_and_token($this->getConsumer(), $this->getToken('oauth'), 'GET', $url, $params);
    $request->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $this->getConsumer(), $this->getToken('oauth'));

    $url = $request->to_url();

    //json !!
    return json_decode($this->call($url, null, 'GET'));
  }
}
