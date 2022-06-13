<?php

$loader->import('security.php');

$container->loadFromExtension(
    'security',
    [
        'encoders' => [
            'Symfony\Component\Security\Core\User\User' => [
                'algorithm'  => 'md5',
  //              'encode_as_base64' => false,
  //              'iterations' => 0
            ],
            'Mautic\UserBundle\Entity\User' => [
                'algorithm'  => 'md5',
  //              'encode_as_base64' => false,
  //              'iterations' => 0
            ],
        ],
    ]
);
