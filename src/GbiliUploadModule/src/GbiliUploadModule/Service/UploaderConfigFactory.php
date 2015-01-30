<?php
namespace GbiliUploadModule\Service;

class UploaderConfigFactory implements \Zend\ServiceManager\FactoryInterface
{
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $sm)
    {
        $service = new UploaderConfig($sm->get('lazyContextConfig')->narrowConfig('file_uploader'));
        $service->setServiceLocator($sm);
        return $service;
    }
}
