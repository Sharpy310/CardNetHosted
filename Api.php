<?php
namespace liamsorsby\CardNetHosted;

use Http\Message\MessageFactory;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\HttpClientInterface;
use Payum\Core\Bridge\Spl\ArrayObject;

class Api
{


    /**
     * It sends back if the message originated with PayPal.
     */
    const NOTIFY_VERIFIED = 'VERIFIED';
    /**
     * if there is any discrepancy with what was originally sent
     */
    const NOTIFY_INVALID = 'INVALID';


    const CMD_NOTIFY_VALIDATE = '_notify-validate';


    /**
     * @var HttpClientInterface
     */
    protected $client;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var array
     */
    protected $options = array(
        'sandbox' => true,
        'txntype => ""',
        'storename' => "",
        'shared_secret' => "",
        'mode' => "",
    );

    /**
     * @param array               $options
     * @param HttpClientInterface $client
     * @param MessageFactory      $messageFactory
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(array $options, HttpClientInterface $client, MessageFactory $messageFactory)
    {
        $options = ArrayObject::ensureArrayObject($options);
        $options->defaults($this->options);
        $options->validateNotEmpty(array(
            'txntype',
            'storename',
            'shared_secret',
            'mode'
        ));

        if (false === is_bool($this->options['sandbox'])) {
            throw new InvalidArgumentException("The boolean sandbox option must be set.");
        }

        $this->options = $options;
        $this->client = $client;
        $this->messageFactory = $messageFactory;
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    public function notifyValidate(array $fields)
    {
        $headers = [];

        $fields['cmd'] = self::CMD_NOTIFY_VALIDATE;

        $request = $this->messageFactory->createRequest('POST', $this->getIpnEndpoint(), $headers, http_build_query($fields));

        $response = $this->client->send($request);

        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        return $response;
    }

    /**
     * @return string
     */
    public function getIpnEndpoint()
    {
        return $this->options['sandbox'] ? 'https://test.ipg-online.com/connect/gateway/processing' : 'https://ipg-online.com/connect/gateway/processing';
    }
}
