<?php
namespace Cti\Storage;

use Build\Application;
use Cti\Core\Application\Bootloader;
use Cti\Core\Module\Console;
use Cti\Core\Module\Project;
use Cti\Di\Manager;
use Cti\Di\Reflection;

class Storage extends Project implements Bootloader
{
    /**
     * @inject
     * @var \Cti\Storage\Schema
     */
    protected $schema;

    /**
     * @var \Storage\Master
     */
    protected $master;

    /**
     * @inject
     * @var \Cti\Di\Manager
     */
    protected $manager;

    /**
     * @inject
     * @var \Cti\Storage\Adapter\DBAL
     */
    protected $adapter;

    public function init(\Cti\Core\Module\Cache $cache)
    {
        parent::init($cache);
        $this->path = dirname(dirname(__DIR__));
    }

    /**
     * @return \Storage\Master
     */
    public function getMaster()
    {
        if (!$this->master) {
            $this->master = $this->manager->get('Storage\\Master');
        }
        return $this->master;
    }

    /**
     * @return Schema
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * @return Adapter\DBAL
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * bootstrap application
     * @param Application $application
     * @return mixed
     */
    public function boot(Application $application)
    {
        $initializer = $application->getManager()->getInitializer();
        $initializer->before('Cti\Core\Module\Console', array($this, 'registerCommands'));
    }

    public function registerCommands(Console $console, Manager $manager)
    {
        foreach($this->getClasses('Command') as $class) {
            $console->add($manager->get($class));
        }
    }

    /**
     * @return array
     */
    protected function getAvailableNamespaces()
    {
        return array('Command');
    }
}