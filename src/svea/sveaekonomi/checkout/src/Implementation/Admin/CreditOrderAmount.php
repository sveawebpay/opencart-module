<?php

namespace Svea\Checkout\Implementation\Admin;

use Svea\Checkout\Model\Request;

class CreditOrderAmount extends AdminImplementationManager
{
    /**
     * @var string $apiUrl
     */
    protected $apiUrl = '/api/v1/orders/%d/deliveries/%d';

    /**
     * Request body - JSON
     *
     * @var Request $requestModel
     */
    private $requestModel;

    /**
     * Input data validation
     * @param array $data Input data to Svea Checkout Library
     */
    public function validateData($data)
    {
        $validator = $this->validator;
        $validator->validate($data);
    }

    /**
     * Prepare date for request
     *
     * @param array $data
     */
    public function prepareData($data)
    {
        $requestData = array();
        $requestData['creditedAmount'] = $data['creditedamount'];

        $orderId = $data['orderid'];
        $deliveryId = $data['deliveryid'];

        $urlParams = array($orderId, $deliveryId);

        $this->requestModel = new Request();
        $this->requestModel->setPatchMethod();
        $this->requestModel->setBody(json_encode($requestData));
        $this->requestModel->setApiUrl($this->prepareUrl($urlParams));
    }

    /**
     * Invoke Api call
     */
    public function invoke()
    {
        $this->responseHandler = $this->connector->sendRequest($this->requestModel);
    }

    /**
     * @return Request
     */
    public function getRequestModel()
    {
        return $this->requestModel;
    }

    /**
     * @param Request $requestModel
     */
    public function setRequestModel($requestModel)
    {
        $this->requestModel = $requestModel;
    }
}
