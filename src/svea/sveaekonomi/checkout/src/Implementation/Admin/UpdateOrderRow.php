<?php

namespace Svea\Checkout\Implementation\Admin;

use Svea\Checkout\Model\Request;

class UpdateOrderRow extends AdminImplementationManager
{
    protected $apiUrl = '/api/v1/orders/%d/rows/%d';

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
        $orderRowData = array();
        if (isset($data['orderrow'])) {
            $orderRowData = $data['orderrow'];
        }


        $orderId = $data['orderid'];
        $orderRowId = $data['orderrowid'];
        $urlParams = array($orderId, $orderRowId);

        $this->requestModel = new Request();
        $this->requestModel->setPatchMethod();
        $this->requestModel->setBody(json_encode($orderRowData));
        $this->requestModel->setApiUrl($this->prepareUrl($urlParams));
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
