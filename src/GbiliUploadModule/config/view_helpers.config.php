<?php
namespace GbiliUploadModule;
return array(
    'invokables' => array(
        'fileUploadMessage' => __NAMESPACE__ . '\View\Helper\FileUploadMessage',
        'gbiliUploadModuleScriptPath' => __NAMESPACE__ . '\View\Helper\Scripts',
    ),
    'factories' => array(
        'uploader' => function ($viewHelperPluginManager) {
            $sm = $viewHelperPluginManager->getServiceLocator();
            $viewHelper = new View\Helper\Uploader;
            $viewHelper->setService($sm->get(__NAMESPACE__ . '\Service\Uploader'));
            return $viewHelper;
        },
    ),
);
