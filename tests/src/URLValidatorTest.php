<?php

namespace Phramework\Validate;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class URLValidatorTest extends TestCase
{

    /**
     * @var URLValidator
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new URLValidator(3, 100);
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
            'simple https' => ['https://nohponex.gr'],
            'http with path, query, fragment' => ['http://www.thmmy.gr/dir/file.php?param=ok&second=false#ok'],
            'http with ip' => ['http://127.0.0.1/app']
        ];
    }

    public static function validateFailureProvider(): array
    {
        //input
        return [
            'string number' => ['100'],
            'integer' => [540],
            'email-like' => ['nx@ma.il'],
            'email-like no dot' => ['nohponex@gmailcom'],
            'double colon' => ['http::://nohponex.gr'],
            'no scheme' => ['nohponex.gr'],
            'just domain part' => ['nohponex'],
            'scheme relative' => ['//nohponex.gr']
        ];
    }

    public function testConstruct(): void
    {
        $validator = new URLValidator();
        $this->assertInstanceOf(URLValidator::class, $validator);
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
            "type": "url",
            "minLength" : 10,
            "maxLength" : 100
        }';

        $validationObject = BaseValidator::createFromJSON($json);

        $this->assertInstanceOf(URLValidator::class, $validationObject);

        $this->assertSame(
            10,
            $validationObject->minLength
        );

        $this->assertSame(
            100,
            $validationObject->maxLength
        );
    }

    public function testGetType(): void
    {
        $this->assertSame('url', $this->object->getType());
    }
}
