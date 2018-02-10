<?php
/**
 * Created by PhpStorm.
 * User: liam
 * Date: 31/03/2017
 * Time: 10:51
 */

namespace liamsorsby\CardNetHosted\Tests;

use liamsorsby\CardNetHosted\Api;
use liamsorsby\CardNetHosted\CardnetHostedGatewayFactory;

class CardnetHostedGatewayFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function shouldImplementCardnetHostedGatewayFactoryInterface()
    {
        $rc = new \ReflectionClass("liamsorsby\CardNetHosted\CardnetHostedGatewayFactory");
        $this->assertTrue($rc->implementsInterface("Payum\Core\GatewayFactoryInterface"));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CardnetHostedGatewayFactory();
    }

    /**
     * @test
     */
    public function shouldCreateCoreGatewayFactoryIfNotPassed()
    {
        $factory = new CardnetHostedGatewayFactory();
        $this->assertAttributeInstanceOf('Payum\Core\CoreGatewayFactory', 'coreGatewayFactory', $factory);
    }


    /**
     * @test
     */
    public function shouldUseCoreGatewayFactoryPassedAsSecondArgument()
    {
        $coreGatewayFactory = $this->getMockBuilder('Payum\Core\GatewayFactoryInterface')
            ->getMock();
        $factory = new CardnetHostedGatewayFactory(array(), $coreGatewayFactory);
        $this->assertAttributeSame($coreGatewayFactory, 'coreGatewayFactory', $factory);
    }


    /**
     * @test
     */
    public function shouldAllowCreateGateway()
    {
        $factory = new CardnetHostedGatewayFactory();
        $gateway = $factory->create(array('sandbox' => true, 'txntype' => 'sale', 'storename' => '1111', 'mode' => 'payonly', "shared_secret" => "123", "password" => "password"));
        $this->assertInstanceOf('Payum\Core\Gateway', $gateway);
        $this->assertAttributeNotEmpty('apis', $gateway);
        $this->assertAttributeNotEmpty('actions', $gateway);
        $extensions = $this->readAttribute($gateway, 'extensions');
        $this->assertAttributeNotEmpty('extensions', $extensions);
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayWithCustomApi()
    {
        $factory = new CardnetHostedGatewayFactory();
        $gateway = $factory->create(array('payum.api' => new \stdClass()));
        $this->assertInstanceOf('Payum\Core\Gateway', $gateway);
        $this->assertAttributeNotEmpty('apis', $gateway);
        $this->assertAttributeNotEmpty('actions', $gateway);
        $extensions = $this->readAttribute($gateway, 'extensions');
        $this->assertAttributeNotEmpty('extensions', $extensions);
    }
    /**
     * @test
     */
    public function shouldAllowCreateGatewayConfig()
    {
        $factory = new CardnetHostedGatewayFactory();
        $config = $factory->createConfig();
        $this->assertInternalType('array', $config);
        $this->assertNotEmpty($config);
    }
    /**
     * @test
     */
    public function shouldAddDefaultConfigPassedInConstructorWhileCreatingGatewayConfig()
    {
        $factory = new CardnetHostedGatewayFactory(array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
            'txntype' => '',
            'storename' => '',
            "shared_secret" => "",
            'mode' => ''
        ));
        $config = $factory->createConfig();
        $this->assertInternalType('array', $config);
        $this->assertArrayHasKey('foo', $config);
        $this->assertEquals('fooVal', $config['foo']);
        $this->assertArrayHasKey('bar', $config);
        $this->assertEquals('barVal', $config['bar']);
    }
    /**
     * @test
     */
    public function shouldConfigContainDefaultOptions()
    {
        $factory = new CardnetHostedGatewayFactory();
        $config = $factory->createConfig();
        $this->assertInternalType('array', $config);
        $this->assertArrayHasKey('payum.default_options', $config);
        $this->assertEquals(array('sandbox' => '', 'txntype' => '', 'storename' => '', 'mode' => '', "shared_secret" => "", "password" => ""), $config['payum.default_options']);
    }
    /**
     * @test
     */
    public function shouldConfigContainFactoryNameAndTitle()
    {
        $factory = new CardnetHostedGatewayFactory();
        $config = $factory->createConfig();
        $this->assertInternalType('array', $config);
        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertEquals('cardnethosted', $config['payum.factory_name']);
        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertEquals('cardnethosted', $config['payum.factory_title']);
    }
    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The txntype, storename, shared_secret, mode fields are required.
     */
    public function shouldThrowIfRequiredOptionsNotPassed()
    {
        $factory = new CardnetHostedGatewayFactory();
        $factory->create();
    }
    /**
     * @test
     */
    public function shouldConfigurePaths()
    {
        $factory = new CardnetHostedGatewayFactory();
        $config = $factory->createConfig();
        $this->assertInternalType('array', $config);
        $this->assertNotEmpty($config);
        $this->assertInternalType('array', $config['payum.paths']);
        $this->assertNotEmpty($config['payum.paths']);
        $this->assertArrayHasKey('PayumCore', $config['payum.paths']);
        $this->assertStringEndsWith('Resources/views', $config['payum.paths']['PayumCore']);
        $this->assertTrue(file_exists($config['payum.paths']['PayumCore']));
    }
}