<?php

namespace App\Services;

use Illuminate\Database\Capsule\Manager as Capsule;

class Database {

    /**
     * @var array
     */
    private $configDatabase;

    /**
     * @var Capsule
     */
    private $capsule;

    /**
     * @param $config
     */
    public function __construct($config) {
        $this->configDatabase = $config['database'];
        $this->initCapsule();
    }

    /**
     * Init capsule
     */
    public function initCapsule() {
        $this->capsule = new Capsule();
        switch($this->configDatabase['driver']) {
            case 'mysql':
                $configConnection = [
                    'driver'    => 'mysql',
                    'host'      => $this->configDatabase['host'],
                    'database'  => $this->configDatabase['database'],
                    'username'  => $this->configDatabase['username'],
                    'password'  => $this->configDatabase['password'],
                    'charset'  => $this->configDatabase['charset'],
                    'collation'  => $this->configDatabase['collation']
                ];
                if (isset($this->configDatabase['prefix'])) {
                    $configConnection['prefix'] = $this->configDatabase['prefix'];
                }
                $this->capsule->addConnection($configConnection);
                break;
        }

        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();
    }

    /**
     * @return Capsule
     */
    public function getCapsule() {
        return $this->capsule;
    }

}