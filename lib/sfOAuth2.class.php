<?php
/**
 *
 *
 *
 * Implementation for OAuth version 2
 *
 * @author Maxime Picaud
 * @since 21 août 2010
 */
class sfOAuth2 extends sfOAuth
{
  /**
   * Constructor - set version to 2
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function __construct($key, $secret, $token = null, $config = array())
  {
    $this->version = 2;

    parent::__construct($key, $secret, $token, $config);
  }

  /**
   * (non-PHPdoc)
   * @see plugins/sfDoctrineOAuthPlugin/lib/sfOAuth::initialize()
   */
  protected function initialize($config) {}

  /**
   * (non-PHPdoc)
   * @see plugins/sfDoctrineOAuthPlugin/lib/sfOAuth::requestAuth()
   */
  public function requestAuth($parameters = array())
  {
    if($this->getController())
    {
      $this->setAuthParameter('client_id', $this->getKey());
      $this->setAuthParameter('redirect_uri', $this->getCallback());
      $this->addAuthParameters($parameters);

      $this->getController()->redirect($this->getRequestAuthUrl().'?'.http_build_query($this->getAuthParameters()));
    }
  }

  /**
   * (non-PHPdoc)
   * @see plugins/sfDoctrineOAuthPlugin/lib/sfOAuth::getAccessToken()
   */
  public function getAccessToken($verifier, $parameters = array())
  {
    $url = $this->getAccessTokenUrl();

    $this->setAccessParameter('client_id', $this->getKey());
    $this->setAccessParameter('client_secret', $this->getSecret());
    $this->setAccessParameter('redirect_uri', $this->getCallback());
    $this->setAccessParameter('code', $verifier);

    $this->addAccessParameters($parameters);

    $params = $this->call($url, $this->getAccessParameters(), 'GET');

    $params = OAuthUtil::parse_parameters($params);

    $access_token = isset($params['access_token'])?$params['access_token']:null;

    if(is_null($access_token))
    {
      $error = sprintf('{OAuth} access token failed - %s returns %s', $this->getName(), print_r($params, true));
      sfContext::getInstance()->getLogger()->err($error);
    }

    $token = new Token();
    $token->setTokenKey($access_token);
    $token->setName($this->getName());
    $token->setStatus(Token::STATUS_ACCESS);
    $token->setOAuthVersion($this->getVersion());

    unset($params['access_token']);

    if(count($params) > 0)
    {
      $token->setParams($params);
    }

    $this->setExpire($token);

    $this->setToken($token);

    // get identifier maybe need the access token
    $token->setIdentifier($this->getIdentifier());

    $this->setToken($token);

    return $token;
  }

  /**
   * (non-PHPdoc)
   * @see plugins/sfDoctrineOAuthPlugin/lib/sfOAuth::connect()
   */
  public function connect($user, $parameters = array())
  {
    $this->requestAuth($parameters);
  }

  /**
   * overriden to support OAuth 2
   *
   * @author Maxime Picaud
   * @since 19 août 2010
   */
  public function get($action, $aliases = null, $params = array(), $method = 'GET')
  {
    if(is_null($this->getToken()))
    {
      throw new sfException(sprintf('no access token available for "%s"', $this->getName()));
    }
    $this->setCallParameter('access_token', $this->getToken()->getTokenKey());
    $this->addCallParameters($params);

    $url = parent::get($action, $aliases, $this->getCallParameters(), $method);

    $response = $this->call($url, $this->getCallParameters(), $method);

    if($this->getOutputFormat() == 'json')
    {
      $response = json_decode($response);
    }

    return $response;
  }
}
