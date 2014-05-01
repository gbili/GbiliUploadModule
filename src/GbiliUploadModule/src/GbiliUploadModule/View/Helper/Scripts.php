<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace GbiliUploadModule\View\Helper;

/**
 * View helper for translating messages.
 */
class Scripts extends \Zend\View\Helper\AbstractHelper
{
    /**
     * Translate a message
     * @return string
     */
    public function __invoke($scriptName)
    {
        return $this->getScriptPath($scriptName);
    }

    public function getScriptPath($scriptName)
    {
        $scriptPath = __DIR__ . '/../../../../view/partial/' . $scriptName . '.phtml';
        if (!file_exists($scriptPath)) {
            throw new \Exception('The requested script does not exist');
        }
        return $scriptPath;
    }
}
