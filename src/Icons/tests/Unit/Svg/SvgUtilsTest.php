<?php

namespace Symfony\UX\Icons\Tests\Unit\Svg;

use PHPUnit\Framework\TestCase;
use Symfony\UX\Icons\Svg\SvgUtils;

class SvgUtilsTest extends TestCase
{
     /**
     * @dataProvider provideIdToName
     */
    public function testIdToName(string $id, string $name)
    {
        $this->assertSame($name, SvgUtils::idToName($id));
    }

    /**
     * @dataProvider provideInvalidIds
     */
    public function testIdToNameThrowsException(string $id)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The id "'.$id.'" is not a valid id.');

        SvgUtils::idToName($id);
    }

    /**
     * @dataProvider provideNameToId
     */
    public function testNameToId(string $name, string $id)
    {
        $this->assertEquals($id, SvgUtils::nameToId($name));
    }

    /**
     * @dataProvider provideInvalidNames
     */
    public function testNameToIdThrowsException(string $name)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The name "'.$name.'" is not a valid name.');

        SvgUtils::nameToId($name);
    }

    /**
     * @dataProvider provideValidIds
     */
    public function testIsValidIdWithValidIds(string $id): void
    {
        $this->assertTrue(SvgUtils::isValidId($id));
    }

    /**
     * @dataProvider provideInvalidIds
     */
    public function testIsValidIdWithInvalidIds(string $id): void
    {
        $this->assertFalse(SvgUtils::isValidId($id));
    }

    /**
     * @dataProvider provideValidNames
     */
    public function testIsValidNameWithValidNames(string $name): void
    {
        $this->assertTrue(SvgUtils::isValidName($name));
    }

    /**
     * @dataProvider provideInvalidNames
     */
    public function testIsValidNameWithInvalidNames(string $name): void
    {
        $this->assertFalse(SvgUtils::isValidName($name));
    }

    /**
     * @dataProvider provideInvalidIds
     */
    public function testInvalidIdToName(string $id)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The id "'.$id.'" is not a valid id.');

        $this->assertFalse(SvgUtils::isValidId($id));
        SvgUtils::idToName($id);
    }

    public static function provideIdToName(): iterable
    {
        yield from [
            ['foo', 'foo'],
            ['foo-bar', 'foo-bar'],
            ['foo-bar-baz', 'foo-bar-baz'],
            ['foo--bar', 'foo:bar'],
            ['foo--bar--baz', 'foo:bar:baz'],
            ['foo-bar--baz', 'foo-bar:baz'],
            ['foo--bar-baz', 'foo:bar-baz'],
        ];
    }

    public static function provideNameToId(): iterable
    {
        yield from [
            ['foo', 'foo'],
            ['foo-bar', 'foo-bar'],
            ['foo-bar-baz', 'foo-bar-baz'],
            ['foo:bar', 'foo--bar'],
            ['foo:bar:baz', 'foo--bar--baz'],
            ['foo-bar:baz', 'foo-bar--baz'],
            ['foo:bar-baz', 'foo--bar-baz'],
        ];
    }

    public static function provideValidIds(): iterable
    {
        yield from self::provideValidIdentifiers();
        yield from [
            ['foo--bar'],
            ['foo--bar-baz'],
            ['foo-bar--baz'],
        ];
    }

    public static function provideInvalidIds(): iterable
    {
        yield from self::provideInvalidIdentifiers();
        yield from [
            ['foo:'],
            [':foo'],
            ['foo::bar'],
        ];
    }

    public static function provideValidNames(): iterable
    {
        yield from self::provideValidIdentifiers();
        yield from [
            ['foo:bar-baz'],
            ['foo:bar'],
        ];
    }

    public static function provideInvalidNames(): iterable
    {
        yield from self::provideInvalidIdentifiers();
        yield from [
            ['foo:'],
            [':foo'],
            ['foo::bar'],
            ['foo:::bar'],
            ['foo::'],
            ['::foo'],
            ['foo--bar'],
        ];
    }

     private static function provideValidIdentifiers(): iterable
    {
        yield from [
            ['foo'],
            ['123'],
            ['foo-bar'],
            ['123-456'],
        ];
    }

    private static function provideInvalidIdentifiers(): iterable
    {
        yield from [
            [''],
            ['FOO'],
            ['&'],
            ['é'],
            ['.'],
            ['/'],
            ['foo-'],
            ['-bar'],
            ['_'],
            ['foo_bar'],
            [' '],
            ['foo '],
            [' foo'],
            ['🙂'],
        ];
    }

}
