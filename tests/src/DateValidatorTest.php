<?php

namespace Phramework\Validate;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DateValidatorTest extends TestCase
{

    /**
     * @var DateValidator
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new DateValidator();
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
        //input, expected
        return [
            'valid date 1' => ['2000-10-12'],
            'valid date 2' => ['2000-01-02']
        ];
    }

    public static function validateFailureProvider(): array
    {
        //input
        return [
            'wrong format d-m-Y' => ['10-10-2014'],
            'just a number' => ['20'],
            'invalid month' => ['10-13-2014'],
            'invalid month Y-m-d' => ['2014-13-10'],
            'invalid day' => ['2014-01-33'],
        ];
    }

    public function testConstruct(): void
    {
        $validator = new DateValidator();
        $this->assertInstanceOf(DateValidator::class, $validator);
    }

    #[DataProvider('validateSuccessProvider')]
    public function testValidateSuccess(string $input): void
    {
        $return = $this->object->validate($input);

        $this->assertSame($input, $return->value);
        $this->assertTrue($return->status);
    }

    #[DataProvider('validateFailureProvider')]
    public function testValidateFailure(string $input): void
    {
        $return = $this->object->validate($input);

        $this->assertFalse($return->status);
    }

    public function testFormatMinimumSuccess(): void
    {
        $validator = new DateValidator(
            '2000-10-10'
        );

        $this->assertSame('2000-10-11', $validator->parse('2000-10-11'));
    }

    public function testFormatMinimumFailure(): void
    {
        $this->expectException(\Exception::class);

        $validator = new DateValidator(
            '2000-10-12'
        );

        $validator->parse('2000-10-11');
    }

    public function testFormatMinimumMaximumSuccess(): void
    {
        $validator = new DateValidator(
            '2000-10-10',
            '2000-10-12'
        );

        $this->assertSame('2000-10-11', $validator->parse('2000-10-11'));
    }

    public function testFormatMaximumFailure(): void
    {
        $this->expectException(\Exception::class);
        $validator = new DateValidator(
            null,
            '2000-10-10'
        );

        $validator->parse('2000-10-12');
    }

    public function testCreateFromJSON(): void
    {
        $json = '{
            "type": "date"
        }';

        $validationObject = BaseValidator::createFromJSON($json);

        $this->assertInstanceOf(DateValidator::class, $validationObject);
    }

    public function testGetType(): void
    {
        $this->assertEquals('date', $this->object->getType());
    }
}
