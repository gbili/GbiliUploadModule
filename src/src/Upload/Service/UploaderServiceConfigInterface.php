<?php
namespace Upload\Service;

interface UploaderServiceConfigInterface
{
    public function configureService(\Upload\Service\Uploader $fu);
}
