<?php

namespace Svea\Checkout\Tests\Unit\Exception;

use Svea\Checkout\Exception\ExceptionCodeList;
use Svea\Checkout\Tests\Unit\TestCase;

class ExceptionCodeListTest extends TestCase
{
    public function testGetErrorMessageWithExistingErrorCode()
    {
        $errorCode = ExceptionCodeList::INPUT_VALIDATION_ERROR;
        $ecl = new ExceptionCodeList();
        $result = $ecl->getErrorMessage($errorCode);


        $this->assertNotEquals(ExceptionCodeList::UNKNOWN_CODE_MESSAGE, $result);
    }

    public function testGetErrorMessageWithUnknownErrorCode()
    {
        $errorCode = 99954612;
        $ecl = new ExceptionCodeList();
        $result = $ecl->getErrorMessage($errorCode);


        $this->assertEquals(ExceptionCodeList::UNKNOWN_CODE_MESSAGE, $result);
    }
}
