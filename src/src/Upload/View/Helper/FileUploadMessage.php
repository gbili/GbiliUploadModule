<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Upload\View\Helper;

/**
 * View helper for translating messages.
 */
class FileUploadMessage extends \Zend\View\Helper\AbstractHelper
{
    /**
     * Translate a message
     * @return string
     */
    public function __invoke(array $message)
    {
        $neededKeys = array('class', 'fileName', 'message');
        $missingKeys = array_diff(array_keys($message), $neededKeys);

        if (!empty($missingKeys)) {
            return $this->notSupported($message);
        }

        $class    = $message['class'];
        $fileName = $message['fileName'];
        $message  = $message['message'];

        return $this->view->message($class, $message, $fileName);
    }

    public function notSupported($message)
    {
        throw new \Exception('The message has not the required keys. Either change the message structure. Or use a different message view helper. Message: ' . print_r($message, true));
    }
}
