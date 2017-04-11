<?php
namespace liamsorsby\CardNetHosted\Action;

use liamsorsby\CardNetHosted\Constants;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model["status"] == "APPROVED") {
            $request->markCaptured();
            return;
        }
        if ($model["status"] == "FAILED") {
            $request->markFailed();
            return;
        }
        if ($model["status"] == "DECLINED") {
            $request->markFailed();
            return;
        }

        $request->markUnknown();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
