## What is it?

SOAP, originally an acronym for Simple Object Access protocol, is a protocol specification for exchanging structured information in the implementation of web services in computer networks. **It uses XML Information Set for its message format**, and relies on other application layer protocols, most notably Hypertext Transfer Protocol (HTTP) or Simple Mail Transfer Protocol (SMTP), for message negotiation and transmission.

SOAP specifies a uniform representation for RPC requests and responses which is platform independent. It does not define mappings to programming languages. Formally, RPC is not part of the core SOAP specification. Its use is optional.

SOAP messages can be transferred using any protocol and this class in for **HTTP binding validation**.

## An example of valid HTTP request
### Headers
```
SOAPAction: AwesomeMethod
Content-Type: application/xml
```
### Content
```
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/enveloper">
	<SOAP-ENV:Body>
		<m:AwesomeMethod xmlns:m="Some-URI">
			<symbol>Random-param</symbol>
		</m:AwesomeMethod>
	</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
```

## A list of rules

1. The request must be `GET` or `POST`. `DELETE`, `PUT`, `PATCH` etc. is not valid.
1. The request must have `SOAPAction` parameter.
1. The parameter cannot be empty and must be a string.
1. The request content must have `<Envelope>` tag.
1. The request content must have `<Body>` tag inside `<Envelope>` tag.
1. The request content must have `<FunctionName>` tag which is the same as `SOAPAction` parameter and it must be inside inside `<Body>` tag
1. Namespace `http://schemas.xmlsoap.org/soap/enveloper` is necessary.
1. Valid XML.
