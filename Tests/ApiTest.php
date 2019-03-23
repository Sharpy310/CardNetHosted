<?php
namespace liamsorsby\CardNetHosted\Tests;

use GuzzleHttp\Psr7\Response;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Payum\Core\HttpClientInterface;
use liamsorsby\CardNetHosted\Api;
use Psr\Http\Message\RequestInterface;


class ApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithHttpClientAndOptions()
    {
        new Api(array(
            "sandbox" => true,
            "txntype" => "test",
            "storename" => "name",
            "shared_secret" => "secret",
            "mode" => "mode",
            "password" => "password"
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());
    }

    /**
     * @test
     *
     * @expectedException Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The txntype fields are required.
     */
    public function throwIfTxnTypeOptionNotSetInConstructor()
    {
        new Api(array("sandbox" => true, "storename" => "name", "shared_secret" => "secret", "mode" => "mode", "password" => "password"), $this->createHttpClientMock(), $this->createHttpMessageFactory());
    }


    /**
     * @test
     *
     * @expectedException Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The storename fields are required.
     */
    public function throwIfStorenameOptionNotSetInConstructor()
    {
        new Api(array("sandbox" => true,"txntype" => "test", "shared_secret" => "secret", "mode" => "mode", "password" => "password"), $this->createHttpClientMock(), $this->createHttpMessageFactory());
    }


    /**
     * @test
     *
     * @expectedException Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The shared_secret fields are required.
     */
    public function throwIfSharedSecretOptionNotSetInConstructor()
    {
        new Api(array("sandbox" => true,"txntype" => "test", "storename" => "name", "mode" => "mode", "password" => "password"), $this->createHttpClientMock(), $this->createHttpMessageFactory());
    }

    /**
     * @test
     *
     * @expectedException Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The mode fields are required.
     */
    public function throwIfModeOptionNotSetInConstructor()
    {
        new Api(array("sandbox" => true, "txntype" => "test", "storename" => "name", "shared_secret" => "secret", "password" => "password"), $this->createHttpClientMock(), $this->createHttpMessageFactory());
    }


    /**
     * @test
     */
    public function shouldReturnSandboxIpnEndpointIfSandboxSetTrueInConstructor()
    {
        $api = new Api(array(
            "sandbox" => true,
            "txntype" => "test",
            "storename" => "name",
            "shared_secret" => "secret",
            "mode" => "mode",
            "password" => "password"
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());
        $this->assertEquals('https://test.ipg-online.com/connect/gateway/processing', $api->getApiEndpoint());
    }

    /**
     * @test
     */
    public function shouldReturnLiveIpnEndpointIfSandboxSetFalseInConstructor()
    {
        $api = new Api(array(
            'sandbox' => false,
            "txntype" => "test",
            "storename" => "name",
            "shared_secret" => "secret",
            "mode" => "mode",
            "password" => "password"
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());
        $this->assertEquals('https://www.ipg-online.com/connect/gateway/processing', $api->getApiEndpoint());
    }
    /**
     * @test
     *
     * @expectedException Payum\Core\Exception\Http\HttpException
     * @expectedExceptionMessage Client error response
     */
    public function throwIfResponseStatusNotOk()
    {
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function (RequestInterface $request) {
                return new Response(404);
            }))
        ;
        $api = new Api(array(
            'sandbox' => false,
            "txntype" => "test",
            "storename" => "name",
            "shared_secret" => "secret",
            "mode" => "mode",
            "password" => "password",
        ), $clientMock, $this->createHttpMessageFactory());
        $api->doCapture(array("chargetotal" => 0.00));
    }
    /**
     * @test
     */
    public function shouldProxyWholeNotificationToClientSend()
    {
        /** @var RequestInterface $actualRequest */
        $actualRequest = null;
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function (RequestInterface $request) use (&$actualRequest) {
                $actualRequest = $request;
                return new Response(200);
            }))
        ;
        $api = new Api(array(
            'sandbox' => false,
            "txntype" => "test",
            "storename" => "name",
            "shared_secret" => "secret",
            "mode" => "mode",
            "password" => "password"
        ), $clientMock, $this->createHttpMessageFactory());
        $expectedNotification = array(
            'foo' => 'foo',
            'bar' => 'baz',
            "chargetotal" => "0",
            "METHOD" => "DoCapture"
        );
        $api->doCapture($expectedNotification);
        $content = array();
        parse_str($actualRequest->getBody()->getContents(), $content);
        $this->assertInstanceOf('Psr\Http\Message\RequestInterface', $actualRequest);
        $this->assertEquals($expectedNotification, $content);
        $this->assertEquals($api->getApiEndpoint(), $actualRequest->getUri());
        $this->assertEquals('POST', $actualRequest->getMethod());
    }
    /**
     * @test
     */
    public function shouldReturnInstanceOfResponse()
    {
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function (RequestInterface $request) {
                return new Response(200, array(), Api::NOTIFY_VERIFIED);
            }))
        ;
        $api = new Api(array(
            'sandbox' => false,
            "txntype" => "test",
            "storename" => "name",
            "shared_secret" => "secret",
            "mode" => "mode",
            "password" => "password"
        ), $clientMock, $this->createHttpMessageFactory());
        $this->assertTrue(is_array($api->doCapture(array("chargetotal" => "0"))));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpClientInterface
     */
    protected function createHttpClientMock()
    {
        return $this->getMockBuilder('Payum\Core\HttpClientInterface', array('send'))->getMock();
    }
    /**
     * @return \Http\Message\MessageFactory
     */
    protected function createHttpMessageFactory()
    {
        return new GuzzleMessageFactory();
    }
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpClientInterface
     */
    protected function createSuccessHttpClientStub()
    {
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->any())
            ->method('send')
            ->will($this->returnCallback(function (RequestInterface $request) {
                return new Response(200);
            }))
        ;
        return $clientMock;
    }


}