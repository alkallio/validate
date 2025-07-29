<?php

namespace Phramework\Validate;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

class AllOfTest extends TestCase
{

    /**
     * @var AllOf
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new AllOf([
            new IntegerValidator(),
            new UnsignedIntegerValidator(),
            new NumberValidator()
        ]);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void {}

    public static function validateSuccessProvider()
    {
        //input, expected
        return  [
            [1, 1],
            [10, 10],
            [100, 100],
            [0, 0]
        ];
    }

    public static function validateFailureProvider()
    {
        //input
        return [
            [],
            [0.0000000000000000000001],
            [0.00000001],
            ['0a1'],
            ['τρθε'],
            ['positive'],
            ['negative'],
            [['abc']],
            [['abc', 10, 32]],
            [0.1],
            [-10],
            [-1]
        ];
    }

    public function testConstruct()
    {
        $validator = new AllOf([
            new StringValidator(),
            new ArrayValidator(
                1,
                10,
                new StringValidator()
            )
        ]);
        $this->assertInstanceOf(AllOf::class, $validator);
    }

    public function testConstructFailure()
    {
        $this->expectException(\Exception::class);
        $validator = new AllOf(['{"type": "integer"}']);
    }

    #[DataProvider('validateSuccessProvider')]
    public function testValidateSuccess(int $input, int $expected): void
    {
        $return = $this->object->validate($input);

        $this->assertTrue($return->status);

        $this->assertIsInt($return->value);

        $this->assertSame($expected, $return->value);
    }

    #[DataProvider('validateFailureProvider')]
    public function testValidateFailure($input = null): void
    {
        $return = $this->object->validate($input);

        $this->assertFalse($return->status);
    }

    public function testCreateFromJSON()
    {
        $json = '{
          "allOf": [
            {
              "type": "integer"
            },
            {
              "type": "number"
            }
          ]
        }';

        $validator = BaseValidator::createFromJSON($json);

        $this->assertInstanceOf(AllOf::class, $validator);

        //Set validator
        $this->object = $validator;

        $this->assertIsArray($validator->allOf);

        $this->testValidateSuccess(10, 10);
        $this->testValidateSuccess(-1, -1);

        $this->testValidateFailure(10.5);

        $this->setUp();

        return $validator;
    }

    #[Depends('testCreateFromJSON')]
    public function testToObject($validator)
    {
        $object = $validator->toObject();

        $this->assertObjectHasProperty('allOf', $object);
        $this->assertIsArray($object->allOf);

        $this->assertIsObject($object->allOf[0]);
        $this->assertIsObject($object->allOf[1]);
    }

    #[Depends('testCreateFromJSON')]
    public function testToArray($validator)
    {
        $object = $validator->toArray();

        $this->assertArrayHasKey('allOf', $object);
        $this->assertIsArray($object['allOf']);

        $this->assertIsArray($object['allOf'][0]);
        $this->assertIsArray($object['allOf'][1]);
    }

    #[Depends('testCreateFromJSON')]
    public function testToJSON($validator)
    {
        $json = $validator->toJSON();

        $this->assertIsString($json);

        $object = json_decode($json);

        $this->assertObjectHasProperty('allOf', $object);
    }

    /**
     * Validate against common enum keyword
     */
    public function testValidateCommon(): void
    {
        $validator = $this->object;

        $validator->enum = [1, 2, 3];

        $return = $validator->validate(2);
        $this->assertTrue(
            $return->status,
            'Expect true since 2 is in enum array'
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
