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

    /**
     * Used by filter
     * @throws exception
     * @return string directory where files should be moved to
     */
    public function getTarget()
    {
        $options = $this->getOptions();
        $target = null;
        if (isset($options['rename_upload_target'])) {
            $target = $options['rename_upload_target'];
        } else if (isset($options['file_upload_dirpath'])) {
            $target = $options['file_upload_dirpath'] . '/media.jpg';
        } else {
            throw new \Exception('Missing target');
        }
        return $target;
    }

    /**
     * Filter identifier in the filter manager
     * @return string filter name 
     */
    public function getFileInputFilterName() 
    {
        $options = $this->getOptions();
        return ((isset($options['file_input_filter_name']))
            ? $options['file_input_filter_name']
            : 'filerenameupload');
    }

    public function createInputFilter()
    {
        $options       = $this->getOptions();
        $target        = $this->getTarget();
        $fileInputFilterName    = $this->getFileInputFilterName();
        $fileInputName = $this->getFileInputName();

        $basicFilterOptions = array(
            'target'    => $target,
            'randomize' => true,
        );

        $filterOptions = (isset($options['file_input_filter_options']))
            ? array_merge($basicFilterOptions, $options['file_input_filter_options'])
            : $basicFilterOptions;

        // File Input
        $file = new \Zend\InputFilter\FileInput($fileInputName);
        $file->setRequired(true);
        $file->getFilterChain()->attachByName($fileInputFilterName, $filterOptions);
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
