<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit66d076565999ea2962e3ed32a66ae37c
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'Myegiotestimonials\\' => 19,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Myegiotestimonials\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit66d076565999ea2962e3ed32a66ae37c::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit66d076565999ea2962e3ed32a66ae37c::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit66d076565999ea2962e3ed32a66ae37c::$classMap;

        }, null, ClassLoader::class);
    }
}
