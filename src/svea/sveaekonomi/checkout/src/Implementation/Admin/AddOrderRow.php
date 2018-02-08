<?php

namespace Svea\Checkout\Implementation\Admin;

use Svea\Checkout\Model\Request;

class AddOrderRow extends AdminImplementationManager
{
    protected $apiUrl = '/api/v1/orders/%d/rows';

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
        $this->validator->validate($data);
    }

    /**
     * Prepare body data for Api call
     * @param array $data
     */
    public function prepareData($data)
    {
        $requestData = $data['orderrow'];

        $orderId = $data['orderid'];
        $this->requestModel = new Request();
        $this->requestModel->setPostMethod();
        $this->requestModel->setBody(json_encode($requestData));

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
