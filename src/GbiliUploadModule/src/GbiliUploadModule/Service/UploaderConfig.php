<?php
namespace GbiliUploadModule\Service;

class UploaderConfig implements UploaderServiceConfigInterface, UploaderControllerPluginConfigInterface
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

    protected $config;

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
        $this->config = $config;
    }

    public function setServiceLocator($sm)
    {
        $this->sm = $sm;
        return $this;
    }

    public function getServiceLocator()
    {
        return $this->sm;
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

    public function configureService(\GbiliUploadModule\Service\Uploader $service)
    {
        $actionRouteParams = $this->getConfigValue('service', 'form_action_route_params', false);
        if ($actionRouteParams) {
            $service->setFormActionRouteParams($actionRouteParams);
        }

        //TODO move this config to the view helper
        //First dont allow to get the config from alias. Then try the other not from alias, then on last resort from alias
        if ($jsScriptPath = $this->getConfigValue('view_helper', 'include_js_script', false, $allowAlias=false)) {
            $service->setIncludeScriptFilePath($jsScriptPath);
        } else if ($packagedJsScriptName = $this->getConfigValue('view_helper', 'include_packaged_js_script_from_basename', false)) {
            $service->setIncludeScriptFilePath($this->getScriptPath($packagedJsScriptName));
        } else if ($jsScriptPath = $this->getConfigValue('view_helper', 'include_js_script', false, $allowAlias=true)) {
            $service->setIncludeScriptFilePath($jsScriptPath);
        }
        $service->displayFormAsPopup($this->getConfigValue('view_helper', 'display_form_as_popup', false));
        $service->setFormInitialStateHidden($this->getConfigValue('view_helper', 'popup_initial_state_hidden', true));
        
        $service->setFileHydrator($this->getServiceFileHydrator());

        $service->setForm($this->createForm());
        return $service;
    }

    /**
     * Create the form with the righ config
     */
    protected function createForm()
    {
        $filterOptions = $this->getConfigValue('form', 'file_input_filter_options', array());
        $uploadTarget = $this->getConfigValue('form', 'file_upload_dirpath', null);
        if (null !== $uploadTarget) {
            $filterOptions['target'] = $uploadTarget . '/media.jpg';
        }
        $options = array(
            'file_input_filter' => array(
                'name' => $this->getConfigValue('form', 'file_input_filter_name', 'filerenameupload'),
                'options' => $filterOptions,
            ),
            'file_input_name' => $this->getConfigValue('form', 'file_input_name', 'file_input'), 
        );
        $formName = $this->getConfigValue('form', 'form_name', 'file_form');
        $formId = $this->getConfigValue('form', 'form_id', 'gbiliuploader_upload_form');
        $form = new \GbiliUploadModule\Form\Html5MultiUpload($formName, $options);
        $form->setAttribute('id', $formId);
        return $form;
    }

    protected function getScriptPath($scriptBasename)
    {
        $path = __DIR__ . str_repeat('/..', 3) . '/view/partial' . '/' . $scriptBasename;
        if (!file_exists($path)) {
            throw new \Exception('Wrong partial basename provided, file does not exist path: ' . $path);
        }
        return $path;
    }

    public function getSpecificConfig()
    {
        if (null !== $this->specificConfig) {
            return $this->specificConfig;
        }

        $configKey = $this->getConfigKey();
        $config    = $this->config;

        if (!isset($config[$configKey])) {
            throw new \Exception(
                $this->errorMessages[(($configKey === self::DEFAULT_CONFIG_KEY)? self::ERROR_MISSING_DEFAULT_CONFIG : self::ERROR_MISSING_CONTROLLER_CONFIG)]
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

    protected function getConfigValue($for, $key, $default, $allowAlias=true)
    {
        $config = $this->getSpecificConfig();
        if (isset($config[$for][$key])) {
            $value = $config[$for][$key];
        } else if ($allowAlias && isset($this->aliasedConfig[$for][$key])) {
            $value = $this->aliasedConfig[$for][$key];
        } else if (isset($this->config[$for][$key])) { // Global config
            $value = $this->config[$for][$key];
        } else {
            $value = $default;
        }
        return $value;
    }

    public function getServiceFileHydrator()
    {
        $fileHydrator = $this->getConfigValue('service', 'file_hydrator', false); 
        if (!$fileHydrator) {
            throw new \Exception('You must set a file hydrator to allow the "uploader" to save entities');
        }
        return $this->sm->get($fileHydrator);
    }

    public function configureControllerPlugin(\GbiliUploadModule\Controller\Plugin\Uploader $plugin)
    {
        $postUploadCallback = $this->getConfigValue('controller_plugin', 'post_upload_callback', false);
        if ($postUploadCallback) {
            if (!is_callable($postUploadCallback)) {
                throw new \Exception('post_upload_callback is not callable');
            }
            $plugin->setPostUploadCallback($postUploadCallback);
        }

        $routeSuccess = $this->getConfigValue('controller_plugin', 'route_success', false);
        if ($routeSuccess) {
            $plugin->setRouteSuccessParams($routeSuccess);
        }

        $plugin->setService($this->getServiceLocator()->get('GbiliUploadModule\Service\Uploader'));
    }
}
