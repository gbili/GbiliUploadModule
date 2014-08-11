<?php
namespace GbiliUploadModule\Form;

class Html5MultiUpload extends \Zend\Form\Form
{
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);
        $this->addElements();
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
}
