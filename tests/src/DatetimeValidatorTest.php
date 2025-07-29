<?php

namespace Phramework\Validate;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DatetimeValidatorTest extends TestCase
{

    /**
     * @var DatetimeValidator
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new DatetimeValidator();
    }

    public static function validateSuccessProvider(): array
    {
        //input, expected
        return [
            'valid datetime 1' => ['2000-10-12 12:00:00'],
            'valid datetime 2' => ['2000-10-12 12:56:00']
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
            'invalid day' => ['2014-13-33'],
            'invalid minute' => ['2000-10-12 12:60:00'],
            'invalid second' => ['2000-10-12 12:56:60'],
            'invalid hour' => ['2000-10-12 25:56:00'],
            'missing seconds' => ['2000-10-12 23:56'],
            'date only' => ['2000-10-12'],
            'missing minutes and seconds' => ['2000-10-12 23']
        ];
    }

    public function testConstruct(): void
    {
        $validator = new DatetimeValidator();
        $this->assertInstanceOf(DatetimeValidator::class, $validator);
    }

    #[DataProvider('validateSuccessProvider')]
    public function testValidateSuccess(string $input): void
    {
        $return = $this->object->validate($input);

        $this->assertSame($input, $return->value);
        $this->assertTrue($return->status);
    }

    public function testFormatMinimumSuccess(): void
    {
        $validator = new DatetimeValidator(
            '2000-10-12 12:00:00'
        );

        $this->assertSame(
            '2000-10-12 12:00:01',
            $validator->parse('2000-10-12 12:00:01')
        );
    }

    public function testFormatMinimumFailure(): void
    {
        $this->expectException(\Exception::class);
        $validator = new DatetimeValidator(
            '2000-10-12 12:00:00'
        );

        $validator->parse('2000-10-11 12:00:00');
    }

    public function testFormatMinimumMaximumSuccess(): void
    {
        $validator = new DatetimeValidator(
            '2000-10-12 12:00:00',
            '2000-10-12 12:01:00'
        );

        $this->assertSame(
            '2000-10-12 12:00:01',
            $validator->parse('2000-10-12 12:00:01')
        );
    }

    public function testFormatMaximumFailure(): void
    {
        $this->expectException(\Exception::class); // Changed
        $validator = new DatetimeValidator(
            null,
            '2000-10-12 12:00:00'
        );

        $validator->parse('2000-11-12 12:00:00');
    }

    #[DataProvider('validateFailureProvider')]
    public function testValidateFailure(string $input): void
    {
        $return = $this->object->validate($input);

        $this->assertFalse($return->status);
    }

    public function testCreateFromJSON(): void
    {
        $json = '{
            "type": "date-time"
        }';

        $validationObject = BaseValidator::createFromJSON($json);

        $this->assertInstanceOf(DatetimeValidator::class, $validationObject);
    }

    public function testGetType(): void
    {
        $this->assertEquals('date-time', $this->object->getType());
    }
}
