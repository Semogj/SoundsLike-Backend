<?php
    $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1');
    header($protocol . ' 404 Not Found');
    $GLOBALS['http_response_code'] = 404;
?>
