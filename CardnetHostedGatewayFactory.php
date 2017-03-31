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
            'payum.action.refund' => new RefundAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'sandbox' => "",
                "txntype" => "",
                "storename" => "",
                "shared_secret" => "",
                "mode" => "",
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ["sandbox", "txntype", "storename", "shared_secret", "mode"];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                $cardnetConfig = array(
                    'sandbox' => $config['sandbox'],
                    'txntype' => $config['txntype'],
                    'storename' => $config['storename'],
                    'shared_secret' => $config['shared_secret'],
                    'mode' => $config['mode'],
                );

                return new Api($cardnetConfig, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }
    }
}
