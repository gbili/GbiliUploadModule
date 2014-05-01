<?php
namespace Upload;

return array(
    'ajax_media_upload' => array(
        'view_helper' => array(
            //overrides the on success 
            'include_js_script'          => realpath(__DIR__ . '/../../Upload/view/partial') . '/image_picker_aware_media_upload.js.phtml', 
            'display_form_as_popup'      => true,
            'popup_initial_state_hidden' => true,
        ),
        'service' => array(
            'file_hydrator' => 'blogUploadFileHydrator',
            'form_action_route_params' => array(
                'route' => 'blog_media_route',
                'params' => array(
                    'controller' => 'blog_media_controller',
                    'action' => 'upload',
                ),
                'reuse_matched_params' => true,
            ),
        ),

        'controller_plugin' => array(
            'route_success' => array(
                'route'  => 'blog_media_route',
                'params' => array(
                    'action' => 'index'
                ),
                'reuse_matched_params' => true,
            ),

            /**
             * Create medias with the uploaded files
             */
            'post_upload_callback' => function ($fileUploader, $controller) {
                if (!$fileUploader->hasFiles()) {
                    return;
                }

                $medias = $controller->mediaEntityCreator($fileUploader->getFiles());
                $uploadedMedias = array();
                foreach ($medias as $media) {
                    $uploadedMedias[] = array(
                        'mediaId' => $media->getId(),
                        'mediaSrc' => $media->getSrc(),
                    );
                }
                return $uploadedMedias;
            },
        ),
    ),
);
