<?php
namespace GbiliUploadModule\Service;

class LazyContextConfigFactory implements \Zend\ServiceManager\FactoryInterface
{
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $sm)
    {
        $config = $sm->get('Config');

        $class = substr(get_class($this), 0, -(strlen('Factory')));
        $service = new $class($config);

        if (isset($config['lazy_context_config']['debug_mode'])) {
            $service->debugMode($config['lazy_context_config']['debug_mode']);
        }

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
