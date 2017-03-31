<?php
namespace liamsorsby\CardNetHosted;

use liamsorsby\CardNetHosted\Action\AuthorizeAction;
use liamsorsby\CardNetHosted\Action\CancelAction;
use liamsorsby\CardNetHosted\Action\ConvertPaymentAction;
use liamsorsby\CardNetHosted\Action\CaptureAction;
use liamsorsby\CardNetHosted\Action\NotifyAction;
use liamsorsby\CardNetHosted\Action\RefundAction;
use liamsorsby\CardNetHosted\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class CardnetHostedGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'cardnethosted',
            'payum.factory_title' => 'cardnethosted',
            'payum.action.capture' => new CaptureAction(),
            'payum.action.authorize' => new AuthorizeAction(),
            'payum.action.refund' => new RefundAction(),
            'payum.action.cancel' => new CancelAction(),
            'payum.action.notify' => new NotifyAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'sandbox' => true,
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = [];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Api((array) $config, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }
    }
}
