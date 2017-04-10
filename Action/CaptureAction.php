<?php
namespace liamsorsby\CardNetHosted\Action;

use liamsorsby\CardNetHosted\Request\Api\DoCapture;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\UnsupportedApiException;
use liamsorsby\CardNetHosted\Api;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Reply\HttpPostRedirect;

class CaptureAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{

    use GatewayAwareTrait;

    /**
     * @var Api
     */
    protected $api;
    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false === $api instanceof Api) {
            throw new UnsupportedApiException('Not supported.');
        }
        $this->api = $api;
    }

    /**
     * @param GatewayInterface $gateway
     */
    public function setGateway(GatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        $request->getToken();

        throw new HttpPostRedirect(
            $this->api->getApiEndpoint(),
            $this->api->addAuthorizeFields($model->toUnsafeArray())
        );
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
