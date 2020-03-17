<?php
namespace Pod\Ai\Platform;
use Pod\Base\Service\ApiRequestHandler;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Pod\Base\Service\Exception\PodException;
use Pod\Base\Service\Exception\RequestException;

Class AiApiRequestHandler extends ApiRequestHandler {
    /**
     * @param string $baseUri
     * @param string $method
     * @param string $relativeUri
     * @param array $option
     * @param bool $optionHasArray
     * @param bool $restFull
     *
     * @return mixed
     *
     * @throws RequestException
     * @throws PodException
     */
    public static function Request($baseUri, $method, $relativeUri, $option, $restFull = false, $optionHasArray = false) {
        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $baseUri,
            // You can set any number of default request options.
            'timeout'  => 30.0,
        ]);

        if ($optionHasArray && $method == 'GET') {

            $withoutBracketParams = isset($option['withoutBracketParams']) ? $option['withoutBracketParams'] : [];
            $withBracketParams = isset($option['withBracketParams']) ? $option['withBracketParams'] : [];

            $httpQuery = self::buildHttpQuery($withoutBracketParams, $withBracketParams);
            $relativeUri = $relativeUri . '?' . $httpQuery;
            unset($option['withoutBracketParams']); // unset query because it is added to uri and dont need send again in query params
            unset($option['withBracketParams']); // unset query because it is added to uri and dont need send again in query params
        }
        try {
            $response = $client->request($method, $relativeUri, $option);
        }
        catch (ClientException $e) {
            // echo Psr7\str($e->getRequest());
            $message = $e->getMessage();
            $code = $e->getCode();
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $code = $response->getStatusCode();
                $responseBody = json_decode($response->getBody()->getContents(), true);
                $message = (isset($responseBody['message']) && !empty($responseBody['message'])) ? $responseBody['message'] : $message;
                throw new RequestException($message, $code, null, $responseBody);
            }

            $code = !isset($code)? RequestException::SERVER_CONNECTION_ERROR : $code;
            $message  = empty($message)? 'Connection Interrupt! please try again later.' : $message;
            throw new RequestException($message, $code);
        } catch (GuzzleException $e) {
            $message = $e->getMessage();
            $code = $e->getCode();
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $code = $response->getStatusCode();
                $responseBody = json_decode($response->getBody()->getContents(), true);
                $message = (isset($responseBody['message']) && !empty($responseBody['message'])) ? $responseBody['message'] : $message;
                throw new RequestException($message, $code, null, $responseBody);
            }
            else {
                throw new PodException($message, $code);
            }
        }

        $result = json_decode($response->getBody()->getContents(), true);
        if(isset($result['result']['result'])) {
            $aiServiceResult =  json_decode($result['result']['result'], true);
            $result['result']['result'] = $aiServiceResult;
        } else {
            $aiServiceResult = '';
        }

        // handle error from pod
        if (isset($result['hasError']) && $result['hasError']) {
            if (!isset($result['message']) && isset($result['errorDescription'])) {
                $result['message'] = $result['errorDescription'];
                unset($result['errorDescription']);
            }
            $message = isset($result['message']) ? $result['message'] :"Some unhandled error has occurred!";
            $errorCode = isset($result['errorCode']) ? $result['errorCode'] : PodException::SERVER_UNHANDLED_ERROR_CODE;
            throw new PodException($message, $errorCode,null, $result);
            // handle error from Ai Service
        } elseif (isset($aiServiceResult['hasError']) && $aiServiceResult['hasError']){

            $message = isset($aiServiceResult['status']) ? $aiServiceResult['status'] :
                (
                isset($aiServiceResult['statusMessage']) ? $aiServiceResult['statusMessage'] :
                    (isset($aiServiceResult['data']) && !empty($aiServiceResult['data']) ? $aiServiceResult['data'] :
                        "Some unhandled error has occurred!"
                    )
                );

            $errorCode = isset($aiServiceResult['statusCode']) ? $aiServiceResult['statusCode'] :
                (isset($result['result']['statusCode']) ? $result['result']['statusCode']: PodException::SERVER_UNHANDLED_ERROR_CODE);
            throw new PodException($message, $errorCode,null, $result);
        }
        return $result;
    }
}