<?php

namespace Svea\Checkout\Implementation\Admin;

use Svea\Checkout\Model\Request;
use Svea\Checkout\Transport\Connector;
use Svea\Checkout\Validation\ValidationService;

class CancelOrder extends AdminImplementationManager
{
    /**
     * @var bool $isCancelAmount
     */
    protected $isCancelAmount;

    /**
     * @var string $apiUrl
     */
    protected $apiUrl = '/api/v1/orders/%d';

    /**
     * Request body - JSON
     *
     * @var Request $requestModel
     */
    private $requestModel;

    public function __construct(Connector $connector, ValidationService $validationService, $isCancelAmount = false)
    {
        parent::__construct($connector, $validationService);
        $this->isCancelAmount = $isCancelAmount;
    }

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

        if ($this->isCancelAmount === true) {
            $requestData['cancelledAmount'] = $data['cancelledamount'];
        } else {
            /**
             * Determines that this order is cancelled
             * Required field if amount is not specified
             */
            $requestData['isCancelled'] = true;
        }

        $orderId = $data['orderid'];
        $this->requestModel = new Request();
        $this->requestModel->setPatchMethod();
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

    /**
     * @return boolean
     */
    public function isIsCancelAmount()
    {
        return $this->isCancelAmount;
    }

    /**
     * @param boolean $isCancelAmount
     */
    public function setIsCancelAmount($isCancelAmount)
    {
        $this->isCancelAmount = $isCancelAmount;
    }
}
