<?php
namespace GbiliUploadModule\Service;

class Uploader 
{
    /**
     * These have to match up the js script
     */
    const UPLOAD_STATUS_ALL_SUCCESS = 1;
    const UPLOAD_STATUS_ALL_FAIL = 0;
    const UPLOAD_STATUS_PARTIAL_SUCCESS = 2;

    /**
     * Points to a php file containing
     * javascript code to override ajax.file_upload.js.phtml
     * TODO Weird
     *
     * @var string
     */
    protected $includeScriptFilePath;

    /**
     * @var array
     */
    protected $formActionRouteParams = array('route' => null, 'params' => array(), 'reuse_matched_params' => true);

    /**
     *
     * @var \GbiliUploadModule\FileHydratorInterface
     */
    protected $hydrator;

    /**
     * TODO this belongs to view helper
     * is the form going to be displayed as a popup
     * or integrated in the content?
     */
    protected $isFormDisplayedAsPopup = false;

    /**
     * TODO this belongs to view helper
     * is the form going to be displayed as a popup
     * or integrated in the content?
     */
    protected $isFormInitialStateHidden = true;

    /**
     *
     * @var \Zend\Http\Request
     */
    protected $request;

    /**
     * @var StdObject array
     */
    protected $postData;

    /**
     * @var string
     */
    protected $fileInputName;

    /**
     * @var string
     */
    protected $formName;

    /**
     * @var string
     */
    protected $formId;

    /**
     * @var array
     */
    protected $messages;

    /**
     * @var array
     */
    protected $files = array();

    /**
     * @var \GbiliUploadModule\Form\Html5MultiUpload
     */
    protected $form;

    /**
     * @var \GbiliUploadModule\Form\Html5MultiUpload
     */
    protected $clonableForm;

    /**
     * Conains either the flag for : all success, partial success, all fail
     * @var integer
     */
    protected $uploadStatus;

    public function getFormCopy()
    {
        if (null === $this->clonableForm) {
            //Popopulate clonableForm
            $this->getForm();
        }
        return clone $this->clonableForm;
    }

    /**
     * This is very weird but... It allows overriding the
     * ajax.file_upload.js.phtml script vars: uploadSuccess
     * uploadFail. You can use this to set different  
     * behaviors on success or on fail
     * TODO Change this to javascript only solution? 
     */
    public function setIncludeScriptFilePath($filePath)
    {
        if (!is_file($filePath)) {
            throw new \Exception('File is not reachable: ' . print_r($filePath, true));
        }
        $this->includeScriptFilePath = $filePath;
        return $this;
    }

    public function hasIncludeScriptFilePath()
    {
        return null !== $this->includeScriptFilePath;
    }

    public function getIncludeScriptFilePath()
    {
        return $this->includeScriptFilePath;
    }

    public function setForm($form)
    {
        if (!($form instanceof \GbiliUploadModule\Form\Html5MultiUpload)) {
            throw new \Exception('Form type not supported, must extend Html5MultiUpload');
        }
        $this->form = $form;
        return $this;
    }

    public function getForm()
    {
        if (null !== $this->form) {
            return $this->form;
        }

        $options = array();
        if (!$this->hasFileInputName()) {
            $this->setFileInputName('file_input');
        }
        $options['file_input_name'] = $this->getFileInputName();

        if (!$this->hasFormName()) {
            $this->setFormName('file_form');
        }

        if (!$this->hasFormId()) {
            $this->setFormId('gbiliuploader_upload_form');
        }

        $form = new \GbiliUploadModule\Form\Html5MultiUpload($this->getFormName(), $options);
        $form->setAttribute('id', $this->getFormId());

        $this->clonableForm = clone $form;
        $this->setForm($form);

        return $form;
    }

    public function setFormActionRouteParams(array $routeParams)
    {
        if (isset($routeParams['route'])) {
            $this->formActionRouteParams['route'] = $routeParams['route'];
        }
        if (isset($routeParams['params'])) {
            $this->formActionRouteParams['params'] = $routeParams['params'];
        }
        if (isset($routeParams['reuse_matched_params'])) {
            $this->formActionRouteParams['reuse_matched_params'] = $routeParams['reuse_matched_params'];
        }
        return $this;
    }

    public function getFormActionRouteParams()
    {
        return $this->formActionRouteParams;
    }

    public function getFiles()
    {
        if (empty($this->files)) {
            throw new \Exception('No file was successfully uploaded, check what went wrong in getMessages()');
        }
        return $this->files;
    }

    public function hasFiles()
    {   
        if (!$this->hasMessages()) {
            throw new \Exception('Make sure tu call uploadFiles() before getFiles()');
        }
        return !empty($this->files);
    }

    protected function addFile($file)
    {
        $this->files[] = $file;
        return $this;
    }

    public function setFormName($name)
    {
        $this->formName = $name;
        return $this;
    }

    public function hasFormName()
    {
        return null !== $this->formName;
    }

    public function getFormName()
    {
        return $this->formName;
    }

    public function setFormId($id)
    {
        $this->formId = $id;
        return $this;
    }

    public function hasFormId()
    {
        return null !== $this->formId;
    }

    public function getFormId()
    {
        return $this->formId;
    }

    public function setEntityManager(\Doctrine\ORM\EntityManager $em)
    {
        $this->objectManager = $em;
        return $this;
    }

    public function getEntityManager()
    {
        if (null === $this->objectManager) {
            throw new \Exception('Doctrine Object Manager not set');
        }
        return $this->objectManager;
    }

