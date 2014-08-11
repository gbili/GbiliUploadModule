<?php
namespace GbiliUploadModule;
return array(
    'factories' => array(
        'uploaderConfig' => __NAMESPACE__ . '\Service\UploaderConfigFactory',
        __NAMESPACE__ . '\Service\Uploader' => function ($sm) {
            $service = new Service\Uploader();
            $sm->get('uploaderConfig')->configureService($service);
            return $service;
        },
        __NAMESPACE__ . '\Service\ContextConfig' => __NAMESPACE__ . '\Service\ContextConfigFactory',
    ),
    'aliases' => array(
        'uploader_service' => __NAMESPACE__ . '\Service\Uploader',
        'contextConfig' => __NAMESPACE__ . '\Service\ContextConfig',
    ),
);
