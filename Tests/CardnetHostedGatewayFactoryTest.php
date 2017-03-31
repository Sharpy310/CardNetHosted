<?php
/**
 * Created by PhpStorm.
 * User: liam
 * Date: 31/03/2017
 * Time: 10:51
 */

namespace liamsorsby\CardNetHosted\Tests;

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
        $coreGatewayFactory = $this->getMock('Payum\Core\GatewayFactoryInterface');
        $factory = new CardnetHostedGatewayFactory(array(), $coreGatewayFactory);
        $this->assertAttributeSame($coreGatewayFactory, 'coreGatewayFactory', $factory);
    }


    /**
     * @test
     */
    public function shouldAllowCreateGateway()
    {
        $factory = new CardnetHostedGatewayFactory();
        $gateway = $factory->create(array('publishable_key' => 'aPubKey', 'secret_key' => 'aSecretKey'));
        $this->assertInstanceOf('Payum\Core\Gateway', $gateway);
        $this->assertAttributeNotEmpty('apis', $gateway);
        $this->assertAttributeNotEmpty('actions', $gateway);
        $extensions = $this->readAttribute($gateway, 'extensions');
        $this->assertAttributeNotEmpty('extensions', $extensions);
    }
}