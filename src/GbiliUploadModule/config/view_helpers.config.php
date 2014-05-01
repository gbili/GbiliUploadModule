<?php
namespace Upload;
return array(
    'invokables' => array(
        'fileUploadMessage' => __NAMESPACE__ . '\View\Helper\FileUploadMessage',
    ),
    'factories' => array(
        'uploader' => function ($viewHelperPluginManager) {
            $sm = $viewHelperPluginManager->getServiceLocator();
            $viewHelper = new View\Helper\Uploader;
            $viewHelper->setService($sm->get('Upload\Service\Uploader'));
            return $viewHelper;
        },
    ),
);
