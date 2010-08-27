<?php
/**
 * Abstract class for both version of OAuth 1 and 2
 *
 * @author Maxime Picaud
 * @since 21 août 2010
 */
abstract class sfOAuth
{
  /**
   * version of OAuth used
   *
   * @var integer $version
   */
  protected $version;

  /**
   * the key retrieve from the service
   *
   * @var string $key
   */
  protected $key;

  /**
   * the secret key retrieve from the service
   *
   * @var string $secret
   */
  protected $secret;

  /**
   * Can be access token or request token. Is the current token
   *
   * @var Token
   */
  protected $token;

  /**
   * The url to request authorization
   *
   * @var string $request_auth_url
   */
  protected $request_auth_url;

  /**
   * Url to retrieve the access token
   *
   * @var string $access_token_url
   */
  protected $access_token_url;

  /**
   * Namespaces used to access api
   *
   * @var array $namespaces
   */
  protected $namespaces = array();

  /**
   * The current namespace. By default is the 'default' namespace.
   *
   * @var string $current_namespace
   */
  protected $current_namespace;

  /**
   * the symfony controller
   *
   * @var sfFrontWebController $controller
   */
  protected $controller;

  /**
   * The name of the instance. used to store in the database and recognize it
   *
   * @var string $name
   */
  protected $name;

  /**
   * Callback to use after request Auth. can be an internal routing rule like @homepage
   *
   * @var string $callback
   */
  protected $callback;

  /**
   * parameters passed for each api request.
   *
   * @var array $parameters
   */
  protected $access_parameters = array();

  /**
   * parameters passed for each api request.
   *
   * @var array $parameters
   */
  protected $auth_parameters = array();

  /**
   * parameters passed for each api request.
   *
   * @var array $parameters
   */
  protected $call_parameters = array();

  /**
   * parameters passed for each api request.
   *
   * @var array $parameters
   */
  protected $aliases = array();

  /**
   * @var string $output_format
   */
  protected $output_format = 'json';

