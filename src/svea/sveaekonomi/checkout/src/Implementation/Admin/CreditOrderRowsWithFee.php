<?php

namespace Svea\Checkout\Implementation\Admin;

use Svea\Checkout\Model\Request;
use Svea\Checkout\Transport\Connector;
use Svea\Checkout\Validation\ValidationService;

class CreditOrderRowsWithFee extends AdminImplementationManager
{
    protected $apiUrl = '/api/v1/orders/%s/deliveries/%s/credits/CreditWithFee';

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
        
		$requestData['orderRowIds'] = $data['orderrowids'];

		if (!empty($data['fee'])) {
			$requestData['fee'] = $data['fee'];
		}

		if (!empty($data['rowcreditingoptions'])) {
			$requestData['rowCreditingOptions'] = $data['rowcreditingoptions'];
		}

        $orderId = $data['orderid'];
        $deliveryId = $data['deliveryid'];
        $urlParams = array($orderId, $deliveryId);

        $this->requestModel = new Request();
        $this->requestModel->setPostMethod();
        $this->requestModel->setBody(json_encode($requestData));
        $this->requestModel->setApiUrl($this->prepareUrl($urlParams));
    }

    /**
     * Invoke Api call
	 * 
	 * @return void
     */
    public function invoke()
    {
        $this->responseHandler = $this->connector->sendRequest($this->requestModel);
    }

    /**
	 * Get the request model
	 * 
     * @return Request
     */
    public function getRequestModel()
    {
        return $this->requestModel;
    }

    /**
	 * Set the request model
	 * 
     * @param Request $requestModel
	 * 
	 * @return void
     */
    public function setRequestModel($requestModel)
    {
        $this->requestModel = $requestModel;
    }
}
