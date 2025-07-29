<?php

namespace Phramework\Validate;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ArrayValidatorTest extends TestCase
{

    /**
     * @var ArrayValidator
     */
    protected $object;

    /**
     * Sets up the fixture
     */
    protected function setUp(): void
    {
        $this->object = new ArrayValidator(1, 3);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void {}

    public function testConstruct(): void
    {
        $validator = new ArrayValidator(
            1,
            3,
            new IntegerValidator(),
            true,
            false
        );
        $this->assertInstanceOf(ArrayValidator::class, $validator);
    }

    public function testConstructFailure1(): void
    {
        $this->expectException(\Exception::class);
        new ArrayValidator(
            1,
            3,
            []
        );
    }

    public function testConstructFailure2(): void
    {
        $this->expectException(\Exception::class);
        new ArrayValidator(
            'a'
        );
    }

    public function testConstructFailure3(): void
    {
        $this->expectException(\Exception::class);
        new ArrayValidator(
            3,
            1
        );
    }

    public function testConstructFailure4(): void
    {
        $this->expectException(\Exception::class);
        new ArrayValidator(
            1,
            3,
            new \stdClass()
        );
    }

    public static function validateSuccessProvider(): array
    {
        //input
        return [
            [[2, '3']],
            [['2', '3']],
            [[1, 2, 3]],
            [[1,2]]
        ];
    }

    public static function validateFailureProvider(): array
    {
        //input
        return [
            'not an array' => [1],
            '0 items' => [[]],
            '>3 items' => [[1, 2, 3, 4, 5, 6]]
        ];
    }

    #[DataProvider('validateSuccessProvider')]
    public function testValidateSuccess(array $input): void
    {
        $return = $this->object->validate($input);

        $this->assertIsArray($return->value);
        $this->assertTrue($return->status);
    }

    #[DataProvider('validateFailureProvider')]
    public function testValidateFailure(mixed $input): void
    {
        $return = $this->object->validate($input);

        $this->assertFalse($return->status);
        $this->assertInstanceOf(
            \Phramework\Exceptions\IncorrectParametersException::class,
            $return->errorObject
        );
    }

    public function testValidateUnique(): void
    {
        $validator = new ArrayValidator(
            1,
            2,
            new EnumValidator(['one', 'two', 'three', 'four'], true),
            true
        );

        $return = $validator->validate(['one', 'one']);

        $this->assertFalse($return->status);

        $return = $validator->validate('one');

        $this->assertFalse($return->status);
    }

    public function testValidateUniqueObject(): void
    {
        $validator = new ArrayValidator(
            1,
            2,
            new ObjectValidator(
                (object) [
                    'value' => new EnumValidator(['1', '2'], true),
                ],
                ['value'],
                false
            ),
            true
        );

        $return = $validator->validate([
            (object) ['value' => '1'],
            (object) ['value' => '1'],
            (object) ['value' => '2'],
        ]);

        $this->assertFalse($return->status);
    }

    public function testValidateItems(): void
    {
        $validator = new ArrayValidator(
            1,
            2,
            new EnumValidator(['one', 'two', 'three', 'four'], true),
            true,
            false
        );

        $this->assertInstanceOf(BaseValidator::class, $validator->items);
        $this->assertInstanceOf(EnumValidator::class, $validator->items);

        $return = $validator->validate(['one', 'two']);

        $this->assertTrue($return->status);

        $return = $validator->validate(['four']);
        $this->assertTrue($return->status);

        $return = $validator->validate(['one', 'two', 'four']);
        $this->assertFalse($return->status, 'Since we have maxItems "2"');

        $return = $validator->validate(['one', 'not a valid value']);
        $this->assertFalse($return->status);
    }

    public function testCreateFromJSON(): void
    {
        $json = '{
          "type": "array",
          "minItems": 1,
          "maxItems": 2,
          "title": "demo array",
          "description": "Pick 1 or 2 options",
          "additionalItems": false,
          "items": {
            "type": "enum",
            "enum": [
              "one",
              "two",
              "three",
              "four"
            ],
            "validateType": true
          },
          "uniqueItems": true
        }';

        $validator = BaseValidator::createFromJSON($json);

        $this->assertInstanceOf(ArrayValidator::class, $validator);

        $this->assertSame(
            1,
            $validator->minItems
        );
        $this->assertSame(
            2,
            $validator->maxItems
        );

        $this->assertInstanceOf(BaseValidator::class, $validator->items);
        $this->assertInstanceOf(EnumValidator::class, $validator->items);

        $return = $validator->validate(['one', 'four']);
        $this->assertTrue($return->status);

        $return = $validator->validate(['one', 'two', 'three']);
        $this->assertFalse($return->status);

        $return = $validator->validate(['one', 'bad value']);
        $this->assertFalse($return->status);
    }

    public function testGetType(): void
    {
        $this->assertEquals('array', $this->object->getType());
    }

    public function testEquals(): void
    {
        $this->assertTrue(
            ArrayValidator::equals(
                [0, 1],
                [0, 1]
            )
        );

        $this->assertTrue(
            ArrayValidator::equals(
                [0, 1],
                [1, 0]
            )
        );


        $this->assertFalse(
            ArrayValidator::equals(
                [0, 1],
                [0, 4]
            )
        );

        $this->assertFalse(
            ArrayValidator::equals(
                [0, 1],
                [0, 1, 3]
            )
        );

        $this->assertFalse(
            ArrayValidator::equals(
                [0, 1],
                []
            )
        );

        $this->assertFalse(
            ArrayValidator::equals(
                [0, 1],
                [0]
            )
        );
    }

    public function testSetValidateCallback(): void
    {
        $value = [1, 2];

        $validator = (new ArrayValidator())
            ->setValidateCallback(
                /**
                 * @param ValidateResult $validateResult
                 * @param BaseValidator $validator
                 * @return ValidateResult
                 */
                function ($validateResult, $validator) use ($value) {
                    $validateResult->value = $value;

                    return $validateResult;
                }
            );

        $this->assertInstanceOf(ArrayValidator::class, $validator);

        $parsed = $validator->parse(['a', 'b', 'c']);

        $this->assertIsArray($parsed);
        $this->assertEquals($value, $parsed);
    }
}
