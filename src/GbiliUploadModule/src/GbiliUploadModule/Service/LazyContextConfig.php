<?php
namespace GbiliUploadModule\Service;

class LazyContextConfig
{
    /**
     * Narrowed config
     * Config used to fetch the values
     * @var array
     */
    protected $config;

    /**
     * Contains the full config
     * @var array
     */
    protected $fullConfig;

    protected $controllerKey;

    protected $controllerAction;

    /**
     * If set to true, loadedConf
     * will contain all loaded values
     *
     * @var boolean
     */
    protected $debugMode = false;

    /**
     * All values that have successfully been loaded
     * @var array
     */
    protected $loadedConfig = array();

    public function __construct(array $config)
    {
        $this->fullConfig = $config;
    }

    /**
     * @param bool $bool whether in debug mode
     */
    public function debugMode($bool=true)
    {
        $this->debugMode = (boolean) $bool;
        return $this;
    }

    public function getLoadedConfig()
    {
        if (!$this->debugMode) {
            throw new \Exception('Must be in debug mode to get loaded conf');
        }
        return $this->loadedConfig;
    }

    /**
     * Get only a part of the global config
     * Values will only be looked up within
     * this narrowed config array.
     * @param array|string $keys
     * @return void
     */
    public function narrowConfig($keys)
    {
        $diver = new ArrayDive();
        if (!$diver->has($this->fullConfig, $keys)) {
            if (is_array($keys)) {
                $keys = implode('"]["', $keys);
            }
            throw new \Exception('Offset does not exist. $this->config["' . $keys . '"]. ' . print_r($this->fullConfig, true));
        }
        $this->config = $diver->got();
        return $this;
    }

    public function setControllerKey($controllerKey)
    {
        $this->controllerKey = $controllerKey;
        return $this;
    }

    public function getControllerKey()
    {
        if (null === $this->controllerKey) {
            throw new \Exception('Controller key not set, trying to use service before event dispatch?');
        }
        return $this->controllerKey;
    }

    public function setControllerAction($controllerAction)
    {
        $this->controllerAction = $controllerAction;
        return $this;
    }

    public function getControllerAction()
    {
        if (null === $this->controllerAction) {
            throw new \Exception('Controller action not set, trying to use service before event dispatch?');
        }
        return $this->controllerAction;
    }

    /**
     * Checks whether the value can contain an alias
     * @param mixed $value the value to be checked
     * @return mixed:string|false the alias or false
     */
    public function getAliasOrFalse($value)
    {
        if (is_string($value)){
            $alias = $value;
        } else if (is_array($value) && isset($value['alias'])) {
            $alias = $value['alias'];
        } else {
            $alias = false;
        }
        return $alias;
    }

    /**
     * Given some specific config, try to find
     * the path to config value from  keys
     * If no path is found, check if there is possibly
     * an alias, then get the aliased config and iterate
     * @return ArrayDive
     */
    public function diveIntoConfigAndResolveAliased($config, $keys)
    {
        $diver = new ArrayDive();
        // Check if keys exist in config
        if ($diver->has($config, $keys)) {
            return $diver;
        }
        // Resolve aliases
        while ($aliasedKeyOrFalse = $this->getAliasOrFalse($config)) {
            $config = $this->config[$aliasedKeyOrFalse];
            if ($diver->has($config, $keys)) break;
        }
        return $diver;
    }

    /**
     * Use getDiver to find some config value given some
     * config keys in a specific order|priority
     *
     * @param array $keys the path that leads to config value
     * @param mixed $default the return value if no config is found
     * @return mixed, the config under $keys path or $default 
     */
    public function getConfigValue($keys, $default=null)
    {
        $controllerKey = $this->getControllerKey();
        $controllerAction = $this->getControllerAction();
        $configs = array();
        $return = $default;
        if (isset($this->config[$controllerKey]['action_override'][$controllerAction])) {
            $configs[] = $this->config[$controllerKey]['action_override'][$controllerAction];
        }
        if (isset($this->config[$controllerKey])) {
            $configs[] = $this->config[$controllerKey];
        }
        $configs[] = $this->config;

        foreach ($configs as $config) {
            $diver = $this->diveIntoConfigAndResolveAliased($config, $keys);
            if ($diver->had()) {
                $return = $diver->got();
                break;
            }
        }
        if ($this->debugMode) {
            $this->loadedConf['["' . implode('"]["', $keys) . '"]'] = $return;
        }
        return $return;
    }
}
