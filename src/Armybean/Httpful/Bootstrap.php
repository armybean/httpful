<?php

namespace Armybean\Httpful;

use Armybean\Httpful\Handlers\CsvHandler;
use Armybean\Httpful\Handlers\FormHandler;
use Armybean\Httpful\Handlers\JsonHandler;
use Armybean\Httpful\Handlers\XmlHandler;

/**
 * Bootstrap class that facilitates autoloading. A naive PSR-0 autoloader.
 *
 * @author Nate Good <me@nategood.com>
 */
class Bootstrap {

    const DIR_GLUE = DIRECTORY_SEPARATOR;
    const NS_GLUE  = '\\';

    public static $registered = false;

    /**
     * Register the autoloader and any other setup needed
     */
    public static function init()
    {
        spl_autoload_register(['\Armybean\Httpful\Bootstrap', 'autoload']);
        self::registerHandlers();
    }

    /**
     * Register default mime handlers.  Is idempotent.
     */
    public static function registerHandlers()
    {
        if (self::$registered === true)
        {
            return;
        }

        // @todo check a conf file to load from that instead of hardcoding into the library?
        $handlers = [
            Mime::JSON => new JsonHandler(),
            Mime::XML  => new XmlHandler(),
            Mime::FORM => new FormHandler(),
            Mime::CSV  => new CsvHandler(),
        ];

        foreach ($handlers as $mime => $handler)
        {
            // Don't overwrite if the handler has already been registered
            if (Httpful::hasParserRegistered($mime))
            {
                continue;
            }
            Httpful::register($mime, $handler);
        }

        self::$registered = true;
    }

    /**
     * The autoload magic (PSR-0 style)
     *
     * @param string $classname
     */
    public static function autoload($classname)
    {
        self::_autoload(dirname(dirname(__FILE__)), $classname);
    }

    /**
     * @param string $base
     * @param string $classname
     */
    private static function _autoload($base, $classname)
    {
        $parts = explode(self::NS_GLUE, $classname);
        $path = $base . self::DIR_GLUE . implode(self::DIR_GLUE, $parts) . '.php';

        if (file_exists($path))
        {
            require_once($path);
        }
    }

    /**
     * Register the autoloader and any other setup needed
     */
    public static function pharInit()
    {
        spl_autoload_register(['\Armybean\Httpful\Bootstrap', 'pharAutoload']);
        self::registerHandlers();
    }

    /**
     * Phar specific autoloader
     *
     * @param string $classname
     */
    public static function pharAutoload($classname)
    {
        self::_autoload('phar://httpful.phar', $classname);
    }
}
