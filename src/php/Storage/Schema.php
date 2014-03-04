<?php

namespace Storage;

use Storage\Component\Model;
use Storage\Component\Link;
use Symfony\Component\Finder\Finder;
use Util\String;

class Schema
{
    /**
     * @inject
     * @var Di\Manager 
     */
    protected $manager;

    /**
     * @inject
     * @var Application\Locator
     */
    protected $locator;

    function init($dump = null)
    {
        if($dump) {
            $this->loadDump($dump);

        } else {
            $this->processMigrations();
            
        }
    }

    public function createModel($name, $comment, $properties = array())
    {
        return $this->models[$name] = $this->manager->create('Storage\Component\Model', array(
            'name' => $name, 
            'comment' => $comment, 
            'properties' => $properties
        ));
    }

    public function getModel($name)
    {
        return $this->models[$name];
    }

    public function getModels()
    {
        return array_values($this->models);
    }

    public function createLink($list)
    {
        if($list instanceof Model) {
            $list = func_get_args();
        }

        $mapping = array();
        $name = array();
        if(count($list) != 2) {
            throw new \Exception("Link must contain 2 models");
        }

        $start = $end = array();

        foreach($list as $k => $v) {
            if(is_numeric($k)) {
                $k = $v->name;
            }
            if($k == $v->name) {
                $start[] = $k;
            } else {
                $end[] = $k;
            }
            $mapping[$k] = $v;
        }
        sort($start);
        sort($end);

        $name = $start;
        foreach($end as $v) {
            $name[] = $v;
        }
        $name[] = 'link';
        $name = implode('_', $name);

        $this->models[$name] = $this->manager->create('Storage\Component\Model', array('name' => $name, 'comment' => $name));
        $this->models[$name]->createBehaviour('link', array(
            'list' => $list
        ));

        foreach($list as $model) {
            if($model->hasBehaviour('log') && !$this->models[$name]->hasBehaviour('log')) {
                $this->models[$name]->createBehaviour('log');
            }
            $this->models[$name]->hasOne($model);
        }

        return $this->models[$name];
    }

    public function restore()
    {
        $dump = \Generated\Storage::getDump();
        foreach ($dump['models'] as $key => $data) {
            $this->models[$key] = Model::restore($this, $data);
        }
        foreach ($dump['links'] as $key => $data) {
            $this->links[$key] = Link::restore($this, $data);
        }
    }

    function processMigrations()
    {
        $finder = new Finder();

        $finder
            ->files()
            ->name("*.php")
            ->in($this->locator->path('resources php migrations'));

        foreach($finder as $file) {

            $date = substr($file->getFileName(), 0, 8);
            $time = substr($file->getFileName(), 9, 6);
            $index = substr($file->getBasename('.php'), 16);
            $name = String::convertToCamelCase($index);

            $class = 'Migration\\' . $name . '_' . $date . '_' . $time;

            if(!class_exists($class, false)) {
                include $file->getPathname();
            }

            $this->manager->get($class)->process($this);
        }        
    }

    function getDump()
    {
        return 'dump for schema';
    }

    function loadDump($dump)
    {
    }
}