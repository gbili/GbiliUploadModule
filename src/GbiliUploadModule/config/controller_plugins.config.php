<?php
namespace GbiliUploadModule;
return array(
    'factories' => array(
        'fileUploader' => function ($controllerPluginManager) {
            $sm = $controllerPluginManager->getServiceLocator();
            $plugin = new \GbiliUploadModule\Controller\Plugin\Uploader;
            $sm->get('uploaderConfig')->configureControllerPlugin($plugin);
            return $plugin;
        },
    ),
);
