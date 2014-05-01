<?php
namespace Upload;
return array(
    'factories' => array(
        'fileUploader' => function ($controllerPluginManager) {
            $sm = $controllerPluginManager->getServiceLocator();
            $plugin = new \Upload\Controller\Plugin\Uploader;
            $sm->get('uploaderConfig')->configureControllerPlugin($plugin);
            return $plugin;
        },
    ),
);
