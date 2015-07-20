<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Silex\Application;
use Silex\Provider\ServiceControllerServiceProvider;
use DerAlex\Silex\YamlConfigServiceProvider;
use App\Services\Database;

$app = new Application();

$app
    ->register(new ServiceControllerServiceProvider())
    ->register(new YamlConfigServiceProvider(__DIR__ . '/config/config.yml'));

$database = new Database($app['config']);
$app['connection'] = $database->getCapsule()->getConnection('default');

/**
 * @param $app
 * @param $shortName
 * @return string
 */
function controller($app, $shortName)
{
    list($shortClass, $shortMethod) = explode(':', $shortName, 2);
    $className = sprintf('App\Controller\%sController', ucfirst($shortClass));
    if (!isset($app[$shortClass .'.controller'])) {
        $app[$shortClass .'.controller'] = $app->share(function() use ($app, $className) {
            return new $className($app);
        });
    }
    return $shortClass .'.controller:' . $shortMethod . 'Action';
}

return $app;