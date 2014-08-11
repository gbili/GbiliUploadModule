<?php
namespace GbiliUploadModule\Form;

class Html5MultiUpload extends \Zend\Form\Form
{
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);
        $this->addElements();
        $this->setInputFilter($this->createInputFilter());
    }

    public function addElements()
    {
        $fileInputName = $this->getFileInputName();

        //File Input
        $file = new \Zend\Form\Element\File($fileInputName);
        $file->setLabel('Select')
            ->setAttributes(array(
                'multiple' => true,
                'id' => $fileInputName, 
            )
        );
        $this->add($file);
        
        // Submit
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Upload',
                'id' => 'submitbutton',
                'class' => 'btn btn-default', 
            ),
        ));
    }

    /**
     * The neame of the file input id
     */
    public function getFileInputName()
    {
        $options = $this->getOptions();
        return ((isset($options['file_input_name']))
            ? $options['file_input_name']
            : 'file');
    }

    public function createInputFilter()
    {
        $options = $this->getOptions();
        $filterConfig = $options['file_input_filter'];
        $fileInputName = $this->getFileInputName();

        // File Input
        $file = new \Zend\InputFilter\FileInput($fileInputName);
        $file->setRequired(true);

        $filterChain = $file->getFilterChain();

        $filter = $filterChain->getPluginManager()->get($filterConfig['name'], $filterConfig['options']['target']);
        $allowedFilterOptions = array_intersect_key($filterConfig['options'], $filter->getOptions());
        $filter->setOptions($allowedFilterOptions);

        $filterChain->attach($filter);

        $file->getValidatorChain()->addByName('fileextension', array('extension' => 'jpg'));
        $file->getValidatorChain()->addByName('filemimetype',  array('mimeType'  => 'image/jpg,image/jpeg',));
        /*$file->getValidatorChain()->addByName(
            'filesize', array('min' => 200, 'max' => 204800)
        );*/

        $inputFilter = new \Zend\InputFilter\InputFilter();
        $inputFilter->add($file);
        return $inputFilter;
    }
}
