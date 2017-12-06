<?php

namespace Svea\Checkout\Implementation;

use Svea\Checkout\Model\InputDataModel;
use Svea\Checkout\Model\Request;

class UpdateOrder extends ImplementationManager
{
    protected $apiUrl = '/api/orders/%d';

    /**
     * Request body - JSON
     *
     * @var Request $requestModel
     */
    private $requestModel;

    /**
     * @param array $data
     * @throws \Svea\Checkout\Exception\SveaInputValidationException
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
        $cart = array();
        $cart['cart'] = $data['cart'];
        $orderId = $data['orderid'];
        $this->requestModel = new Request();
        $this->requestModel->setPutMethod();
        $this->requestModel->setBody(json_encode($cart));
        $this->requestModel->setApiUrl($this->connector->getBaseApiUrl() . sprintf($this->apiUrl, $orderId));
    }

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
