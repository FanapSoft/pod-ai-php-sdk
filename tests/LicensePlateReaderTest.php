<?php
use PHPUnit\Framework\TestCase;
use Pod\Ai\Service\AiService;
use Pod\Base\Service\BaseInfo;
use Pod\Base\Service\Exception\ValidationException;
use Pod\Base\Service\Exception\PodException;

final class LicensePlateReaderTest extends TestCase
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

	public function testLicensePlateReaderAllParameters()
	{
		$params = [
			## ================= *Required Parameters  =================
			'image' => '{Put image url}',
			'scApiKey' => '{Put Service Call Api Key}',
			## ================= Optional Parameters  =================
			'isCrop' => 'true/false',
			'token'     => '{Put Token}',
			//'scVoucherHash' => '['{Put Service Call Voucher Hashes}', ...]',
		];
		try {
			$result = $AiService->licensePlateReader($params);
			$this->assertFalse($result['error']);
			$this->assertEquals($result['code'], 200);
		} catch (ValidationException $e) {
			$this->fail('ValidationException: ' . $e->getErrorsAsString());
		} catch (PodException $e) {
			$error = $e->getResult();
			$this->fail('PodException: ' . $error['message']);
		}
	}

	public function testLicensePlateReaderRequiredParameters()
	{
		$params = [
			## ================= *Required Parameters  =================
			'image' => '{Put image url}',
			'scApiKey' => '{Put Service Call Api Key}',
		try {
			$result = $AiService->licensePlateReader($params);
			$this->assertFalse($result['error']);
			$this->assertEquals($result['code'], 200);
		} catch (ValidationException $e) {
			$this->fail('ValidationException: ' . $e->getErrorsAsString());
		} catch (PodException $e) {
			$error = $e->getResult();
			$this->fail('PodException: ' . $error['message']);
		}
	}

	public function testLicensePlateReaderValidationError()
	{
		$paramsWithoutRequired = [];
		$paramsWrongValue = [
			## ======================= *Required Parameters  ==========================
			'image' => 123,
			'scApiKey' => 123,
			## ======================== Optional Parameters  ==========================
			'isCrop' => 123,
			'scVoucherHash' => '123',
		];
		try {
			self::$AiService->licensePlateReader($paramsWithoutRequired);
		} catch (ValidationException $e) {
			$validation = $e->getErrorsAsArray();
			$this->assertNotEmpty($validation);

			$result = $e->getResult();

			$this->assertArrayHasKey('image', $validation);
			$this->assertEquals('The property image is required', $validation['image'][0]);

			$this->assertArrayHasKey('scApiKey', $validation);
			$this->assertEquals('The property scApiKey is required', $validation['scApiKey'][0]);


			$this->assertEquals(887, $result['code']);
		} catch (PodException $e) {
			$error = $e->getResult();
			$this->fail('PodException: ' . $error['message']);
		}
		try {
			self::$AiService->licensePlateReader($paramsWrongValue);
		} catch (ValidationException $e) {

			$validation = $e->getErrorsAsArray();
			$this->assertNotEmpty($validation);

			$result = $e->getResult();
			$this->assertArrayHasKey('image', $validation);
			$this->assertEquals('Integer value found, but a string is required', $validation['image'][1]);

			$this->assertArrayHasKey('isCrop', $validation);
			$this->assertEquals('Integer value found, but a string is required', $validation['isCrop'][0]);

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