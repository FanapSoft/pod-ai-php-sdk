<?php
use PHPUnit\Framework\TestCase;
use Pod\Ai\Service\AiService;
use Pod\Base\Service\BaseInfo;
use Pod\Base\Service\Exception\ValidationException;
use Pod\Base\Service\Exception\PodException;

final class NLUBankingTest extends TestCase
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

	public function testNLUBankingAllParameters()
	{
		$params = [
			## ================= *Required Parameters  =================
			'text' => '  از کارت  "شماره کارت مبدا" مبلغ 3000 به کارت"شماره کارت مقصد"',
			'scApiKey' => '{Put Service Call Api Key}',
			## ================= Optional Parameters  =================
			'token'     => '{Put Token}',
			
			//'scVoucherHash' => '['{Put Service Call Voucher Hashes}', ...]',
		];
		try {
			$result = $AiService->NLUBanking($params);
			$this->assertFalse($result['error']);
			$this->assertEquals($result['code'], 200);
		} catch (ValidationException $e) {
			$this->fail('ValidationException: ' . $e->getErrorsAsString());
		} catch (PodException $e) {
			$error = $e->getResult();
			$this->fail('PodException: ' . $error['message']);
		}
	}

	public function testNLUBankingRequiredParameters()
	{
		$params = [
			## ================= *Required Parameters  =================
			'text' => 'put text',
			'scApiKey' => '{Put Service Call Api Key}',
		try {
			$result = $AiService->NLUBanking($params);
			$this->assertFalse($result['error']);
			$this->assertEquals($result['code'], 200);
		} catch (ValidationException $e) {
			$this->fail('ValidationException: ' . $e->getErrorsAsString());
		} catch (PodException $e) {
			$error = $e->getResult();
			$this->fail('PodException: ' . $error['message']);
		}
	}

	public function testNLUBankingValidationError()
	{
		$paramsWithoutRequired = [];
		$paramsWrongValue = [
			## ======================= *Required Parameters  ==========================
			'text' => 123,
			'scApiKey' => 123,
			## ======================== Optional Parameters  ==========================
			'scVoucherHash' => '123',
		];
		try {
			self::$AiService->NLUBanking($paramsWithoutRequired);
		} catch (ValidationException $e) {
			$validation = $e->getErrorsAsArray();
			$this->assertNotEmpty($validation);

			$result = $e->getResult();

			$this->assertArrayHasKey('text', $validation);
			$this->assertEquals('The property text is required', $validation['text'][0]);

			$this->assertArrayHasKey('scApiKey', $validation);
			$this->assertEquals('The property scApiKey is required', $validation['scApiKey'][0]);


			$this->assertEquals(887, $result['code']);
		} catch (PodException $e) {
			$error = $e->getResult();
			$this->fail('PodException: ' . $error['message']);
		}
		try {
			self::$AiService->NLUBanking($paramsWrongValue);
		} catch (ValidationException $e) {

			$validation = $e->getErrorsAsArray();
			$this->assertNotEmpty($validation);

			$result = $e->getResult();
			$this->assertArrayHasKey('text', $validation);
			$this->assertEquals('Integer value found, but a string is required', $validation['text'][1]);

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