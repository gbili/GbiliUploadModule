<?php
/**
 * Copyright 2013 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 * http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

namespace GbiliUploadModule\View\Helper;

/**
 * Factory used to instantiate a S3 link view helper
 */
class UploaderFactory implements \Zend\ServiceManager\FactoryInterface
{
    /**
     * {@inheritDoc}
     * @return S3Link
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $viewHelperPluginManager)
    {
        $serviceManager = $viewHelperPluginManager->getServiceLocator();
        $config = $serviceManager->get('Config');
        $viewHelperConfig = $config['file_uploader'][''];

        return new S3Link($s3Client);
    }
}
