<?php

use Di\Configuration;
use Di\Manager;

class DiTests extends PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $m = new Manager;
        $this->assertInstanceOf('Common\Module', $m->get('Common\Module'));
        $this->assertNotSame($m->get('Common\Module'), $m->create('Common\Module'));
    }

    public function testInstanceConfiguration()
    {
        $configuration = new Configuration(array(
            'Common\Module' => array(
                'state' => 'active'
            )
        ));

        $m = new Manager($configuration);

        $this->assertSame($m->get('Common\Module')->getState(), 'active');
        $this->assertSame($m->create('Common\Module')->getState(), 'active');

        // change configuration
        $configuration->set('Common\Module', 'state', 'disabled');
        $this->assertSame($m->create('Common\Module')->getState(), 'disabled');

        // override configuration
        $instance = $m->create('Common\Module', array(
            'state' => 'unknown'
        ));
        $this->assertSame($instance->getState(), 'unknown');

        // define new class configuration
        $configuration->set('Common\ClassWithConstructor', 'property', 'tra-ta-ta');
        $this->assertSame($m->create('Common\ClassWithConstructor')->property, 'tra-ta-ta');

        // get value from configuration
        $value = $configuration->get('Common\ClassWithConstructor', 'emptyProperty', 'defaultValue');
        $this->assertSame($value, 'defaultValue');

        // merge configuration
        $configuration->merge(array(
            'Common\Application' => array(
                'k' => 'v'
            ),
            'Common\ClassWithConstructor' => array(
                'emptyProperty' => 'concreteValue',
                'property' => 'piu-piu'
            )
        ));
        $this->assertSame($configuration->get('Common\ClassWithConstructor', 'property'), 'piu-piu');
    }

    public function testPropertyInjection() 
    {
        $m = new Manager;
        $module = $m->get('Common\Application')->getModule();
        $this->assertSame($module, $m->get('Common\Module'));
    }

    public function testMethodInjection()
    {
        $m = new Manager;

        $this->assertSame(
            $m->call('Common\Application', 'extractModuleFromManager'), 
            $m->get('Common\Module')
        );

        // numeric array arguments
        $this->assertSame($m->call('Common\Application', 'greet', array('Dmitry')), 'Hello, Dmitry');

        // associative arguments
        $this->assertSame($m->call('Common\Application', 'greet', array('name' => 'Dmitry')), 'Hello, Dmitry');
    }

    public function testMethodParamNotFoundException()
    {
        $this->setExpectedException('Exception');
        $m = new Manager;
        $m->call('Common\Application', 'greet');
    }

    public function testConstructorInjection()
    {
        $m = new Manager();
        $this->assertSame($m->get('Common\ClassWithConstructor')->getApplication(), $m->get('Common\Application'));
    }

    public function testInstanceRegistration()
    {
        $m = new Manager;
        $myApplication = new Common\Application;
        $m->register($myApplication);

        $this->assertSame($m->get('Common\Application'), $myApplication);
    }

    public function testRegistrationConflictException()
    {
        $this->setExpectedException('Exception');

        $myApplication = new Common\Application;

        $m = new Manager;
        $m->register($myApplication);
        $m->register($myApplication);
    }

    public function testNoClassException()
    {
        $this->setExpectedException('Exception');
        $m = new Manager;
        $m->get('Unknown_Class');
    }

    public function testEmptyClass()
    {
        $this->setExpectedException('Exception');
        $m = new Manager;
        $m->get('');
    }
}