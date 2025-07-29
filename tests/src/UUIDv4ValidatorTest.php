<?php

namespace Phramework\Validate;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @author Spafaridis Xenofon <nohponex@gmail.com>
 * @author Nikolopoulos Konstantinos <kosnikolopoulos@gmail.com>
 */
class UUIDv4ValidatorTest extends TestCase
{
    /**
     * @var UUIDv4Validator
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new UUIDv4Validator();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
    }

    public static function validateSuccessProvider(): array
    {
        //input
        return [
            'valid uuid 1' => ['20b33445-7464-41c5-a47d-c6c41e29c77d'],
            'valid uuid 2' => ['2015f512-e39c-4093-b2c6-958030b57764'],
            'valid uuid 3' => ['db3f6f4b-df60-4b7f-bf4a-8e6bc578550c'],
            'valid uuid 4' => ['e1da5d7f-2d32-4cf9-b42e-9b06817c6495'],
        ];
    }

    public static function validateFailureProvider(): array
    {
        //input
        return [
            'string number' => ['100'],
            'integer' => [5400000],
            'long string' => ['this is an invalid string if you consider it as uuid'],
            'email' => ['knikolopoulos@vivantehealth.com'],
            'invalid format' => ['asdasdasd-asdasdasdasd-asdasdasdasd-asdasdasd'],
            'too long' => ['e1da5d7f-2d32-4cf9-b42e-9b06817c6495-9b06817c6495'],
            'too short' => ['e1da5d7f-2d32-4cf9-b42e'],
        ];
    }

    public function testConstruct(): void
    {
        $validator = new UUIDv4Validator();
        $this->assertInstanceOf(UUIDv4Validator::class, $validator);
    }

    #[DataProvider('validateSuccessProvider')]
    public function testValidateSuccess(string $input): void
    {
        $return = $this->object->validate($input);

        $this->assertIsString($return->value);
        $this->assertTrue($return->status);
    }

    #[DataProvider('validateFailureProvider')]
    public function testValidateFailure(mixed $input): void
    {
        $return = $this->object->validate($input);

        $this->assertFalse($return->status);
    }

    public function testCreateFromJSON(): void
    {
        $json = '{
            "type": "UUIDv4"
        }';

        $validationObject = BaseValidator::createFromJSON($json);

        $this->assertInstanceOf(UUIDv4Validator::class, $validationObject);
    }

    public function testGetType(): void
    {
        $this->assertSame('UUIDv4', $this->object->getType());
    }
}
