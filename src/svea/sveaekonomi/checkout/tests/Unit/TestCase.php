<?php

namespace Svea\Checkout\Tests\Unit;

use Svea\Checkout\Model\Request;
use Svea\Checkout\Transport\ApiClient;
use Svea\Checkout\Transport\Connector;
use Svea\Checkout\Transport\Http\HttpRequestInterface;

class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Request $requestModel
     */
    protected $requestModel;

    /**
     * @var Connector|\PHPUnit_Framework_MockObject_MockObject $connectorMock
     */
    protected $connectorMock;

    /**
     * @var ApiClient|\PHPUnit_Framework_MockObject_MockObject $apiClientMock
     */
    protected $apiClientMock;

    /**
     * @var HttpRequestInterface|\PHPUnit_Framework_MockObject_MockObject $httpClientMock
     */
    protected $httpClientMock;

    /**
     * @var array $inputCreateData
     */
    protected $inputCreateData;

    /**
     * @var array $inputUpdateData
     */
    protected $inputUpdateData;

    /**
     * @var array $inputGetAvailablePartPaymentCampaignsData
     */
    protected $inputGetAvailablePartPaymentCampaignsData;

    /**
     * @var string $apiResponse
     */
    protected $apiResponse;

    /**
     * Client credential data
     *
     * @var string $merchantId
     */
    protected $merchantId = '123456';

    /**
     * Client credential data
     *
     * @var string $sharedSecret
     */
    protected $sharedSecret = '80e3a905e597ca428f4e25200433263c';

    /**
     * @var string $apiUrl
     */
    protected $apiUrl = Connector::TEST_BASE_URL;

    protected function setUp()
    {
        $this->setApiResponse();
        $this->setRequest();
        $this->setCurlRequest();
        $this->setApiClient();
        $this->setConnector();
        $this->setInputCreateData();
        $this->setInputUpdateData();
        $this->setInputGetAvailablePartPaymentCampaignsData();
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object $object Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    protected function invokeMethod($object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    private function setRequest()
    {
        $this->requestModel = new Request();
        $this->requestModel->setApiUrl($this->apiUrl);
        $this->requestModel->setBody(json_encode($this->inputCreateData));
        $this->requestModel->setPostMethod();
        $this->requestModel->setAuthorizationToken('123456789');
    }

    private function setCurlRequest()
    {
        $this->httpClientMock = $this->getMockBuilder('Svea\Checkout\Transport\Http\HttpRequestInterface')->getMock();
    }

    private function setApiClient()
    {
        $httpClientMock = $this->getMockBuilder('Svea\Checkout\Transport\Http\HttpRequestInterface')->getMock();
        $this->apiClientMock = $this->getMockBuilder('\Svea\Checkout\Transport\ApiClient')
            ->setConstructorArgs(array($httpClientMock))
            ->getMock();
    }

    private function setConnector()
    {
        $this->connectorMock = $this->getMockBuilder('Svea\Checkout\Transport\Connector')
            ->setConstructorArgs(array($this->apiClientMock, $this->merchantId, $this->sharedSecret, $this->apiUrl))
            ->getMock();
    }

    private function setApiResponse()
    {
        $json = <<<JSON
        {
            "MerchantSettings":{
                "TermsUri":"http://localhost:51898/terms",
                "CheckoutUri":"http://localhost:51925/",
                "ConfirmationUri":"http://localhost:51925/checkout/confirm",
                "PushUri":"https://svea.com/push.aspx?sid=123&svea_order=123"
            },
            "Cart":{
                "Items":[
                    {
                        "ArticleNumber":"123456789",
                        "Name":"Dator",
                        "Quantity":200,
                        "UnitPrice":12300,
                        "DiscountPercent":1000,
                        "VatPercent":2500,
                        "Unit":null
                    },
                    {
                        "ArticleNumber":"SHIPPING",
                        "Name":"Shipping Fee",
                        "Quantity":100,
                        "UnitPrice":4900,
                        "DiscountPercent":0,
                        "VatPercent":2500,
                        "Unit":null
                    }
                ]
            },
            "Customer":null,
            "ShippingAddress":null,
            "BillingAddress":null,
            "Gui":{
                "Layout":"desktop",
                "Snippet":""
            },
            "Locale":"sv-SE",
            "Currency":null,
            "CountryCode":null,
            "PresetValues":null,
            "OrderId":13,
            "Status":"Created"
        }
JSON;

        $this->apiResponse = 'HTTP/1.1 201 Created' . "\r\n";
        $this->apiResponse .= 'Cache-Control: no-cache' . "\r\n";
        $this->apiResponse .= 'Pragma: no-cache' . "\r\n";
        $this->apiResponse .= 'Content-Length: 3469' . "\r\n";
        $this->apiResponse .= 'Content-Type: application/json; charset=utf-8' . "\r\n";
        $this->apiResponse .= 'Expires: -1' . "\r\n";
        $this->apiResponse .= 'Server: Microsoft-IIS/8.5' . "\r\n";
        $this->apiResponse .= 'X-AspNet-Version: 4.0.30319' . "\r\n";
        $this->apiResponse .= 'X-Powered-By: ASP.NET' . "\r\n";
        $this->apiResponse .= 'Date: Wed, 27 Apr 2016 09:42:19 GMT';

        $this->apiResponse .= "\r\n\r\n" . $json;
    }

    private function setInputCreateData()
    {
        $this->inputCreateData = array(
            "countrycode" => "SE",
            "currency" => "SEK",
            "locale" => "sv-SE",
            "cart" => array(
                "items" => array(
                    array(
                        "articlenumber" => "123456",
                        "name" => "Tomatoes",
                        "quantity" => 10,
                        "unitprice" => 600,
                        "discountpercent" => 1000,
                        "vatpercent" => 2500
                    ),
                    array(
                        "articlenumber" => "654321",
                        "name" => "Bananas",
                        "quantity" => 1,
                        "unitprice" => 500,
                        "discountpercent" => 900,
                        "vatpercent" => 2000
                    )
                )
            ),
            "clientordernumber" => '12312312312213231',
            "merchantsettings" => array(
                "termsuri" => "http://www.merchant.com/toc",
                "checkouturi" => "http://www.merchant.com/checkout?svea_order_id={checkout.order.id}",
                "confirmationuri" => "http://www.merchant.com/thank-you?svea_order_id={checkout.order.id}",
                "pushuri" => "http://www.merchant.com/create_order?svea_order_id={checkout.order.id}"
            )
        );
    }

    private function setInputUpdateData()
    {
        $this->inputUpdateData = array(
            "orderid" => 4,
            "cart" => array(
                "items" => array(
                    array(
                        "articlenumber" => "123456789",
                        "name" => "Dator",
                        "quantity" => 200,
                        "unitprice" => 12300,
                        "discountpercent" => 1000,
                        "vatpercent" => 2500
                    ),
                    array(
                        "articlenumber" => "SHIPPING",
                        "name" => "Shipping fee",
                        "quantity" => 100,
                        "unitprice" => 4900,
                        "vatpercent" => 2500
                    )
                )
            )
        );
    }

    private function setInputGetAvailablePartPaymentCampaignsData()
    {
        $this->inputGetAvailablePartPaymentCampaignsData = array("IsCompany" => false);
    }
}
