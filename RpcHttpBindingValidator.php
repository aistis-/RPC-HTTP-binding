<?php

/**
 * RPC HTTP binding validator.
 */
class RpcHttpBindingValidator
{
    /**
     * Validate HTTP request headers using $_SERVER global variable.
     *
     * Note: HTTP header parameters names are case-insensitive.
     *
     * @return bool
     */
    public static function validate()
    {
        // Check if request method is GET or POST
        if (in_array($_SERVER['REQUEST_METHOD'], array('GET', 'POST'), true)) {

            // Check if SOAPAction field exists in request header and it is not empty
            if (isset($_SERVER['HTTP_SOAPACTION']) && !empty($_SERVER['HTTP_SOAPACTION'])) {

                // Parse xml and find SOAP-ENV:Body inside SOAP-ENV:Envelope
                $xml = @simplexml_load_string(file_get_contents('php://input'));

                // Check if xml read was not successful
                if (false === $xml) {
                    return false;
                }

                $methodName = htmlspecialchars($_SERVER['HTTP_SOAPACTION']);

                $xPath = "//SOAP-ENV:Envelope/SOAP-ENV:Body/*[local-name()='{$methodName}']";

                $xmlElements = $xml->xpath($xPath);

                // Check if found SOAPAction
                if (1 === count($xmlElements)) {
                    return true;
                }
            }
        }

        return false;
    }
} 
