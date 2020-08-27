<?php
use PHPUnit\Framework\TestCase;
use Pod\Ai\Service\AiService;
use Pod\Base\Service\BaseInfo;
use Pod\Base\Service\Exception\ValidationException;
use Pod\Base\Service\Exception\PodException;

final class ImageProcessingAuthenticationTest extends TestCase
{
    public static $AiService;
    private $token;
    public function setUp(): void
   {
        parent::setUp();
        # set serverType to SandBox or Production
        BaseInfo::initServerType(BaseInfo::SANDBOX_SERVER);
        $testData =  require __DIR__ . '/testData.php';
        $this->token = $testData['token'];

        $baseInfo = new BaseInfo();
        $baseInfo->setToken($this->token);
		self::$AiService = new AiService($baseInfo);
    }

	public function testImageProcessingAuthenticationAllParameters()
	{
		$params = [
			## ================= *Required Parameters  =================
			'image1' => 'image 1 url',
			'image2' => 'image 2 url',
			'mode' => 'easy',
			'scApiKey' => '{Put Service Call Api Key}',
			## ================= Optional Parameters  =================
			'token'     => '{Put Token}',
			//'scVoucherHash' => '['{Put Service Call Voucher Hashes}', ...]',
		];
		try {
			$result = $AiService->imageProcessingAuthentication($params);
			$this->assertFalse($result['error']);
			$this->assertEquals($result['code'], 200);
		} catch (ValidationException $e) {
			$this->fail('ValidationException: ' . $e->getErrorsAsString());
		} catch (PodException $e) {
			$error = $e->getResult();
			$this->fail('PodException: ' . $error['message']);
		}
	}

	public function testImageProcessingAuthenticationRequiredParameters()
	{
		$params = [
			## ================= *Required Parameters  =================
			'image1' => 'image 1 url',
			'image2' => 'image 2 url',
			'mode' => 'easy',
			'scApiKey' => '{Put Service Call Api Key}',
		try {
			$result = $AiService->imageProcessingAuthentication($params);
			$this->assertFalse($result['error']);
			$this->assertEquals($result['code'], 200);
		} catch (ValidationException $e) {
			$this->fail('ValidationException: ' . $e->getErrorsAsString());
		} catch (PodException $e) {
			$error = $e->getResult();
			$this->fail('PodException: ' . $error['message']);
		}
	}

	public function testImageProcessingAuthenticationValidationError()
	{
		$paramsWithoutRequired = [];
		$paramsWrongValue = [
			## ======================= *Required Parameters  ==========================
			'image1' => 123,
			'image2' => 123,
			'mode' => 123,
			'scApiKey' => 123,
			## ======================== Optional Parameters  ==========================
			'scVoucherHash' => '123',
		];
		try {
			self::$AiService->imageProcessingAuthentication($paramsWithoutRequired);
		} catch (ValidationException $e) {
			$validation = $e->getErrorsAsArray();
			$this->assertNotEmpty($validation);

			$result = $e->getResult();

			$this->assertArrayHasKey('image1', $validation);
			$this->assertEquals('The property image1 is required', $validation['image1'][0]);

			$this->assertArrayHasKey('image2', $validation);
			$this->assertEquals('The property image2 is required', $validation['image2'][0]);

			$this->assertArrayHasKey('mode', $validation);
			$this->assertEquals('The property mode is required', $validation['mode'][0]);

			$this->assertArrayHasKey('scApiKey', $validation);
			$this->assertEquals('The property scApiKey is required', $validation['scApiKey'][0]);


			$this->assertEquals(887, $result['code']);
		} catch (PodException $e) {
			$error = $e->getResult();
			$this->fail('PodException: ' . $error['message']);
		}
		try {
			self::$AiService->imageProcessingAuthentication($paramsWrongValue);
		} catch (ValidationException $e) {

			$validation = $e->getErrorsAsArray();
			$this->assertNotEmpty($validation);

			$result = $e->getResult();
			$this->assertArrayHasKey('image1', $validation);
			$this->assertEquals('Integer value found, but a string is required', $validation['image1'][1]);

			$this->assertArrayHasKey('image2', $validation);
			$this->assertEquals('Integer value found, but a string is required', $validation['image2'][1]);

			$this->assertArrayHasKey('mode', $validation);
			$this->assertEquals('Integer value found, but a string is required', $validation['mode'][1]);

			$this->assertArrayHasKey('scVoucherHash', $validation);
			$this->assertEquals('String value found, but an array is required', $validation['scVoucherHash'][0]);

			$this->assertArrayHasKey('scApiKey', $validation);
			$this->assertEquals('Integer value found, but a string is required', $validation['scApiKey'][1]);

			$this->assertEquals(887, $result['code']);
		} catch (PodException $e) {
			$error = $e->getResult();
			$this->fail('PodException: ' . $error['message']);
		}
	}

}