  /**
   *
   * @param string $key
   * @param string $secret
   * @param Token $token
   * @param array $config
   *
   * Constructor
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function __construct($key, $secret, $token = null, $config = array())
  {
    $this->setKey($key);
    $this->setSecret($secret);
    $this->setToken($token);

    $this->initialize($config);

    $this->init($config, 'callback');
    $this->init($config, 'request_auth_url');
    $this->init($config, 'access_token_url');
    $this->init($config, 'namespaces');
    $this->init($config, 'current_namespace');
    $this->init($config, 'controller');
    $this->init($config, 'name');
    $this->init($config, 'callback');
    $this->init($config, 'auth_parameters', 'add');
    $this->init($config, 'call_parameters', 'add');
    $this->init($config, 'access_parameters', 'add');
    $this->init($config, 'aliases', 'add');
    $this->init($config, 'output_format');


  }

  /**
   *
   * @param array $config
   * @param mixed $key
   *
   * to init config parameters
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  protected function init($config, $key, $prefix = 'set')
  {
    if(isset($config[$key]))
    {
      $method = $prefix.sfInflector::classify($key);
      $this->$method($config[$key]);
    }
  }

  /**
   *
   * @param array $config
   *
   * Initialize child classes
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  abstract protected function initialize($config);

  /**
   * implemented by child classes to request auth
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  abstract public function requestAuth($parameters = array());

  /**
   *
   * @param string $verifier
   *
   * get the access token with the verification code
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  abstract public function getAccessToken($verifier, $parameters = array());

  /**
   *
   * @param sfMelodyUser $user
   *
   * Allow to connect an user to this service
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  abstract public function connect($user, $parameters = array());

  /**
   * Identifier is used to create user with unique name. must be override in child classes
   * to have user id of the service for example
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function getIdentifier()
  {
    return mt_rand(0, 99999999999);
  }

  /**
   * Could be overriden in child classes for those need to refresh their tokens
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function refreshToken()
  {
  }

  /**
   *
   * @param Token $token
   *
   * Idem as refreshToken
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  protected function setExpire(&$token)
  {
  }

  /**
   * getter $version
   *
   * @return integer
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function getVersion()
  {
    return $this->version;
  }

  /**
   * getter $key
   *
   * @return string
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function getKey()
  {
    return $this->key;
  }

  /**
   *
   * @param string $key
   *
   * setter $key
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function setKey($key)
  {
    $this->key = $key;
  }

  /**
   * getter $secret
   *
   * @return string
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function getSecret()
  {
    return $this->secret;
  }

  /**
   *
   * @param string $secret
   *
   * setter $secret
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function setSecret($secret)
  {
    $this->secret = $secret;
  }

  /**
   * @param string $format
   *
   * format can be 'oauth' to retrieve en OAuthToken Object
   * getter $token
   *
   * @return Token
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

  /**
   *
   * @param Token $token
   *
   * setter $token
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function setToken($token)
  {
    $this->token = $token;
  }

  /**
   * getter $output_format
   *
   * @return string
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function getOutputFormat()
  {
    return $this->output_format;
  }

  /**
   *
   * @param string $output_format
   *
   * setter $output_format
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function setOutputFormat($output_format)
  {
    $this->output_format = $output_format;
  }

  /**
   * getter $request_auth_url
   *
   * @return string
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function getRequestAuthUrl()
  {
    return $this->request_auth_url;
  }

  /**
   *
   * @param string $request_auth_url
   *
   * setter $request_auth_url
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function setRequestAuthUrl($request_auth_url)
  {
    $this->request_auth_url = $request_auth_url;
  }

  /**
   * getter $access_token_url
   *
   * @return string
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function getAccessTokenUrl()
  {
    return $this->access_token_url;
  }

  /**
   *
   * @param string $access_token_url
   *
   * setter $access_token_url
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function setAccessTokenUrl($access_token_url)
  {
    $this->access_token_url = $access_token_url;
  }

  /**
   * getter $controller. If not set call to the default context
   *
   *
   * @return sfFrontWebController
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function getController()
  {
    if(is_null($this->controller))
    {
      $this->controller = sfContext::getInstance()->getController();
    }

    return $this->controller;
  }

  /**
   *
   * @param sfFrontWebController $controller
   *
   * setter $controller
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function setController(sfFrontWebController $controller)
  {
    $this->controller = $controller;
  }

  /**
   * getter $callback
   *
   * @return string
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function getCallback()
  {
    return $this->callback;
  }

  /**
   *
   * @param string $callback
   *
   * setter callback - url or rouging rule like @homepage
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function setCallback($callback)
  {
    if(strpos($callback, '@') !== false)
    {
      $callback = $this->getController()->genUrl($callback, true);
    }

    $this->getController()->convertUrlStringToParameters($callback);

    $this->callback = $callback;
  }

  /**
   * getter $name
   *
   * @return string
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   *
   * @param string $name
   *
   * setter $name
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function setName($name)
  {
    $this->name = $name;
  }

  /**
   *
   * @param array $parameters
   *
   * setter $parameters
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function setAuthParameters($parameters)
  {
    $this->auth_parameters = $parameters;
  }

  /**
   *
   * @param mixed $key
   * @param mixed $value
   *
   * set a parameter
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function setAuthParameter($key, $value)
  {
    $this->auth_parameters[$key] = $value;
  }

  /**
   * getter $parameters
   *
   * @return array
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function getAuthParameters()
  {
    return $this->auth_parameters;
  }

  /**
   *
   * @param mixed $key
   * @param mixed $default
   *
   * Retrieve a parameter by its key and return $default if is undefined
   *
   * @return mixed
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function getAuthParameter($key, $default = null)
  {
    return isset($this->auth_parameters[$key])?$this->auth_parameters[$key]:$default;
  }

  /**
   *
   * @param array $parameters
   *
   * merge current parameters with this $parameters
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function addAuthParameters($parameters)
  {
    $this->auth_parameters = array_merge($this->auth_parameters, $parameters);
  }

  /**
   *
   * @param array $parameters
   *
   * setter $parameters
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function setAccessParameters($parameters)
  {
    $this->access_parameters = $parameters;
  }

  /**
   *
   * @param mixed $key
   * @param mixed $value
   *
   * set a parameter
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function setAccessParameter($key, $value)
  {
    $this->access_parameters[$key] = $value;
  }

  /**
   * getter $parameters
   *
   * @return array
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function getAccessParameters()
  {
    return $this->access_parameters;
  }

  /**
   *
   * @param mixed $key
   * @param mixed $default
   *
   * Retrieve a parameter by its key and return $default if is undefined
   *
   * @return mixed
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function getAccessParameter($key, $default = null)
  {
    return isset($this->access_parameters[$key])?$this->access_parameters[$key]:$default;
  }

  /**
   *
   * @param array $parameters
   *
   * merge current parameters with this $parameters
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function addAccessParameters($parameters)
  {
    $this->access_parameters = array_merge($this->access_parameters, $parameters);
  }

  /**
   *
   * @param array $parameters
   *
   * setter $parameters
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function setCallParameters($parameters)
  {
    $this->call_parameters = $parameters;
  }

  /**
   *
   * @param mixed $key
   * @param mixed $value
   *
   * set a parameter
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function setCallParameter($key, $value)
  {
    $this->call_parameters[$key] = $value;
  }

  /**
   * getter $parameters
   *
   * @return array
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function getCallParameters()
  {
    return $this->call_parameters;
  }

  /**
   *
   * @param mixed $key
   * @param mixed $default
   *
   * Retrieve a parameter by its key and return $default if is undefined
   *
   * @return mixed
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function getCallParameter($key, $default = null)
  {
    return isset($this->call_parameters[$key])?$this->call_parameters[$key]:$default;
  }

  /**
   *
   * @param array $parameters
   *
   * merge current parameters with this $parameters
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function addCallParameters($parameters)
  {
    $this->call_parameters = array_merge($this->call_parameters, $parameters);
  }

  /**
   *
   * @param array $parameters
   *
   * setter $parameters
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function setAliases($aliases)
  {
    $this->aliases = $aliases;
  }

  /**
   *
   * @param mixed $key
   * @param mixed $value
   *
   * set an alias
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function setAlias($key, $value)
  {
    $this->aliases[$key] = $value;
  }

  /**
   * getter $aliases
   *
   * @return array
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function getAliases()
  {
    return $this->aliases;
  }

  /**
   *
   * @param mixed $key
   * @param mixed $default
   *
   * Retrieve an alias by its key and return $default if is undefined
   *
   * @return mixed
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function getAlias($key, $default = null)
  {
    return isset($this->aliases[$key])?$this->aliases[$key]:$default;
  }

  /**
   *
   * @param array $aliases
   *
   * merge current aliases with this $aliases
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function addAliases($aliases)
  {
    $this->aliases = array_merge($this->aliases, $aliases);
  }

  /**
   *
   * @param array $namespaces
   *
   * setter $namespaces
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function setNamespaces($namespaces)
  {
    $this->namespaces = $namespaces;
  }

  /**
   *
   * @param mixed $key
   * @param string $value
   *
   * set a specific namespace
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function setNamespace($key, $value)
  {
    $this->namespaces[$key] = $value;
  }

  /**
   * getter $namespaces
   *
   * @return array
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function getNamespaces()
  {
    return $this->namespaces;
  }

  /**
   *
   * @param mixed $key
   *
   * return a specifi namespace
   *
   * @return string
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function getNamespace($key)
  {
    return isset($this->namespaces[$key])?$this->namespaces[$key]:$default;
  }

  /**
   *
   * @param array $namespaces
   *
   * mixed with existing namespaces
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function addNamespaces($namespaces)
  {
    $this->namespaces = array_merge($this->namespaces, $namespaces);
  }

  /**
   *
   * @param string $url
   * @param array $params
   * @param string $method
   *
   * call REST Api
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  protected function call($url, $params = array(), $method = 'POST')
  {
    $ci = curl_init();

    if($method == 'POST')
    {
      curl_setopt($ci, CURLOPT_POST, true);
      curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
    }
    elseif($method == 'GET' && count($params) > 0)
    {
      $params = http_build_query($params);
      if(strpos($url, '?') !== false)
      {
        $url .= '&'.$params;
      }
      else
      {
        $url .= '?'.$params;
      }
    }

    curl_setopt($ci, CURLOPT_HEADER, false);
    curl_setopt($ci, CURLOPT_URL, $url);
    curl_setopt($ci,CURLOPT_RETURNTRANSFER,true);

    $response = curl_exec($ci);
    curl_close ($ci);

    return $response;
  }

  /**
   *
   * @param string $namespace
   * @throws sfException
   *
   * Choose the current namespace
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function ns($namespace)
  {
    if(in_array($namespace, array_keys($this->namespaces)))
    {
      $this->current_namespace = $namespace;
    }
    else
    {
      throw new sfException(sprintf('Namespace "%s" is not defined for Melody "%s"', $namespace, get_class($this)));
    }

    return $this;
  }

  /**
   * getter $current_namespace
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function getCurrentNamespace()
  {
    if(is_null($this->current_namespace))
    {
      $this->current_namespace = 'default';
    }

    return $this->current_namespace;
  }

  /**
   *
   * @param string $url
   * @param array $url_params
   *
   * apply aliases on the url
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function applyUrlParams($url, $aliases)
  {
    foreach($aliases as $key => $alias)
    {
      $url = preg_replace('/\/'.$key.'(\/|$)/', '/'.$alias.'$1', $url);
    }

    return $url;
  }

  /**
   *
   * @param string $method
   * @param array $arguments
   *
   * Used for api call
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function __call($method, $arguments)
  {
    $params = explode('_',sfInflector::tableize($method));

    $callable = array($this, array_shift($params));
    array_unshift($arguments, implode('/', $params));

    if(is_callable($callable))
    {
      return call_user_func_array($callable, $arguments);
    }
    else throw new sfException(sprintf('method "%s" does not exists in "%s" class', $callable[1], get_class($this)));
  }

  /**
   *
   * @param string $action
   * @param array $url_params
   * @param array $params
   * @param string $method
   *
   * make api call
   *
   * @author Maxime Picaud
   * @since 21 août 2010
   */
  public function get($action, $aliases = null, $parameters = array(), $method = 'GET')
  {
    if(is_null($this->getToken()))
    {
      throw new sfException(sprintf('there is no available token to make an api call in "%s" oauth', $this->getName()));
    }

    $base_url = $this->getNamespace($this->getCurrentNamespace());

    $url = $base_url.'/'.$action;

    if(is_string($aliases))
    {
      $url .= '/'.$aliases;
    }
    elseif(is_array($aliases))
    {
      $aliases = array_merge($this->getAliases(), $aliases);
    }

    if(!is_array($aliases))
    {
      $aliases = $this->getAliases();
    }

    return $this->applyUrlParams($url, $aliases);
  }
}
