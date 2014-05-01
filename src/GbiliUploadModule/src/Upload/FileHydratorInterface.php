<?php
namespace Upload;

interface FileHydratorInterface
{
    /**
     * Form data is passed to $hydrater->getHydratedFile($formData)
     * the method needs to return a doctrine file entity
     */
    public function getHydratedFile(array $fileData);
}
