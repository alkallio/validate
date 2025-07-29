<?php

namespace Phramework\Validate;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AnyOfTest extends TestCase
{

    /**
     * @var AnyOf
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new AnyOf([
            new IntegerValidator(),
            new ArrayValidator(
                1,
                10,
                new IntegerValidator()
            )
        ]);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
    }

    public static function validateSuccessProvider()
    {
        //input, expected
        return [
            [1, 1],
            [10, 10],
            [[10], [10]],
            [[10, 100, 32], [10, 100, 32]],
            [[10, 40], [10, 40]]
        ];
    }

    public static function validateFailureProvider()
    {
        //input
        return [
            [],
            ['0a1'],
            ['τρθε'],
            ['positive'],
            ['negative'],
            [['abc']],
            [['abc', 10, 32]],
            [null],
            [[]], //expectes arrays with at least one item (minItems)
            [[null]],
            [10.4]
        ];
    }

    public function testConstruct(): void
    {
        $validator = new AnyOf([
            new StringValidator(),
            new ArrayValidator(
                1,
                10,
                new StringValidator()
            )
        ]);
        $this->assertInstanceOf(AnyOf::class, $validator);
    }

    public function testConstructFailure(): void
    {
        $this->expectException(\Exception::class);
        new AnyOf(['{"type": "integer"}']);
    }

    #[DataProvider('validateSuccessProvider')]
    public function testValidateSuccess($input, $expected): void
    {
        $return = $this->object->validate($input);

        $this->assertTrue($return->status);

        if (is_array($return->value)) {
            foreach ($return->value as $values) {
                $this->assertIsInt($values);
            }
        } else {
            $this->assertIsInt($return->value);
        }

        $this->assertEquals($expected, $return->value);
    }

    public function testValidateSuccessFailureTypes(): void
    {
        //any

        $validator = new AnyOf([
            new StringValidator(),
            new IntegerValidator()
        ]);

        $return = $validator->validate([1]);

        $parameters = $return->errorObject->getParameters();

        $this->assertEquals('anyOf', $parameters[0]['failure']);

        //all

        $validator = new AllOf([
            new StringValidator(),
            new IntegerValidator()
        ]);

        $return = $validator->validate([1]);

        $parameters = $return->errorObject->getParameters();

        $this->assertEquals('allOf', $parameters[0]['failure']);

        //one

        $validator = new OneOf([
            new StringValidator(),
            new IntegerValidator()
        ]);

        $return = $validator->validate([1]);

        $parameters = $return->errorObject->getParameters();

        $this->assertEquals('oneOf', $parameters[0]['failure']);
    }

    #[DataProvider('validateFailureProvider')]
    public function testValidateFailure($input = null): void
    {
        $return = $this->object->validate($input);

        $this->assertFalse($return->status);
    }

    public function testCreateFromObject(): void
    {
        $object = (object)json_decode('{
          "anyOf": [
            {
              "type": "integer"
            },
            {
              "type": "array",
              "items": {
                "type": "integer"
              }
            }
          ]
        }');

        $validator = BaseValidator::createFromObject($object);

        $this->assertInstanceOf(AnyOf::class, $validator);
        $this->assertIsArray($validator->anyOf);
    }
    public function testCreateFromJSON(): void
    {
        $json = '{
          "anyOf": [
            {
              "type": "integer"
            },
            {
              "type": "array",
              "items": {
                "type": "integer"
              }
            }
          ]
        }';

        $validator = BaseValidator::createFromJSON($json);

        $this->assertInstanceOf(AnyOf::class, $validator);
        $this->assertIsArray($validator->anyOf);

        //Test success
        $return = $validator->validate(10);
        $this->assertTrue($return->status);
        $this->assertSame(10, $return->value);

        $return = $validator->validate([10, 20]);
        $this->assertTrue($return->status);
        $this->assertEquals([10, 20], $return->value);

        //Test failure
        $this->assertFalse($validator->validate(10.5)->status);
        $this->assertFalse($validator->validate('null')->status);
    }

    /**
     * Validate against common enum keyword
     */
    public function testValidateCommon(): void
    {
        $validator = $this->object;

        $validator->enum = [1, 2, [10, 100]];

        $return = $validator->validate(2);
        $this->assertTrue(
            $return->status,
            'Expect true since 2 is in enum array'
        );

        $return = $validator->validate([10, 100]);
        $this->assertTrue(
            $return->status,
            'Expect true since [10, 100] is in enum array'
        );

        $return = $validator->validate(10);
        $this->assertFalse(
            $return->status,
            'Expect false since 10 is not in enum array'
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
