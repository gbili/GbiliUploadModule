<?php
namespace GbiliUploadModule\Service;

class UploaderConfig implements UploaderServiceConfigInterface, UploaderControllerPluginConfigInterface
{
    protected $sm;

    /**
     * @var ContextConfig
     */
    protected $config;

    public function __construct(LazyContextConfig $config)
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

    public function configureService(\GbiliUploadModule\Service\Uploader $service)
    {
        $actionRouteParams = $this->config->getConfigValue(['service', 'form_action_route_params'], false);
        if ($actionRouteParams) {
            $service->setFormActionRouteParams($actionRouteParams);
        }

        //TODO move this config to the view helper
        //First dont allow to get the config from alias. Then try the other not from alias, then on last resort from alias
        if ($jsScriptPath = $this->config->getConfigValue(['view_helper', 'include_js_script'], false, $allowAlias=false)) {
            $service->setIncludeScriptFilePath($jsScriptPath);
        } else if ($packagedJsScriptName = $this->config->getConfigValue(['view_helper', 'include_packaged_js_script_from_basename'], false)) {
            $service->setIncludeScriptFilePath($this->getScriptPath($packagedJsScriptName));
        } else if ($jsScriptPath = $this->config->getConfigValue(['view_helper', 'include_js_script'], false, $allowAlias=true)) {
            $service->setIncludeScriptFilePath($jsScriptPath);
        }
        $service->displayFormAsPopup($this->config->getConfigValue(['view_helper', 'display_form_as_popup'], false));
        $service->setFormInitialStateHidden($this->config->getConfigValue(['view_helper', 'popup_initial_state_hidden'], true));
        
        $service->setFileHydrator($this->getServiceFileHydrator());

        $service->setForm($this->createForm());
        return $service;
    }

    /**
     * Create the form with the righ config
     */
    protected function createForm()
    {
        $filterOptions = $this->config->getConfigValue(['form', 'file_input_filter_options'], array());
        $uploadTarget = $this->config->getConfigValue(['form', 'file_upload_dirpath'], null);
        if (is_string($uploadTarget)) {
            $filterOptions['target'] = $uploadTarget . '/media.jpg';
        }

        $options = array(
            'file_input_filter' => array(
                'name' => $this->config->getConfigValue(['form', 'file_input_filter_name'], 'filerenameupload'),
                'options' => $filterOptions,
            ),
            'file_input_name' => $this->config->getConfigValue(['form', 'file_input_name'], 'file_input'), 
        );

        // Pass the S3 client to s3rename upload filter
        if ('s3renameupload' === $options['file_input_filter']['name']) {
            $options['file_input_filter']['options']['client'] = $this->getServiceLocator()->get('Aws')->get('S3');
        }

        $inputFilterFactory = new \GbiliUploadModule\Form\InputFilter\FileInputFilterFactory;
        $inputFilter = $inputFilterFactory->createInputFilter($options);

        $form = new \GbiliUploadModule\Form\Html5MultiUpload($this->config->getConfigValue(['form', 'form_name'], 'file_form'), $options);
        $form->setAttribute('id', $this->config->getConfigValue(['form', 'form_id'], 'gbiliuploader_upload_form'));
        $form->setInputFilter($inputFilter);
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

    public function getServiceFileHydrator()
    {
        $fileHydrator = $this->config->getConfigValue(['service', 'file_hydrator'], false); 
        if (!$fileHydrator) {
            throw new \Exception('You must set a file hydrator to allow the "uploader" to save entities');
        }
        return $this->sm->get($fileHydrator);
    }

    public function configureControllerPlugin(\GbiliUploadModule\Controller\Plugin\Uploader $plugin)
    {
        $postUploadCallback = $this->config->getConfigValue(['controller_plugin', 'post_upload_callback'], false);
        if ($postUploadCallback) {
            if (!is_callable($postUploadCallback)) {
                throw new \Exception('post_upload_callback is not callable');
            }
            $plugin->setPostUploadCallback($postUploadCallback);
        }

        $routeSuccess = $this->config->getConfigValue(['controller_plugin', 'route_success'], false);
        if ($routeSuccess) {
            $plugin->setRouteSuccessParams($routeSuccess);
        }

        $plugin->setService($this->getServiceLocator()->get('GbiliUploadModule\Service\Uploader'));
    }
}
