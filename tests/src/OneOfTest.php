<?php

namespace Phramework\Validate;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class OneOfTest extends TestCase
{

    /**
     * @var OneOf
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new OneOf([
            new IntegerValidator(-999, -1),
            new NumberValidator(10, 30),
            new UnsignedIntegerValidator(),
            new ObjectValidator(['a' => new IntegerValidator()], ['a'], true, 0, 1),
            new ArrayValidator(2, 2, new IntegerValidator()),
            new StringValidator(2, 4),
            new StringValidator(3, 6)
        ]);
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
            'unsigned integer 0' => [0, 0], //exists only in UnsignedIntegerValidator
            'signed integer -2' => [-2, -2], //exists only in IntegerValidator
            'unsigned integer 2' => [2, 2], //exists only in UnsignedIntegerValidator
            'unsigned integer 100' => [100, 100], //exists only in UnsignedIntegerValidator
            'number 13.4' => [13.4, 13.4], //exists only in Number
            'array' => [[1, 2], [1, 2]],
            'object' => [
                (object)['a' => 1],
                (object)['a' => 1]
            ],
            'string "ab"' => ['ab', 'ab'], //exists only in first string
            'string "ababab"' => ['ababab', 'ababab'] //exists only in first string
        ];
    }

    public static function validateFailureProvider(): array
    {
        //input
        return [
            'integer 10 (in two validators)' => [10], //exists in two
            'string "abc" (in two validators)' => ['abc'] //exists in both string
        ];
    }

    public function testConstruct(): void
    {
        $validator = new OneOf([
            new StringValidator(),
            new ArrayValidator(
                1,
                10,
                new StringValidator()
            )
        ]);
        $this->assertInstanceOf(OneOf::class, $validator);
    }

    public function testConstructFailure(): void
    {
        $this->expectException(\Exception::class);
        new OneOf(['{"type": "integer"}']);
    }

    #[DataProvider('validateSuccessProvider')]
    public function testValidateSuccess(mixed $input, mixed $expected): void
    {
        $return = $this->object->validate($input);

        $this->assertTrue($return->status);

        $this->assertEquals($expected, $return->value);
    }

    #[DataProvider('validateFailureProvider')]
    public function testValidateFailure(mixed $input = null): void
    {
        $return = $this->object->validate($input);

        $this->assertFalse($return->status);
    }

    public function testCreateFromJSON(): void
    {
        $json = '{
          "oneOf": [
            {
              "type": "string",
              "minLength" : 1,
              "maxLength" : 3
            },
            {
              "type": "string",
              "minLength" : 2,
              "maxLength" : 5
            },
            {
              "type": "integer"
            }
          ]
        }';

        $validator = BaseValidator::createFromJSON($json);

        $this->assertInstanceOf(OneOf::class, $validator);

        $this->assertIsArray($validator->oneOf);

        //Test success
        $this->assertTrue($validator->validate('a')->status);
        $this->assertTrue($validator->validate('abced')->status);
        $this->assertTrue($validator->validate(10)->status);

        //Test failure because it matches multiple schemas
        $this->assertFalse($validator->validate('10')->status);
        //Test failure because it matches two schemas
        $this->assertFalse($validator->validate('abc')->status);
    }

    /**
     * Validate against common enum keyword
     */
    public function testValidateCommon(): void
    {
        $validator = $this->object;

        $validator->enum = [1, 2, 3, 13.4];

        $return = $validator->validate(2);
        $this->assertTrue(
            $return->status,
            'Expect true since 2 is in enum array'
        );

        $return = $validator->validate(13.4);
        $this->assertTrue(
            $return->status,
            'Expect true since 13.4 is in enum array'
        );

        $return = $validator->validate(1.1);
        $this->assertFalse(
            $return->status,
            'Expect false since 1.1 is not in enum array'
        );

        $return = $validator->validate([10]);
        $this->assertFalse(
            $return->status,
            'Expect false since [10] is not in enum array'
        );
    }

    public function testGetType(): void
    {
        $this->assertSame(null, $this->object->getType());
    }
}
