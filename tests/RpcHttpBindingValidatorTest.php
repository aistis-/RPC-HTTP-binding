<?php

require_once __DIR__ . '/../RpcHttpBindingValidator.php';
require_once 'Mock/MockPhpStream.php';

class RpcHttpBindingValidatorTest extends PHPUnit_Framework_TestCase
{
    public function testMissingHeaders()
    {
        unset($_SERVER);

        $this->assertFalse(RpcHttpBindingValidator::validate());
    }

    public function testMissingSoapActionHeader()
    {
        if (isset($_SERVER)) {
            unset($_SERVER['HTTP_SOAPACTION']);
        }

        $this->assertFalse(RpcHttpBindingValidator::validate());
    }

    public function testEmptySoapActionHeader()
    {
        $_SERVER['HTTP_SOAPACTION'] = '';

        $this->assertFalse(RpcHttpBindingValidator::validate());
    }

    public function testRequestBody()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_SOAPACTION'] = 'TestMethodName';

        stream_wrapper_unregister('php');
        stream_wrapper_register('php', 'MockPhpStream');

        // Correct
        $this->inputStreamTestCase(
            '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/enveloper">
                <SOAP-ENV:Body>
                    <m:' . $_SERVER['HTTP_SOAPACTION'] . ' xmlns:m="Some-URI">
                        <symbol>Random-parameter</symbol>
                    </m:' . $_SERVER['HTTP_SOAPACTION'] . '>
                </SOAP-ENV:Body>
            </SOAP-ENV:Envelope>',
            true
        );

        // Wrong method name
        $this->inputStreamTestCase(
            '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/enveloper">
                <SOAP-ENV:Body>
                    <m:prefix_' . $_SERVER['HTTP_SOAPACTION'] . ' xmlns:m="Some-URI">
                        <symbol>Random-parameter</symbol>
                    </m:prefix_' . $_SERVER['HTTP_SOAPACTION'] . '>
                </SOAP-ENV:Body>
            </SOAP-ENV:Envelope>',
            false
        );

        // Missing body tag
        $this->inputStreamTestCase(
            '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/enveloper">
                    <m:' . $_SERVER['HTTP_SOAPACTION'] . ' xmlns:m="Some-URI">
                        <symbol>Random-parameter</symbol>
                    </m:' . $_SERVER['HTTP_SOAPACTION'] . '>
            </SOAP-ENV:Envelope>',
            false
        );

        // Missing envelope tag
        $this->inputStreamTestCase(
                '<SOAP-ENV:Body xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/enveloper">
                    <m:' . $_SERVER['HTTP_SOAPACTION'] . ' xmlns:m="Some-URI">
                        <symbol>Random-parameter</symbol>
                    </m:' . $_SERVER['HTTP_SOAPACTION'] . '>
                </SOAP-ENV:Body>',
            false
        );

        // Typo in envelope tag
        $this->inputStreamTestCase(
            '<SOAP-ENV:Envelopee xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/enveloper">
                <SOAP-ENV:Body>
                    <m:' . $_SERVER['HTTP_SOAPACTION'] . ' xmlns:m="Some-URI">
                        <symbol>Random-parameter</symbol>
                    </m:' . $_SERVER['HTTP_SOAPACTION'] . '>
                </SOAP-ENV:Body>
            </SOAP-ENV:Envelopee>',
            false
        );

        // Typo in body tag
        $this->inputStreamTestCase(
            '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/enveloper">
                <SOAP-ENV:Bodyy>
                    <m:' . $_SERVER['HTTP_SOAPACTION'] . ' xmlns:m="Some-URI">
                        <symbol>Random-parameter</symbol>
                    </m:' . $_SERVER['HTTP_SOAPACTION'] . '>
                </SOAP-ENV:Bodyy>
            </SOAP-ENV:Envelope>',
            false
        );

        // Not valid XML
        $this->inputStreamTestCase(
            '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/enveloper">
                <SOAP-ENV:Body>
                    <m:' . $_SERVER['HTTP_SOAPACTION'] . ' xmlns:m="Some-URI">
                        <symbol>Random-parameter</symbol>
                    </m:' . $_SERVER['HTTP_SOAPACTION'] . '>
                </SOAP-ENV:Body>',
            false
        );

        // Bad namespace
        $this->inputStreamTestCase(
            '<SOAP-ENV:Envelope xmlns:SOAP-ENV="BAD_NAMESPACE">
                <SOAP-ENV:Body>
                    <m:' . $_SERVER['HTTP_SOAPACTION'] . ' xmlns:m="Some-URI">
                        <symbol>Random-parameter</symbol>
                    </m:' . $_SERVER['HTTP_SOAPACTION'] . '>
                </SOAP-ENV:Body>
            </SOAP-ENV:Envelope>',
            false
        );

        // Extra tags
        $this->inputStreamTestCase(
            '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/enveloper">
                <o></o>
                <SOAP-ENV:Body>
                    <o></o>
                    <o></o>
                    <m:' . $_SERVER['HTTP_SOAPACTION'] . ' xmlns:m="Some-URI">
                        <symbol>Random-parameter</symbol>
                    </m:' . $_SERVER['HTTP_SOAPACTION'] . '>
                </SOAP-ENV:Body>
            </SOAP-ENV:Envelope>',
            true
        );

        stream_wrapper_restore('php');
    }

    private function inputStreamTestCase($requestBody, $result)
    {
        file_put_contents('php://input', $requestBody);

        if ($result) {
            $this->assertTrue(RpcHttpBindingValidator::validate());
        } else {
            $this->assertFalse(RpcHttpBindingValidator::validate());
        }
    }
} 

