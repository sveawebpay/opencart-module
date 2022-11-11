<?php

namespace Svea\Checkout\Implementation\Admin;

use Svea\Checkout\Model\Request;
use Svea\Checkout\Transport\Connector;
use Svea\Checkout\Validation\ValidationService;

class ReplaceOrderRows extends AdminImplementationManager
{
    protected $apiUrl = '/api/v1/orders/%s/rows/replaceOrderRows';

    /**
     * @var bool $isNewCreditRow
     */
    protected $isNewCreditRow;

    /**
     * Request body - JSON
     *
     * @var Request $requestModel
     */
    private $requestModel;

    /**
     * CreditOrderRows constructor.
     * @param Connector $connector
     * @param ValidationService $validationService
     * @param bool $isNewCreditRow
     */
    public function __construct(Connector $connector, ValidationService $validationService)
    {
        parent::__construct($connector, $validationService);
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

		$requestData['orderRows'] = $data['orderrows'];

        $orderId = $data['orderid'];
        $urlParams = array($orderId);

        $this->requestModel = new Request();
        $this->requestModel->setPutMethod();
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
