<?php

namespace Svea\Checkout\Implementation\Admin;

use Svea\Checkout\Model\Request;

class GetOrder extends AdminImplementationManager
{
    /**
     * url of action
     */
    protected $apiUrl = '/api/v1/orders/%d';

    /**
     * Request body - JSON
     *
     * @var Request $requestModel
     */
    private $requestModel;

    /**
     * Input data validation
     *
     * @param array $data Input data to Svea Checkout Library
     */
    public function validateData($data)
    {
        $this->validator->validate($data);
    }

    /**
     * Prepare body data for Api call
     *
     * @param array $data
     */
    public function prepareData($data)
    {
        $orderId = $data['orderid'];
        $this->requestModel = new Request();
        $this->requestModel->setGetMethod();

        $urlParams = array($orderId);
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