    public function setMessages(array $messages)
    {
        $this->messages = $messages;
        return $this;
    }

    public function hasMessages()
    {
        return null !== $this->messages;
    }

    public function getMessages()
    {
        if (!$this->hasMessages()) {
            throw new \Exception('It seems that you have not called uploadFiles');
        }
        return $this->messages;
    }

    public function setFileInputName($name)
    {
        $this->fileInputName = $name;
        return $this;
    }

    public function hasFileInputName()
    {
        return null !== $this->fileInputName;
    }

    public function getFileInputName()
    {
        if (!$this->hasFileInputName()) {
            throw new \Exception('File input name not set');
        }
        return $this->fileInputName;
    }

    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getPostData()
    {
        if (null === $this->postData) {
            $this->postData = array_merge_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
        }
        return $this->postData;
    }

    public function areAllFilesUploaded()
    {
        return $this->getUploadStatus() === self::UPLOAD_STATUS_ALL_SUCCESS;
    }

    public function getUploadStatus()
    {
        if (null !== $this->uploadStatus) {
            return $this->uploadStatus;
        }

        $atLeastOneSuccess = false;
        $atLeastOneFail    = false;
        foreach ($this->getMessages() as $message) {
            if ($this->isFileUploaded($message)) {
                $atLeastOneSuccess = true; 
            } else {
                $atLeastOneFail = true; 
            }
        }

        if ($atLeastOneSuccess && $atLeastOneFail) {
            $uploadStatus = self::UPLOAD_STATUS_PARTIAL_SUCCESS;
        } else if ($atLeastOneSuccess) {
            $uploadStatus = self::UPLOAD_STATUS_ALL_SUCCESS;
        } else {
            $uploadStatus = self::UPLOAD_STATUS_ALL_FAIL;
        }
        $this->uploadStatus = $uploadStatus;

        return $uploadStatus;
    }

    /**
     * If request is ajax the Js Script will send a
     * request with a isAjax variable set to 1
     * You should find the Js Script in partial/file_upload.phtml
     */
    public function isAjax()
    {
        $postData = $this->getPostData();
        return !empty($postData['isAjax']);
    } 

    public function isFileUploaded($message)
    {
        return isset($message['class']) && $message['class'] === 'success';
    }

    public function uploadFiles()
    {
        $formName      = $this->getFormName();
        $fileInputName = $this->getFileInputName();
        $data          = $this->getPostData();

        $messages      = array();
        $filesPostData = isset($data[$fileInputName])
            ? $data[$fileInputName] 
            : array();

        if (empty($filesPostData)) {
            $messages[] = array(
                'class' => 'danger',
                'message' => 'Your browser does not support sending this file. Try removing "[" or "]" in file name.',
            );
        }

        foreach ($filesPostData as $fileData) {
            $message = array();
            $singleData = array($fileInputName => $fileData);
            $fileName = $fileData['name'];
            $singleFileFormData = $data;
            $singleFileFormData[$fileInputName] = $fileData;

            $message = $this->uploadOneFile($singleFileFormData);
            $message['fileName'] = $fileName;

            $messages[] = $message;
        }
        $this->setMessages($messages);
        return $this;
    }

    public function uploadOneFile(array $singleFileFormData)
    {
        $form = $this->getFormCopy();
        $form->setData($singleFileFormData);
        if (!$form->isValid()) {
            $messages = $form->getMessages();
            $message = implode('. ', $messages[$this->getFileInputName()]);
            return array(
                'class' => 'danger',
                'message' => $message,
            );
        }
        //array('otherinput'=>'inputval', 
        //      'file' => array(
        //           name=>filename.jpg, 
        //           tmp_upload=>'/something/asdfasdf/LKGO'
        //           ...
        //       )
        //       otherinput ...
        //);
        $formData = $form->getData();
        
        //array(
        //    name=>filename.jpg,
        //    tmp_upload=>'/something/asdfasdf/LKGO'
        //    ...
        // );
        $fileData = $formData[$this->getFileInputName()];
        $this->saveFile($fileData);
        return array(
            'class' => 'success', 
            'message' => 'File Uploaded',
        );
    }

    public function saveFile(array $fileData)
    {
        $file = $this->getFileHydrator()->getHydratedFile($fileData);

        $this->persistFile($file);

        $this->addFile($file);
    }

    public function displayFormAsPopup($bool)
    {
        $this->isFormDisplayedAsPopup = (boolean) $bool;
        return $this;
    }

    public function isFormDisplayedAsPopup()
    {
        return $this->isFormDisplayedAsPopup;
    }

    public function setFormInitialStateHidden($bool)
    {
        $this->isFormInitialStateHidden = (boolean) $bool;
        return $this;
    }

    public function isFormInitialStateHidden()
    {
        return $this->isFormInitialStateHidden;
    }

    /**
     * Form data is passed to $hydrater->getHydratedFile($formData)
     * the method needs to return a doctrine file entity
     */
    public function setFileHydrator(\GbiliUploadModule\FileHydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;
        return $this;
    }

    public function getFileHydrator()
    {
        if (null === $this->hydrator) {
            throw new \Exception('Call setFileHydrator($hydrator), before starting to upload');
        }
        return $this->hydrator;
    }

    public function persistFile($file)
    {
        $this->getEntityManager()->persist($file);
        $this->getEntityManager()->flush();
    }
}
