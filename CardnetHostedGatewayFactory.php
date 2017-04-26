<?php
namespace liamsorsby\CardNetHosted;

use liamsorsby\CardNetHosted\Action\Api\AuthorizeTokenAction;
use liamsorsby\CardNetHosted\Action\NotifyAction;
use liamsorsby\CardNetHosted\Action\CaptureAction;
use liamsorsby\CardNetHosted\Action\RefundAction;
use liamsorsby\CardNetHosted\Action\StatusAction;
use liamsorsby\CardNetHosted\Request\Api\DoCapture;
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
            'payum.action.status' => new StatusAction(),
            'payum.action.capture' => new CaptureAction(),
            'payum.action.notify' => new NotifyAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'sandbox' => false,
                "txntype" => "",
                "storename" => "",
                "shared_secret" => "",
                "mode" => "",
                "password" => "",
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ["txntype", "storename", "shared_secret", "mode"];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                $cardnetConfig = array(
                    'sandbox' => $config['sandbox'],
                    'txntype' => $config['txntype'],
                    'storename' => $config['storename'],
                    'shared_secret' => $config['shared_secret'],
                    'mode' => $config['mode'],
                    'password' => $config['password'],
                );

                return new Api($cardnetConfig, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }
    }
}
