<?php
namespace GbiliUploadModule\Service;

class ContextConfig implements UploaderServiceConfigInterface, UploaderControllerPluginConfigInterface
{
    const DEFAULT_CONFIG_KEY              = 'default';

    const ERROR_MISSING_CONTROLLER_CONFIG = 0;
    const ERROR_MISSING_DEFAULT_CONFIG    = 1;
    const ERROR_MISSING_CONFIG_ALIAS      = 2;

    protected $errorMessages = array(
          self::ERROR_MISSING_CONTROLLER_CONFIG => 'Controller impelements \GbiliUploadModule\ConfigKeyAwareInterface, but no controller specific configuration was found',
          self::ERROR_MISSING_DEFAULT_CONFIG    => 'There is no controller specific config, nor a default config',
          self::ERROR_MISSING_CONFIG_ALIAS      => "'file_uploader':'%s' config, references an alias. But no 'file_uploader':%s isset.",
    );

    protected $sm;

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

    /**
     * Controller specific config
     */
    protected $specificConfig;

    /**
     * aliased config, can be used if some controller specific config key is not set
     * would be better if it was mergeable, to the controller spcific but I dont know how
     * array_merge_recursive adds new arrays when key exists
     */
    protected $aliasedConfig;

    protected $controllerKey;

    protected $controllerAction;

    public function __construct(array $config)
    {
        $this->fullConfig = $config;
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
            throw new \Exception('Offset does not exist. $this->config["' . implode('"]["', $keys). '"]');
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
     * Allows to get configurations based on controller and action
     * scope. To avoid configuration repetition, controller and action
     * config may alias configuration.
     * @return array configuration specifically defined within some config key
     */
    public function getSpecificConfig()
    {
        if (null !== $this->specificConfig) {
            return $this->specificConfig;
        }

        $configKey = $this->getConfigKey();
        $config    = $this->config;

        if (!isset($config[$configKey])) {
            throw new \Exception(
                $this->errorMessages[(($configKey === self::DEFAULT_CONFIG_KEY)
                    ? self::ERROR_MISSING_DEFAULT_CONFIG 
                    : self::ERROR_MISSING_CONTROLLER_CONFIG)]
            );
        }

        $specificConfig = $config[$configKey];

        if (isset($specificConfig['alias'])) {
            $configAlias = $specificConfig['alias'];
            if (!isset($config[$configAlias])) {
                throw new \Exception(sprintf($this->errorMessages[self::ERROR_MISSING_CONFIG_ALIAS], $configKey, $configAlias));
            }
            $this->aliasedConfig  = $config[$configAlias];
        }

        $controllerAction = $this->getControllerAction();
        if (isset($specificConfig['action_override'][$controllerAction])) {
            $actionConfig = $specificConfig['action_override'][$controllerAction];
            foreach (array('controller_plugin', 'service', 'view_helper') as $what) {
                if (!isset($actionConfig[$what])) continue;
                if (!isset($specificConfig[$what])) {
                    $specificConfig[$what] = array();
                }
                foreach ($actionConfig[$what] as $key => $value) {
                    $specificConfig[$what][$key] = $value;
                }
            }
        }

        $this->specificConfig = $specificConfig;

        return $specificConfig; 
    }

    /**
     * Get the config key based on the controller
     * being served. If the config is an alias,
     * the returns the aliased key.
     *
     * @return string
     */
    public function getConfigKey()
    {   
        $config    = $this->config;
        $configKey = $this->getControllerKey();

        if (!isset($config[$configKey])) {
            $configKey = self::DEFAULT_CONFIG_KEY;
        }
        if (is_string($config[$configKey])) {
            $configAlias = $config[$configKey];
            if (!isset($config[$configAlias])) {
                throw new \Exception(sprintf($this->errorMessages[self::ERROR_MISSING_CONFIG_ALIAS], $configKey, $configAlias));
            }
            $configKey = $configAlias;
        }
        return $configKey;
    }

    public function getConfigValue($keys, $default, $allowAlias=true)
    {
        $diver = new ArrayDive();
        if ($diver->has($this->getSpecificConfig(), $keys)) {
            $value = $diver->got();
        } else if ($allowAlias && $diver->has($this->aliasedConfig, $keys)) {
            $value = $diver->got();
        } else if ($diver->has($this->config, $keys)) {// Global config
            $value = $diver->got();
        } else {
            $value = $default;
        }
        return $value;
    }
}
