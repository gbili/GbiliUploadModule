<?php
namespace GbiliUploadModule\Form\InputFilter;

/**
 * Creates an input filter an allows to pass a renameupload filter
 * as file_input_filter key in options array
 * It will also attempt to retrieve one from the plugin manager if
 * the name is provided under ['file_input_filter']['name']
 */
class FileInputFilterFactory
{
    /**
     * Create an input filter for files
     * @return \Zend\InputFilter\InputFilterInterface
     */
    public function createInputFilter($options)
    {
        // File Input
        $file = new \Zend\InputFilter\FileInput($options['file_input_name']);
        $file->setRequired(true);
 
        //Validators
        $validatorChain = $file->getValidatorChain();
        $validatorChain->addByName('fileextension', array('extension' => 'jpg'));
        $validatorChain->addByName('filemimetype',  array('mimeType'  => 'image/jpg,image/jpeg',));
        /*$validatorChain->addByName('filesize', array('min' => 200, 'max' => 204800));*/

        $filterChain = $file->getFilterChain();
        $filterChain->attach($this->getFilter($options, $filterChain->getPluginManager()));

        $inputFilter = new \Zend\InputFilter\InputFilter();
        $inputFilter->add($file);
        return $inputFilter;
    }

    public function getFilter($config, $pluginManager)
    {
        $filter = $config['file_input_filter'];
        if (is_array($filter)) {
            $filter = $pluginManager->get($filter['name'], $filter['options'])
        }
        if (!($filter instanceof \Zend\Filter\FilterInterface)) {
            throw new \Exception('Filter must implement \Zend\Filter\FilterInterface');
        }
        return $filter;
    }
}
