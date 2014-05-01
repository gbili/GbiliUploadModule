<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace GbiliUploadModule\Controller\Plugin;

/**
 *
 */
class Uploader extends \Zend\Mvc\Controller\Plugin\AbstractPlugin
{
    protected $service;

    protected $postUploadCallback;

    protected $routeSuccessParams = array('route' => null, 'params' => array(), 'reuse_matched_params' => true);

    /**
     * Upload action
     * @return mixed
     */
    public function __invoke()
    {
        $controller = $this->getController();

        $fileUploader = $this->service;

        if ($controller->getRequest()->isPost()) {

            $fileUploader->setRequest($controller->getRequest())
                         ->setEntityManager($controller->em());

            $fileUploader->uploadFiles();

            if ($this->hasPostUploadCallback()) {
                $callbackReturn = call_user_func($this->getPostUploadCallback(), $fileUploader, $controller);
            }
            
            $messages = $fileUploader->getMessages();

            if ($fileUploader->areAllFilesUploaded()) {
                if (!$fileUploader->isAjax()) {
                    return $controller->redirect()->toRoute(
                        $this->routeSuccessParams['route'],
                        $this->routeSuccessParams['params'],
                        $this->routeSuccessParams['reuse_matched_params']
                    );
                }
            }

            if ($fileUploader->isAjax()) {
                return new \Zend\View\Model\JsonModel(array(
                    'status' => $fileUploader->getUploadStatus(),
                    'callbackReturn' => ((isset($callbackReturn))? $callbackReturn : array()),
                    'messages' => ((isset($messages))? $messages : array()),
                    'formData' => $fileUploader->getPostData(),
                ));
            }
        }

        return new \Zend\View\Model\ViewModel(array(
            'fileUploader' => $fileUploader,
            'messages' => ((isset($messages))? $messages : array()),
            'form' => $fileUploader->getForm(),
        ));
    }

    public function setService(\GbiliUploadModule\Service\Uploader $service)
    {
        $this->service = $service;
        return $this;
    }

    public function getService()
    {
        return $this->service;
    }

    public function setPostUploadCallback($postUploadCallback)
    {
        $this->postUploadCallback = $postUploadCallback;
        return $this;
    }

    public function getPostUploadCallback()
    {
        if (!$this->hasPostUploadCallback()) {
            throw new \Exception('Post Upload Callback not set');
        }
        return $this->postUploadCallback;
    }

    public function hasPostUploadCallback()
    {
        return null !== $this->postUploadCallback;
    }

    public function setRouteSuccessParams(array $routeParams)
    {
        if (isset($routeParams['route'])) {
            $this->routeSuccessParams['route'] = $routeParams['route'];
        }
        if (isset($routeParams['params'])) {
            $this->routeSuccessParams['params'] = $routeParams['params'];
        }
        if (isset($routeParams['reuse_matched_params'])) {
            $this->routeSuccessParams['reuse_matched_params'] = $routeParams['reuse_matched_params'];
        }
        return $this;
    }
}
