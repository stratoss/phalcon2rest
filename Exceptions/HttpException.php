<?php
namespace Phalcon2Rest\Exceptions;

use Phalcon\Di,
    Phalcon2Rest\Responses\JsonResponse,
    Phalcon2Rest\Responses\CsvResponse;

class HttpException extends \Exception {

    public $devMessage;
    public $errorCode;
    public $response;
    public $additionalInfo;

    /**
     * HttpException constructor.
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     * @param array $errorArray
     */
    public function __construct($message, $code, $previous = null, $errorArray = []) {
        $this->message = $message;
        $this->devMessage = (array_key_exists('dev', $errorArray) ? $errorArray['dev'] : '');
        $this->errorCode = (array_key_exists('internalCode', $errorArray) ? $errorArray['internalCode'] : '');
        $this->code = $code;
        $this->additionalInfo = (array_key_exists('more', $errorArray) ? $errorArray['more'] : '');
        $this->response = $this->getResponseDescription($code);
    }

    public function send() {
        $di = Di::getDefault();

        $res = $di->get('response');
        $req = $di->get('request');

        //query string, filter, default
        if (!$req->get('suppress_response_codes', null, null)) {
            $res->setStatusCode($this->getCode(), $this->response)->sendHeaders();
        } else {
            $res->setStatusCode('200', 'OK')->sendHeaders();
        }

        $error = [
            'errorCode' => $this->getCode(),
            'userMessage' => $this->getMessage(),
            'devMessage' => $this->devMessage,
            'more' => $this->additionalInfo,
            'applicationCode' => $this->errorCode,
        ];

        if (!$req->get('type') || $req->get('type') === 'json') {
            $response = new JsonResponse();
            $response->send($error, true);
            return false;
        } elseif ($req->get('type') === 'csv') {
            $response = new CsvResponse();
            $response->send(array($error));
            return false;
        }

        error_log('HTTPException: ' . $this->getFile() . ' at ' . $this->getLine());

        return true;
    }

    protected function getResponseDescription($code) {
        $codes = [

            // Informational 1xx
            100 => 'Continue',
            101 => 'Switching Protocols',

            // Success 2xx
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',

            // Redirection 3xx
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',  // 1.1
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            // 306 is deprecated but reserved
            307 => 'Temporary Redirect',

            // Client Error 4xx
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',

            // Server Error 5xx
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            509 => 'Bandwidth Limit Exceeded'
        ];

        $result = (array_key_exists($code, $codes) ?
            $codes[$code]          :
            'Unknown Status Code'
        );

        return $result;
    }
}