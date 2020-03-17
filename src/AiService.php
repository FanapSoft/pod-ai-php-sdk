<?php
namespace Pod\Ai\Platform;

use Pod\Base\Service\BaseService;
use Pod\Base\Service\BaseInfo;
use Pod\Base\Service\ApiRequestHandler;

class AiService extends BaseService
{
    private $header;
    private static $jsonSchema;
    private static $aiApi;
    private static $serviceCallProductId;
    private static $baseUri;
    private static $resultStatus = [
        'noFaceInSecondImage' => -2,
        'noFaceInFirstImage' => -1,
        'match' => 0,
        'probablyMatch' => 1,
        'probablyMismatch' => 2,
        'mismatch' => 3,
    ];

    private static $resultMessage = [
        'noFaceInSecondImage' => 'در تصویر دوم چهره وجود ندارد',
        'noFaceInFirstImage' => 'در تصویر اول چهره وجود ندارد',
        'match' => 'هر دو تصویر متعلق به یک نفر است',
        'probablyMatch' => 'به احتمال فراوان دو تصویر متعلق به یک نفر است',
        'probablyMismatch' => 'به احتمال فراوان دو تصویر متعلق به یک نفر نیست',
        'mismatch' => 'دو تصویر متعلق به دو نفر است',
    ];

    public function __construct($baseInfo)
    {
        BaseInfo::initServerType(BaseInfo::PRODUCTION_SERVER);
        parent::__construct();
        self::$jsonSchema = json_decode(file_get_contents(__DIR__ . '/../config/validationSchema.json'), true);
        $this->header = [
            '_token_issuer_'    =>  $baseInfo->getTokenIssuer(),
            '_token_'           => $baseInfo->getToken(),
        ];
        self::$aiApi = require __DIR__ . '/../config/apiConfig.php';
        self::$serviceCallProductId = require __DIR__ . '/../config/serviceCallProductId.php';
//        self::$serviceCallProductId = self::$serviceCallProductId[self::$serverType];
        self::$baseUri = self::$config[self::$serverType];
    }

    public function speechToText($params) {
        $apiName = 'speechToText';
        $header = $this->header;
        $optionHasArray = false;
        array_walk_recursive($params, 'self::prepareData');
        $relativeUri = self::$aiApi[$apiName]['subUri'];
        $method = self::$aiApi[$apiName]['method'];
        $paramKey = $method == 'GET' ? 'query' : 'form_params';

        // if token is set replace it
        if(isset($params['token'])) {
            $header["_token_"] = $params['token'];
            unset($params['token']);
        }

        $option = [
            'headers' => $header,
            $paramKey => $params,
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);

        # set service call product Id
        $option[$paramKey]['scProductId'] = self::$serviceCallProductId[$apiName];

        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option[$paramKey];
            unset($option[$paramKey]);
            $optionHasArray = true;
            $method = 'GET';
        }

