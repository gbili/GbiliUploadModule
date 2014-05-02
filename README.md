GbiliUploadModule
==============

Zf2 module. Provides uploading functionallity to your modules.

Installation
============

Require the module:

Using composer, merge these lines into your project's composer.json file:

    {
        "repositories": [
            {
                "type":"vcs",
                "url":"https://github.com/gbili/GbiliUploadModule"
            }
        ],
        "require": {
            "gbili/gbili-upload-module": "dev-master"
        },
        "autoload": {
            "psr-0": {
            }
        }
    }

Example Config
==============

in ``MyModule/config/module.config.php``:
    <?php
    return array(
        'file_uploader'   => include __DIR__ . '/file_uploader.config.php',
    );

in ``MyModule/config/file_uploader.config.php``:

    <?php
    return array(
        'mymodule_controller_identifier' => array(
            'alias' => 'ajax_media_upload', //using packaged aliased config
            'service' => array(
                'form_action_route_params' => array(
                    'route' => 'dogtore_dog_upload_route',
                    'params' => array(
                        'controller' => 'dogtore_dog_controller',
                        'action' => 'upload',
                    ),
                    'reuse_matched_params' => true,
                ),
            ),
            'controller_plugin' => array(
                'route_success' => array(
                    'route'                => 'dogtore_dog_add_route',
                    'params'               => array(),
                    'reuse_matched_params' => true,
                ),
            ),
            // Override the overall dog controller behaviour in
            // specific actions
            'action_override' => array(
                'viewmydog' => array( //tell uploader to set the form route to different than controller
                    'view_helper' => array(
                        //overrides the on success, to add medias to wall
                        'include_js_script_from_basename' => 'ajax_media_upload.js.phtml', 
                    ),
                    'service' => array(
                        'form_action_route_params' => array(
                            'route' => 'dogtore_dog_upload_my_dog_medias_route',
                            'params' => array(
                                'controller' => 'dogtore_dog_controller',
                                'action' => 'uploaddogmedias',
                            ),
                            // this will set the dogname_underscored which is 
                            // required to add each media to the proper dog
                            // inside the controller_plugin:post_upload_callback
                            'reuse_matched_params' => true,
                        ),
                    ),
                ),
                'uploadmydogmedias' => array(
                    'controller_plugin' => array(
                        // For each uploaded media, add it to the dog medias
                        'post_upload_callback' => function ($fileUploader, $controller) {
                            if (!$fileUploader->hasFiles()) {
                                return;
                            }

                            $medias = $controller->mediaEntityCreator($fileUploader->getFiles());
                            $em = $controller->em();

                            $dogname = $controller->routeParamTransform('dogname_underscored')->underscoreToSpace();

                            $dogs = $controller->repository()->findBy(array(
                                    'user' => $controller->identity(), 
                                    'name' => $dogname,
                                )
                            );

                            if (empty($dogs)) {
                                throw new \Exception('No such dog');
                            }

                            $dog = current($dogs);

                            $uploadedMedias = array();

                            foreach ($medias as $media) {
                                $dog->addMedia($media);

                                $uploadedMedias[] = array(
                                    'mediaId' => $media->getId(),
                                    'mediaSrc' => $media->getSrc(),
                                );
                            }
                            $em->persist($dog);
                            $em->flush();

                            return $uploadedMedias;
                        },
                    ),
                ),
            ),
        ),
    );

Customize this at will
