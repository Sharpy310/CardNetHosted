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
}