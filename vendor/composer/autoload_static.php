<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit12d54a8ab178928493f11f6b4cd8446f
{
    public static $prefixLengthsPsr4 = array (
        't' => 
        array (
            'think\\composer\\' => 15,
        ),
        'a' => 
        array (
            'app\\' => 4,
        ),
        'I' => 
        array (
            'Imagine\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'think\\composer\\' => 
        array (
            0 => __DIR__ . '/..' . '/topthink/think-installer/src',
        ),
        'app\\' => 
        array (
            0 => __DIR__ . '/../..' . '/application',
        ),
        'Imagine\\' => 
        array (
            0 => __DIR__ . '/..' . '/imagine/imagine/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit12d54a8ab178928493f11f6b4cd8446f::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit12d54a8ab178928493f11f6b4cd8446f::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}