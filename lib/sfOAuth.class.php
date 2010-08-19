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

  protected $namespaces;
  protected $current_namespace;

  protected $controller;

  protected $name;

  protected $callback;

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

  public static function findToken($service, $user = null)
  {
    return Doctrine::getTable('Token')->findOneByNameAndUserId($service, $user);
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
   * @return Token
   *
   *
   * Enter description here ...
   *
   * @author Maxime Picaud
   * @since 12 août 2010
   */
  public function getToken($format = 'token')
  {
    if($format == 'oauth')
    {
      if(!is_null($this->token))
      {
        return $this->token->toOAuthToken();
      }
      else
      {
        return null;
      }
    }
    return $this->token;
  }

  public function setToken(Token $token)
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

    $this->getController()->convertUrlStringToParameters($callback);

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

  public function setNamespaces($namespaces)
  {
    $this->namespaces = $namespaces;
  }

  public function setNamespace($key, $value)
  {
    $this->namespaces[$key] = $value;
  }

  public function getNamespaces()
  {
    return $this->namespaces;
  }

  public function getNamespace($key)
  {
    return isset($this->namespaces[$key])?$this->namespaces[$key]:$default;
  }

  public function addNamespaces($namespaces)
  {
    $this->namespaces = array_merge($this->namespaces, $namespaces);
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

  public function ns($namespace)
  {
    if(in_array($ns, array_keys($this->namespaces)))
    {
      $this->current_namespace = $ns;
    }
    else
    {
      throw new sfException(sprintf('Namespace "%s" is not defined for Melody "%s"', $ns, get_class($this)));
    }

    return $this;
  }

  public function getCurrentNamespace()
  {
    if(is_null($this->current_namespace))
    {
      $this->current_namespace = 'default';
    }

    return $this->current_namespace;
  }

  public function getDefaultParamaters()
  {
    return array();
  }

  public function __call($method, $arguments)
  {
     $params = explode('_',sfInflector::tableize($method));

     $callable = array($this, array_shift($params));
     array_unshift($arguments, implode('/', $params));

     return call_user_func_array($callable, $arguments);
  }
}
