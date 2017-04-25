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
        'password' => "",
        'txndatetime' => ""
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
            'mode',
            'password'
        ));

        if (false === is_bool($this->options['sandbox'])) {
            throw new InvalidArgumentException("The boolean sandbox option must be set.");
        }

        $this->options = $options;


        $this->options['txndatetime'] = $this->getDateTime();
        $this->client = $client;
        $this->messageFactory = $messageFactory;
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    protected function doRequest(array $fields)
    {
        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded',
        );

        $request = $this->messageFactory->createRequest('POST', $this->getApiEndpoint(), $headers, http_build_query($fields));
        $response = $this->client->send($request);
        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }
        $result = array();
        parse_str($response->getBody()->getContents(), $result);
        foreach ($result as &$value) {
            $value = urldecode($value);
        }
        return $result;
    }

    /**
     * Require: AUTHORIZATIONID, AMT, COMPLETETYPE
     *
     * @param array $fields
     *
     * @return array
     */
    public function doCapture(array $fields)
    {
        $fields['METHOD']  = 'DoCapture';
        $this->addAuthorizeFields($fields);
        return $this->doRequest($fields);
    }

    /**
     * @param array $fields
     */
    public function addAuthorizeFields(array $params)
    {
        $params['txndatetime'] = $this->getDateTime();
        $params['txntype'] = $this->options['txntype'];
        $params['storename'] = $this->options['storename'];
        $params['shared_secret'] = $this->options['shared_secret'];
        $params['mode'] = "payonly";
        $params['timezone'] = "Europe/London";
        $params['password'] = $this->options['password'];
        $params['hash'] = $this->getHash($params["chargetotal"]);
        $params['hash_algorithm'] = "SHA256";

        return $params;
    }

    protected function getDateTime()
    {
        return date("Y:m:d-H:i:s");
    }

    protected function getHash($total)
    {
        $stringToHash =  $this->options["storename"] . $this->getDateTime() . $total . 826 . $this->options['shared_secret'];
        $ascii = bin2hex($stringToHash);
        return hash('sha256',$ascii);
    }


    /**
     * @return string
     */
    public function getApiEndpoint()
    {
        return $this->options['sandbox'] ? 'https://test.ipg-online.com/connect/gateway/processing' : 'https://www.ipg-online.com/vt/login';
    }
}
