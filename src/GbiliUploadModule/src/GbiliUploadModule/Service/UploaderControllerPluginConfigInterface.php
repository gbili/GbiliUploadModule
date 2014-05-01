<?php
namespace GbiliUploadModule\Service;

interface UploaderControllerPluginConfigInterface
{
    public function configureControllerPlugin(\GbiliUploadModule\Controller\Plugin\Uploader $fu);
}

