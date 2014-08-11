<?php
namespace GbiliUploadModule\Service;

class ContextConfigFactory implements \Zend\ServiceManager\FactoryInterface
{
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $sm)
    {
        $config = $sm->get('Config');

        $service = new ContextConfig($config);

        $routeMatch = $sm->get('Application')->getMvcEvent()->getRouteMatch();
        $controllerKey = $routeMatch->getParam('controller');
        if (null === $controllerKey) {
            throw new \Exception('Trying to use Uploader service before routing has occured');
        }
        $service->setControllerKey($controllerKey);

        $controllerAction = $routeMatch->getParam('action');
        if (null === $controllerAction) {
            throw new \Exception('Trying to use Uploader service before routing has occured');
        }

        $service->setControllerAction($controllerAction);
        return $service;
    }

}
