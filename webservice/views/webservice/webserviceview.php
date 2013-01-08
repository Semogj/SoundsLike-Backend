<?php
namespace VIRUS\webservice;
use VIRUS\webservice\CoreVIRUS;
use VIRUS\webservice\WebserviceResponse;

if(!defined("VIRUS")){
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
$logger = CoreVIRUS::getLogger();

/**
 * @var WebserviceResponse $response
 */
if (isset($response) && is_object($response) && $response instanceof WebserviceResponse)
{

    $status = $response->getStatus();
    $outputArr = $response->getOutputArray();
    $resultType = $response->getResultType();

    \http_response_code($status);
    if ($resultType == 'json')
    {
        header('Content-type: application/json');
        echo json_encode(array(RESPONSE_APP_NAME => $outputArr));
    } else
    {
        header('Content-type: text/xml');
        $output = $response->getOutputArrayAsXML();
        $statusHttp = getStatusCode($status);
        echo '<', RESPONSE_APP_NAME, " status=\"$status\" code=\"$statusHttp\">
                $output
        </", RESPONSE_APP_NAME, '>';
    }
//    $b=ob_get_contents();
//    $logger->LogFatal(var_export($b, true));
//    ob_end_flush();
} else
{
//    ob_end_flush();
    $logger->LogFatal('The $response parameter in apiv1result_view is not a valid output array.');
    showErrorResponse(500, getStatusCode(500), "An unexpected error has happened while processing your request.", '', false);
}




