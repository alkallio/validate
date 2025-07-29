<?php

namespace Phramework\Validate;

use PHPUnit\Framework\TestCase;
use Phramework\Exceptions\IncorrectParametersException;

class ValidateResultTest extends TestCase
{
    public function testConstruct(): void
    {
        $value = 'test-value';
        $status = true;
        $errorObject = new \Exception('test-error');

        $validateResult = new ValidateResult($value, $status, $errorObject);

        $this->assertInstanceOf(ValidateResult::class, $validateResult);
        $this->assertSame($value, $validateResult->value);
        $this->assertSame($status, $validateResult->status);
        $this->assertSame($errorObject, $validateResult->errorObject);
    }

    public function testJsonSerialize(): void
    {
        $value = 'test-value';
        $status = true;
        $errorObject = new IncorrectParametersException([
            ['type' => 'string', 'failure' => 'minLength']
        ]);

        $validateResult = new ValidateResult($value, $status, $errorObject);

        $json = json_encode($validateResult);
        $decoded = json_decode($json, true);

        $this->assertSame($value, $decoded['value']);
        $this->assertSame($status, $decoded['status']);
        $this->assertIsArray($decoded['errorObject']);
    }
}
