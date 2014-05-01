<?php
namespace Upload;
return array(
    'factories' => array(
        'uploaderConfig' => __NAMESPACE__ . '\Service\UploaderConfigFactory',

        'Upload\Service\Uploader' => function ($sm) {
            $service = new Service\Uploader();
            $sm->get('uploaderConfig')->configureService($service);
            return $service;
        },
    ),
    'aliases' => array(
        'uploader_service' => 'Upload\Service\Uploader',
    ),
);
