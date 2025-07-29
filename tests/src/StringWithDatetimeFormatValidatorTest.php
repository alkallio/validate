<?php

namespace Phramework\Validate;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class StringWithDatetimeFormatValidatorTest extends TestCase
{

    /**
     * @var StringValidator
     */
    protected $object;

    protected function setUp(): void
    {
        $this->object = new StringValidator(
            20,
            35,
            null,
            false,
            'date-time'
        );

        $this->object->setFormatMinimum('2018-11-14T14:30:26+02:00');
        $this->object->setFormatMaximum('2020-11-14T14:30:26+02:00');
    }

    public static function validateSuccessProvider(): array
    {
        //input, expected
        return [
            'valid with colon in offset' => ['2019-11-14T14:30:26+02:00', '2019-11-14T14:30:26+02:00'],
            'valid without colon in offset' => ['2019-11-14T14:30:26+0200', '2019-11-14T14:30:26+0200'],
            'valid with leap second' => ['2019-11-14T14:30:60+02:00', '2019-11-14T14:30:60+02:00'],
            'valid with leap second and Z offset' => ['2019-11-14T14:30:60+00:00', '2019-11-14T14:30:60+00:00'],
            'valid with negative offset' => ['2019-11-14T14:30:45-05:00', '2019-11-14T14:30:45-05:00'],
            'valid midnight' => ['2019-11-14T00:00:00-05:00', '2019-11-14T00:00:00-05:00'],
            'valid Z offset' => ['2019-11-14T14:30:26Z', '2019-11-14T14:30:26Z'],
            'valid at minimum' => ['2018-11-14T14:30:26+02:00', '2018-11-14T14:30:26+02:00'],
            'valid at maximum' => ['2020-11-14T14:30:26+02:00', '2020-11-14T14:30:26+02:00'],
            'valid leap year date' => ['2020-02-28T00:30:26+02:00', '2020-02-28T00:30:26+02:00'],
            'valid short offset' => ['2020-02-28T00:30:26+02', '2020-02-28T00:30:26+02'],
        ];
    }

    public static function validateFailureProvider(): array
    {
        //input
        return [
            'Z and offset' => ['2019-11-14T14:30:26Z+02:00', 'date-time'],
            'too short' => ['2019-11-14T', 'minLength'],
            'space instead of T' => ['2019-11-14 14:30:26+02:00', 'date-time'],
            'random suffix' => ['2019-11-14T14:30:26+02:00random', 'date-time'],
            'too long' => ['2019-11-14T14:30:26.123543333654+02:00', 'maxLength'],
            'invalid day' => ['2019-02-30T01:01:01Z', 'date-time'],
            'invalid month' => ['2019-13-30T01:01:01Z', 'date-time'],
            'random string' => ['asdfasdf', 'minLength'],
            'uuid like' => ['a708465e-8fec-4508-b159-46d545de3b', 'date-time'],
            'invalid offset hour' => ['2019-11-14T00:00:00-99:00', 'date-time'],
            'invalid minute' => ['2019-11-14T00:80:00-00:00', 'date-time'],
            'before minimum' => ['2017-11-14T14:30:26+02:00', 'formatMinimum'],
            'after maximum' => ['2023-11-14T14:30:26+02:00', 'formatMaximum'],
            'double colon in offset' => ['2019-11-14T14:30:45-05::00', 'date-time'],
            'trailing colon in offset' => ['2020-02-28T00:30:26+02:', 'date-time'],
        ];
    }

    #[DataProvider('validateSuccessProvider')]
    public function testValidateSuccess(
        string $input,
        string $expected
    ): void {
        $return = $this->object->validate($input);

        $this->assertIsString($return->value);
        $this->assertSame($expected, $return->value);
        $this->assertTrue($return->status);
    }

    #[DataProvider('validateFailureProvider')]
    public function testValidateFailure(
        string $input,
        string $failure
    ): void {
        $return = $this->object->validate($input);

        $this->assertFalse($return->status);

        $expectedError =
            [
                'type' => 'string',
                'failure' => $failure
            ];

        $this->assertContains($expectedError, $return->errorObject->getParameters());
    }

    public function testCreateFromJSON(): void
    {
        $json = '{
          "type": "string",
          "order": "5",
          "format": "date-time",
          "formatMinimum": "2019-10-14T22:35:38+00:00",
          "formatMaximum": "2019-12-12T22:25:38+00:00"
        }';

        $validator = StringValidator::createFromJSON($json);

        $this->assertInstanceOf(StringValidator::class, $validator);

        $this->assertSame(
            '2019-10-14T22:35:38+00:00',
            $validator->formatMinimum
        );

        $this->assertSame(
            '2019-12-12T22:25:38+00:00',
            $validator->formatMaximum
        );

        $return = $validator->validate('2019-09-14T22:35:38+00:00');

        $this->assertFalse($return->status);

        $expectedError =
            [
                'type' => 'string',
                'failure' => 'formatMinimum'
            ];

        $this->assertContains($expectedError, $return->errorObject->getParameters());

        $return = $validator->validate('2019-12-14T22:45:38+00:00');

        $this->assertFalse($return->status);

        $expectedError =
            [
                'type' => 'string',
                'failure' => 'formatMaximum'
            ];

        $this->assertContains($expectedError, $return->errorObject->getParameters());

        $return = $validator->validate('2019-12-12T22:25:38+00:00');

        $this->assertTrue($return->status);
        $this->assertSame('2019-12-12T22:25:38+00:00', $return->value);

        $return = $validator->validate('2019-12-11T22:25:38+00:00');

        $this->assertTrue($return->status);
        $this->assertSame('2019-12-11T22:25:38+00:00', $return->value);
    }

    public function testGetType(): void
    {
        $this->assertSame('string', $this->object->getType());
    }
}
