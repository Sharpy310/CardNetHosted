<?php
namespace liamsorsby\CardNetHosted\Action;

use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
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
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;

class CaptureAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface,  GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait;
    use ApiAwareTrait;
    use GenericTokenFactoryAwareTrait;

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

        $httpRequest = new GetHttpRequest();
        $this->gateway->execute($httpRequest);

        if (isset($httpRequest->request["status"])) {
            $model->replace($httpRequest->request);
        } else {

            $notifyToken = $this->tokenFactory->createNotifyToken(
                $request->getToken()->getGatewayName(),
                $request->getToken()->getDetails()
            );
            $model["notify_token"] = $notifyToken->getHash();
            $model["payum_token"] = $request->getToken()->getHash();
            $model['transactionNotificationURL'] = $notifyToken->getTargetUrl();
            throw new HttpPostRedirect(
                $this->api->getApiEndpoint(),
                $this->api->addAuthorizeFields($model->toUnsafeArray())
            );
        }
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
