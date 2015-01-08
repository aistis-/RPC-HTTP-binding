<?php

/**
 * RPC HTTP binding validator.
 */
class RpcHttpBindingValidator
{
    /**
     * XML schema namespace URL for envelope tag.
     */
    const NS_XML_ENVELOPE = 'http://schemas.xmlsoap.org/soap/enveloper';

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
        if (isset($_SERVER['REQUEST_METHOD']) &&
            in_array($_SERVER['REQUEST_METHOD'], array('GET', 'POST'), true)) {

            // Check if SOAPAction field exists in request header and it is not empty
            if (isset($_SERVER['HTTP_SOAPACTION']) && !empty($_SERVER['HTTP_SOAPACTION'])) {

                // Parse xml and find SOAP-ENV:Body inside SOAP-ENV:Envelope
                $xml = @simplexml_load_string(file_get_contents('php://input'));

                // Check if xml read was not successful
                if (false === $xml) {
                    return false;
                }

                // Namespaces checking
                $namespace = array_search(self::NS_XML_ENVELOPE, $xml->getNamespaces(), true);

                $methodName = htmlspecialchars($_SERVER['HTTP_SOAPACTION']);

                // Check if namespace in xml was not found
                if (empty($namespace)) {
                    return false;
                }

                $xPath = "//{$namespace}:Envelope/{$namespace}:Body/*[local-name()='{$methodName}']";


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
