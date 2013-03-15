<?php

namespace VIRUS\webservice;

use VIRUS\webservice\CoreVIRUS;
use VIRUS\webservice\WebserviceResponse;

if (!defined("VIRUS"))
{
    die("You are not allowed here!");
}

/*
 * fields:
 * $resultArray
 * $page
 * $limit
 * $resource
 * $resultType
 */
//ob_start();

$response = $data->get('response', null);

/**
 * @var WebserviceResponse $response
 */
if (isset($response) && is_object($response) && $response instanceof WebserviceResponse)
{

    $status = $response->getStatus();
//    $outputArr = $response->getOutputArray();
    $resultType = $response->getResultType();
    $statusHttp = getStatusCode($status);
    \http_response_code($status);


    $output = $response->getOutputArrayAsXML();

    $output = '<' . RESPONSE_APP_NAME . " status=\"$status\" code=\"$statusHttp\">
                $output
        </" . RESPONSE_APP_NAME . '>';
    if ($resultType == 'json')
    {
        header('Content-type: application/json');
        $output = \xml2json::transformXmlStringToJson($output);
    } else
    {
        header('Content-type: text/xml, charset=utf-8');
    }
    CoreVIRUS::logDebug("Response (httpCode=$statusHttp; type=$resultType)");
    CoreVIRUS::log(CoreVIRUS::LOG_DEBUG_VERBOSE, "Response output body: $output");
    echo $output;

} else
{
    ob_end_flush();
    CoreVIRUS::logFatal('The $response parameter in apiv1result_view is not a valid output array.');
    showErrorResponse(500, getStatusCode(500), "An unexpected error has happened while processing your request.", '', false);
}




