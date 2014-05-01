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
    ),
    'aliases' => array(
        'uploader_service' => __NAMESPACE__ . '\Service\Uploader',
    ),
);
