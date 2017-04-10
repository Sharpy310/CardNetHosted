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

        if ($model["response_code_3dsecure"] == 1) {
            $request->markCaptured();
            return;
        }
        if ($model["response_code_3dsecure"] == 2) {
            $request->markCaptured();
            return;
        }
        if ($model["processor_response_code"] == 00 || $model["processor_response_code"] == 4000) {
            $request->markCaptured();
            return;
        }
        if ($model["response_code_3dsecure"] == 3) {
            $request->markFailed();
            return;
        }
        if ($model["response_code_3dsecure"] == 4) {
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
