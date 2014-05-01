<?php
namespace Upload\Service;

interface UploaderControllerPluginConfigInterface
{
    public function configureControllerPlugin(\Upload\Controller\Plugin\Uploader $fu);
}

