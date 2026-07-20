<?php return array(
    'root' => array(
        'name' => 'his/web',
        'pretty_version' => 'dev-main',
        'version' => 'dev-main',
        'reference' => 'd35c443efa598a2ae4fc781b716fadfc81c12fd6',
        'type' => 'library',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'his/web' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'reference' => 'd35c443efa598a2ae4fc781b716fadfc81c12fd6',
            'type' => 'library',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'monolog/monolog' => array(
            'pretty_version' => '2.2.0',
            'version' => '2.2.0.0',
            'reference' => '1cb1cde8e8dd0f70cc0fe51354a59acad9302084',
            'type' => 'library',
            'install_path' => __DIR__ . '/../monolog/monolog',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'psr/log' => array(
            'pretty_version' => '1.1.3',
            'version' => '1.1.3.0',
            'reference' => '0f73288fd15629204f9d42b7055f72dacbe811fc',
            'type' => 'library',
            'install_path' => __DIR__ . '/../psr/log',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'psr/log-implementation' => array(
            'dev_requirement' => false,
            'provided' => array(
                0 => '1.0.0',
            ),
        ),
    ),
);