        return AiApiRequestHandler::Request(
            self::$config[self::$serverType][self::$aiApi[$apiName]['baseUri']],
            $method,
            $relativeUri,
            $option,
            false,
            $optionHasArray
        );
    }

    public function imageProcessingAuthentication($params) {
        $apiName = 'imageProcessingAuthentication';
        $header = $this->header;
        $optionHasArray = false;
        array_walk_recursive($params, 'self::prepareData');
        $relativeUri = self::$aiApi[$apiName]['subUri'];
        $method = self::$aiApi[$apiName]['method'];
        $paramKey = $method == 'GET' ? 'query' : 'form_params';

        // if token is set replace it
        if(isset($params['token'])) {
            $header["_token_"] = $params['token'];
            unset($params['token']);
        }

        $option = [
            'headers' => $header,
            $paramKey => $params,
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);

        # set service call product Id
        $option[$paramKey]['scProductId'] = self::$serviceCallProductId[$apiName];

        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option[$paramKey];
            unset($option[$paramKey]);
            $optionHasArray = true;
            $method = 'GET';
        }

        $result =  AiApiRequestHandler::Request(
            self::$config[self::$serverType][self::$aiApi[$apiName]['baseUri']],
            $method,
            $relativeUri,
            $option,
            false,
            $optionHasArray
        );
        $imageProcessorResult = json_decode($result['result']['result'], true);
        if(!$result['hasError'] && !$imageProcessorResult['hasError']) {
            switch ($imageProcessorResult['data']['resultStatus']) {
                case self::$resultStatus['noFaceInSecondImage']:
                    $imageProcessorResult['data']['resultMessage'] = self::$resultMessage['noFaceInSecondImage'];
                    break;
                case self::$resultStatus['noFaceInFirstImage']:
                    $imageProcessorResult['data']['resultMessage'] = self::$resultMessage['noFaceInFirstImage'];
                    break;
                case self::$resultStatus['match']:
                    $imageProcessorResult['data']['resultMessage'] = self::$resultMessage['match'];
                    break;
                case self::$resultStatus['probablyMatch']:
                    $imageProcessorResult['data']['resultMessage'] = self::$resultMessage['probablyMatch'];
                    break;
                case self::$resultStatus['probablyMismatch']:
                    $imageProcessorResult['data']['resultMessage'] = self::$resultMessage['probablyMismatch'];
                    break;
                case self::$resultStatus['mismatch']:
                    $imageProcessorResult['data']['resultMessage'] = self::$resultMessage['mismatch'];
                    break;
            }
            $result['result']['result'] = json_encode($imageProcessorResult, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        return $result;
    }

    public function NLUBanking($params) {
        $apiName = 'NLUBanking';
        $header = $this->header;
        $optionHasArray = false;
        array_walk_recursive($params, 'self::prepareData');
        $relativeUri = self::$aiApi[$apiName]['subUri'];
        $method = self::$aiApi[$apiName]['method'];
        $paramKey = $method == 'GET' ? 'query' : 'form_params';

        // if token is set replace it
        if(isset($params['token'])) {
            $header["_token_"] = $params['token'];
            unset($params['token']);
        }

        $option = [
            'headers' => $header,
            $paramKey => $params,
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);

        # set service call product Id
        $option[$paramKey]['scProductId'] = self::$serviceCallProductId[$apiName];

        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option[$paramKey];
            unset($option[$paramKey]);
            $optionHasArray = true;
            $method = 'GET';
        }

        return AiApiRequestHandler::Request(
            self::$config[self::$serverType][self::$aiApi[$apiName]['baseUri']],
            $method,
            $relativeUri,
            $option,
            false,
            $optionHasArray
        );
    }

    public function NLUIOT($params) {
        $apiName = 'NLUIOT';
        $header = $this->header;
        $optionHasArray = false;
        array_walk_recursive($params, 'self::prepareData');
        $relativeUri = self::$aiApi[$apiName]['subUri'];
        $method = self::$aiApi[$apiName]['method'];
        $paramKey = $method == 'GET' ? 'query' : 'form_params';

        // if token is set replace it
        if(isset($params['token'])) {
            $header["_token_"] = $params['token'];
            unset($params['token']);
        }

        $option = [
            'headers' => $header,
            $paramKey => $params,
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);

        # set service call product Id
        $option[$paramKey]['scProductId'] = self::$serviceCallProductId[$apiName];

        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option[$paramKey];
            unset($option[$paramKey]);
            $optionHasArray = true;
            $method = 'GET';
        }

        return AiApiRequestHandler::Request(
            self::$config[self::$serverType][self::$aiApi[$apiName]['baseUri']],
            $method,
            $relativeUri,
            $option,
            false,
            $optionHasArray
        );
    }

    public function licensePlateReader($params) {
        $apiName = 'licensePlateReader';
        $header = $this->header;
        $optionHasArray = false;
        array_walk_recursive($params, 'self::prepareData');
        $relativeUri = self::$aiApi[$apiName]['subUri'];
        $method = self::$aiApi[$apiName]['method'];
        $paramKey = $method == 'GET' ? 'query' : 'form_params';

        // if token is set replace it
        if(isset($params['token'])) {
            $header["_token_"] = $params['token'];
            unset($params['token']);
        }

        $option = [
            'headers' => $header,
            $paramKey => $params,
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);

        # set service call product Id
        $option[$paramKey]['scProductId'] = self::$serviceCallProductId[$apiName];

        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option[$paramKey];
            unset($option[$paramKey]);
            $optionHasArray = true;
            $method = 'GET';
        }

        return AiApiRequestHandler::Request(
            self::$config[self::$serverType][self::$aiApi[$apiName]['baseUri']],
            $method,
            $relativeUri,
            $option,
            false,
            $optionHasArray
        );
    }
}