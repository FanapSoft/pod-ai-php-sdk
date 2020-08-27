<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$seriveName = "ai"; //TODO::## FILL THIS FOR EVERY PROJECT 
$serviceClass = ucfirst($seriveName) . 'Service';
$projectFolderName = 'pod-'.$seriveName.'-sdk'; 
$schema = json_decode(file_get_contents(__DIR__ . "/config/validationSchema.json"), true);
$apiConfig = require(__DIR__ ."/config/apiConfig.php");
    
// echo $context;
    foreach($schema as $functionName => $val){
        $text = "<?php" . PHP_EOL .
    "use PHPUnit\Framework\TestCase;" . PHP_EOL .
    "use Pod\\".ucfirst($seriveName)."\\Service\\$serviceClass;" . PHP_EOL .
    "use Pod\\Base\\Service\\BaseInfo;" . PHP_EOL .
    "use Pod\\Base\\Service\\Exception\\ValidationException;" . PHP_EOL .
    "use Pod\\Base\\Service\\Exception\\PodException;" . PHP_EOL . PHP_EOL .
    "final class " . ucfirst($functionName) . "Test extends TestCase" . PHP_EOL .
    "{" . PHP_EOL .
    "    public static \$$serviceClass;" . PHP_EOL .
    "    private \$token;" . PHP_EOL .
    "    public function setUp(): void" . PHP_EOL .
    "   {" . PHP_EOL .
    "        parent::setUp();" . PHP_EOL .
    "        # set serverType to SandBox or Production" . PHP_EOL .
    "        BaseInfo::initServerType(BaseInfo::SANDBOX_SERVER);" . PHP_EOL .
    "        \$testData =  require __DIR__ . '/testData.php';" . PHP_EOL .
    "        \$this->token = \$testData['token'];" . PHP_EOL . PHP_EOL .      
    "        \$baseInfo = new BaseInfo();" . PHP_EOL .
    "        \$baseInfo->setToken(\$this->token);" . PHP_EOL .
    "\t\tself::\$$serviceClass = new $serviceClass(\$baseInfo);" . PHP_EOL .
    "    }" . PHP_EOL. PHP_EOL ;

        //TODO::## CHECK THIS LINES FOR EVERY PROJECT 
        // $paramKey = 'query' ;
        // $paramKey = $method == 'GET' ? 'query' : 'form_params';
        $paramKey = $apiConfig[$functionName]['method'] == 'POST' ? 'form_params' : 'query' ;
        $text.= "\tpublic function test".ucfirst($functionName)."AllParameters()" . PHP_EOL .
        "\t{" . PHP_EOL .
            "\t\t\$params = [".PHP_EOL.
                "\t\t\t## ================= *Required Parameters  =================".PHP_EOL;
                $requiredFields = '';
                $requiredWrongValue = '';
                $optionalWrongValue = '';
                $wrongValue = null;
                $requiredAssertation = '';
                $wrongValueAssertation = '';
                $reqList = isset($val[$paramKey]["required"]) ? $val[$paramKey]["required"] : (isset($val[$paramKey]['oneOf'][0]['required'])? $val[$paramKey]['oneOf'][0]['required'] : []);
                foreach ($reqList as $required){
                    $requiredFields.= "\t\t\t'$required' => '',".PHP_EOL;
                }
                $text.= $requiredFields;
                $text.= "\t\t\t## ================= Optional Parameters  =================".PHP_EOL;
                $optionalFields = '';
                foreach ($val[$paramKey]["properties"] as $field => $fv){
                    $index = array_search($field, $reqList) === false ? 0 : 1;
                    $fv['type'] = !isset($fv['type']) ? $fv['type']['oneOf'][0]['type'] : $fv['type'];
                    $fv['type'] = is_array($fv['type'])? $fv['type'][0] : $fv['type'];
                    // if (!isset($fv['type'])){
                    //     echo $functionName.' '.$fv;die;
                    // }
                    switch ($fv['type']){
                        // string or boolean
                        case 'string':
                            $wrongValueAssertation.= "\t\t\t\$this->assertArrayHasKey('$field', \$validation);".PHP_EOL.
                            "\t\t\t\$this->assertEquals('Integer value found, but a string is required', \$validation['$field'][$index]);".PHP_EOL.PHP_EOL;
                            $wrongValue = 123;
                        break;
                        case 'boolean': 
                            $wrongValue = 123;
                            $wrongValueAssertation.= "\t\t\t\$this->assertArrayHasKey('$field', \$validation);".PHP_EOL.
                            "\t\t\t\$this->assertEquals('Integer value found, but a boolean is required', \$validation['$field'][$index]);".PHP_EOL.PHP_EOL;
                        break;
                        // number or integer or array
                        case 'number': 
                            $wrongValue = "'123'";
                            $wrongValueAssertation.= "\t\t\t\$this->assertArrayHasKey('$field', \$validation);".PHP_EOL.
                            "\t\t\t\$this->assertEquals('String value found, but a number is required', \$validation['$field'][$index]);".PHP_EOL.PHP_EOL;
                        break;
                        case 'integer':
                            $wrongValue = "'123'";
                            $wrongValueAssertation.= "\t\t\t\$this->assertArrayHasKey('$field', \$validation);".PHP_EOL.
                            "\t\t\t\$this->assertEquals('String value found, but an integer is required', \$validation['$field'][$index]);".PHP_EOL.PHP_EOL;
                        break;
                        case 'array':
                            $wrongValue = "'123'";
                            $wrongValueAssertation.= "\t\t\t\$this->assertArrayHasKey('$field', \$validation);".PHP_EOL.
                            "\t\t\t\$this->assertEquals('String value found, but an array is required', \$validation['$field'][$index]);".PHP_EOL.PHP_EOL;
 
                    }
                    
                    if(array_search($field, $reqList) === false){
                        $optionalFields.= "\t\t\t'$field' => '',".PHP_EOL;
                        $optionalWrongValue.= "\t\t\t'$field' => $wrongValue,".PHP_EOL;
                    }else {
                        $requiredWrongValue.= "\t\t\t'$field' => $wrongValue,".PHP_EOL;
                        $requiredAssertation.= "\t\t\t\$this->assertArrayHasKey('$field', \$validation);".PHP_EOL.
                        "\t\t\t\$this->assertEquals('The property $field is required', \$validation['$field'][0]);".PHP_EOL.PHP_EOL;
                    }
                }
                $text.= $optionalFields;
                $text.= 
                //TODO::## CHECK THIS LINES FOR EVERY PROJECT 
                "\t\t\t'token'     => '{Put Token}',".PHP_EOL.
                "\t\t\t//'scApiKey' => '{Put Service Call Api Key}',".PHP_EOL.
                "\t\t\t//'scVoucherHash' => '['{Put Service Call Voucher Hashes}', ...]',".PHP_EOL.
                "\t\t];".PHP_EOL.
                "\t\ttry {".PHP_EOL.
                    "\t\t\t\$result = \$$serviceClass->$functionName(\$params);".PHP_EOL.
                    "\t\t\t\$this->assertFalse(\$result['error']);".PHP_EOL.
                    "\t\t\t\$this->assertEquals(\$result['code'], 200);".PHP_EOL.
                "\t\t} catch (ValidationException \$e) {".PHP_EOL.
                    "\t\t\t\$this->fail('ValidationException: ' . \$e->getErrorsAsString());".PHP_EOL.
                "\t\t} catch (PodException \$e) {".PHP_EOL.
                    "\t\t\t\$error = \$e->getResult();".PHP_EOL.
                    "\t\t\t\$this->fail('PodException: ' . \$error['message']);".PHP_EOL.
                "\t\t}".PHP_EOL.
            "\t}".PHP_EOL.PHP_EOL. 
            "\tpublic function test".ucfirst($functionName)."RequiredParameters()" . PHP_EOL .
            "\t{" . PHP_EOL .
                "\t\t\$params = [".PHP_EOL.
                    "\t\t\t## ================= *Required Parameters  =================".PHP_EOL;
                    $text.= $requiredFields;
                    
                    $text.= "\t\ttry {".PHP_EOL.
                        "\t\t\t\$result = \$$serviceClass->$functionName(\$params);".PHP_EOL.
                        "\t\t\t\$this->assertFalse(\$result['error']);".PHP_EOL.
                        "\t\t\t\$this->assertEquals(\$result['code'], 200);".PHP_EOL.
                    "\t\t} catch (ValidationException \$e) {".PHP_EOL.
                        "\t\t\t\$this->fail('ValidationException: ' . \$e->getErrorsAsString());".PHP_EOL.
                    "\t\t} catch (PodException \$e) {".PHP_EOL.
                        "\t\t\t\$error = \$e->getResult();".PHP_EOL.
                        "\t\t\t\$this->fail('PodException: ' . \$error['message']);".PHP_EOL.
                    "\t\t}".PHP_EOL.
                    "\t}".PHP_EOL.PHP_EOL. 
                    "\tpublic function test".ucfirst($functionName)."ValidationError()".PHP_EOL.
                    "\t{".PHP_EOL.
                        "\t\t\$paramsWithoutRequired = [];".PHP_EOL.
                        "\t\t\$paramsWrongValue = [".PHP_EOL.
                           "\t\t\t## ======================= *Required Parameters  ==========================".PHP_EOL.
                            $requiredWrongValue.
                           "\t\t\t## ======================== Optional Parameters  ==========================".PHP_EOL.
                            $optionalWrongValue.
                        "\t\t];".PHP_EOL.
                        "\t\ttry {".PHP_EOL.
                            "\t\t\tself::\$$serviceClass->$functionName(\$paramsWithoutRequired);".PHP_EOL.
                        "\t\t} catch (ValidationException \$e) {".PHP_EOL.
                            "\t\t\t\$validation = \$e->getErrorsAsArray();".PHP_EOL.
                            "\t\t\t\$this->assertNotEmpty(\$validation);".PHP_EOL.PHP_EOL.
                            "\t\t\t\$result = \$e->getResult();".PHP_EOL.PHP_EOL.
                            $requiredAssertation.PHP_EOL.
                            "\t\t\t\$this->assertEquals(887, \$result['code']);".PHP_EOL.
                        "\t\t} catch (PodException \$e) {".PHP_EOL.
                            "\t\t\t\$error = \$e->getResult();".PHP_EOL.
                            "\t\t\t\$this->fail('PodException: ' . \$error['message']);".PHP_EOL.
                        "\t\t}".PHP_EOL.
                        "\t\ttry {".PHP_EOL.
                            "\t\t\tself::\$$serviceClass->$functionName(\$paramsWrongValue);".PHP_EOL.
                        "\t\t} catch (ValidationException \$e) {".PHP_EOL.PHP_EOL.
                
                            "\t\t\t\$validation = \$e->getErrorsAsArray();".PHP_EOL.
                            "\t\t\t\$this->assertNotEmpty(\$validation);".PHP_EOL.PHP_EOL.
            
                            "\t\t\t\$result = \$e->getResult();".PHP_EOL.
                            $wrongValueAssertation.
                            "\t\t\t\$this->assertEquals(887, \$result['code']);".PHP_EOL.
                        "\t\t} catch (PodException \$e) {".PHP_EOL.
                            "\t\t\t\$error = \$e->getResult();".PHP_EOL.
                            "\t\t\t\$this->fail('PodException: ' . \$error['message']);".PHP_EOL.
                        "\t\t}".PHP_EOL.
                    "\t}".PHP_EOL.PHP_EOL.
                    "}"; 
                file_put_contents( 'tests/'.ucfirst($functionName) . "Test.php", $text); 
    }
?>

