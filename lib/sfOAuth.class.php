<?php
abstract class sfOAuth
{
  protected $version;

  protected $key;
  protected $secret;

  protected $request;
  protected $token;

  protected $request_token_url;
  protected $request_auth_url;
  protected $access_token_url;

  protected $controller;

  protected $name;

  protected $parameters = array();

  public function __construct($key, $secret, Token $token = null, $config = array())
  {
    $this->key = $key;
    $this->secret = $secret;
    $this->token = $token;

    if(isset($config['callback']))
    {
      $this->setCallback($config['callback']);
    }

    $this->initialize($config);
  }

  public static function getInstance($config)
  {
    $version = isset($config['version'])?$config['version']:null;
    $provider = isset($config['provider'])?$config['provider']:null;
    $key = isset($config['key'])?$config['key']:null;
    $secret = isset($config['secret'])?$config['secret']:null;
    $token = isset($config['token'])?$config['token']:null;
  }

  abstract protected function initialize($config);
  abstract public function requestAuth();
  abstract public function getAccessToken($verifier);
  abstract public function connect($user);

  public function getVersion()
  {
    return $this->version;
  }

  public function getKey()
  {
    return $this->key;
  }

  public function setKey($key)
  {
    $this->key = $key;
  }

  public function getSecret()
  {
    return $this->secret;
  }

  public function setSecret($secret)
  {
    $this->secret = $secret;
  }

  /**
   * @return OAuthToken
   *
   *
   * Enter description here ...
   *
   * @author Maxime Picaud
   * @since 12 août 2010
   */
  public function getToken()
  {
    return $this->token;
  }

  public function setToken(OAuthToken $token)
  {
    $this->token = $token;
  }

  public function getRequestTokenUrl()
  {
    return $this->request_token_url;
  }

  public function setRequestTokenUrl($request_token_url)
  {
    $this->request_token_url = $request_token_url;
  }

  public function getRequestAuthUrl()
  {
    return $this->request_auth_url;
  }

  public function setRequestAuthUrl($request_auth_url)
  {
    $this->request_auth_url = $request_auth_url;
  }

  public function getAccessTokenUrl()
  {
    return $this->access_token_url;
  }

  public function setAccessTokenUrl($access_token_url)
  {
    $this->access_token_url = $access_token_url;
  }

  public function getController()
  {
    if(is_null($this->controller))
    {
      $this->controller = sfContext::getInstance()->getController();
    }

    return $this->controller;
  }

  public function setController(sfWebController $controller)
  {
    $this->controller = $controller;
  }

  public function getCallback()
  {
    return $this->callback;
  }

  public function setCallback($callback)
  {
    if(strpos($callback, '@') !== false)
    {
      $callback = $this->getController()->genUrl($callback, true);
    }

    $this->callback = $callback;
    $this->setParameter('oauth_callback', $callback);
  }

  public function getName()
  {
    return $this->name;
  }

  public function setName($name)
  {
    $this->name = $name;
  }

  public function setParameters($parameters)
  {
    $this->parameters = $parameters;
  }

  public function setParameter($key, $value)
  {
    $this->parameters[$key] = $value;
  }

  public function getParameters()
  {
    return $this->parameters;
  }

  public function getParameter($key, $default = null)
  {
    return isset($this->parameters[$key])?$this->parameters[$key]:$default;
  }

  public function addParameters($parameters)
  {
    $this->parameters = array_merge($this->parameters, $parameters);
  }

  protected function call($url, $request, $method = 'POST')
  {
    $ci = curl_init();

    curl_setopt($ci, CURLOPT_HEADER, false);
    if($method == 'POST')
    {
      curl_setopt($ci, CURLOPT_POST, true);
      curl_setopt($ci, CURLOPT_POSTFIELDS, $request->to_postdata());
    }
    curl_setopt($ci, CURLOPT_URL, $url);
    curl_setopt($ci,CURLOPT_RETURNTRANSFER,true);
    $response = curl_exec($ci);
    curl_close ($ci);

    return $response;
  }
}
