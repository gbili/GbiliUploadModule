<?php
namespace Upload;

return array(
    'controller_plugins' => include __DIR__ . '/controller_plugins.config.php',
    'doctrine'           => include __DIR__ . '/doctrine.config.php',
    'file_uploader'      => include __DIR__ . '/file_uploader.config.php',
    'service_manager'    => include __DIR__ . '/service_manager.config.php',
    'view_helpers'       => include __DIR__ . '/view_helpers.config.php',
);
