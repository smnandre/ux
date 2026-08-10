<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\UX\TwigComponent\Twig\TwigPreLexer;
use Twig\Error\SyntaxError;

final class TwigPreLexerNamespaceTest extends TestCase
{
    /**
     * The set handed to the lexer in a real application: reserved namespaces plus a
     * few third-party ones.
     */
    private const NAMESPACES = [
        'ux' => 'UX',
        'sf' => 'UX',
        'symfony' => 'UX',
        'ea' => 'EasyAdminBundle',
        'admin' => 'Admin',
        'app' => '',
    ];

    private function lex(string $input, ?array $namespaces = null): string
    {
        return new TwigPreLexer(1, $namespaces ?? self::NAMESPACES)->preLexComponents($input);
    }

    // ----------------------------------------------------------------------
    // Backward compatibility
    // ----------------------------------------------------------------------

    public function testTheLexerAloneOnlyKnowsTwig()
    {
        $this->assertSame("{{ component('Alert') }}", new TwigPreLexer()->preLexComponents('<twig:Alert />'));
        $this->assertSame('<ux:icon />', new TwigPreLexer()->preLexComponents('<ux:icon />'));
    }

    #[DataProvider('provideUxNamespaceTests')]
    public function testTheUxNamespaceAsTheExtensionDeclaresIt(string $input, string $expected)
    {
        // exactly what TwigComponentExtension hands to the lexer service
        $lexer = new TwigPreLexer(1, ['ux' => 'ux']);

        $this->assertSame($expected, $lexer->preLexComponents($input));
    }

    public static function provideUxNamespaceTests(): iterable
    {
        yield 'icon' => ['<ux:icon name="check" />', "{{ component('ux:icon', { name: 'check' }) }}"];
        yield 'map' => ['<ux:map zoom="4" />', "{{ component('ux:map', { zoom: '4' }) }}"];
        yield 'pagination' => ['<ux:pagination />', "{{ component('ux:pagination') }}"];
        yield 'capitalized name' => ['<ux:Icon />', "{{ component('ux:Icon') }}"];
        yield 'twig still works' => ['<twig:Alert />', "{{ component('Alert') }}"];
        yield 'twig needs no declaring' => ['<twig:Card>x</twig:Card>', "{% component 'Card' %}{% block content %}x{% endblock %}{% endcomponent %}"];
        yield 'unknown namespace untouched' => ['<app:Alert />', '<app:Alert />'];
        yield 'sf is not a namespace' => ['<sf:Icon />', '<sf:Icon />'];
        yield 'symfony is not a namespace' => ['<symfony:Icon />', '<symfony:Icon />'];
    }

    #[DataProvider('provideUnregisteredNamespaceTests')]
    public function testUnregisteredNamespacesArePassedThrough(string $input)
    {
        $this->assertSame($input, $this->lex($input));
    }

    public static function provideUnregisteredNamespaceTests(): iterable
    {
        yield 'unknown namespace' => ['<nope:Foo />'];
        yield 'unknown namespace with content' => ['<nope:Foo>bar</nope:Foo>'];
        yield 'plain html' => ['<div class="x">y</div>'];
        yield 'xml-ish markup' => ['<xsl:template match="/"><p>x</p></xsl:template>'];
        yield 'svg use tag' => ['<svg><use xlink:href="#a" /></svg>'];
        yield 'namespace is a prefix of a registered one' => ['<u:Icon />'];
        yield 'registered namespace without colon' => ['<uxfoo />'];
    }

    // ----------------------------------------------------------------------
    // Namespace resolution and aliasing
    // ----------------------------------------------------------------------

    #[DataProvider('provideResolutionTests')]
    public function testNamespacesResolveToComponentNamePrefixes(string $input, string $expected)
    {
        $this->assertSame($expected, $this->lex($input));
    }

    public static function provideResolutionTests(): iterable
    {
        yield 'twig keeps the bare name' => [
            '<twig:Alert message="hi" />',
            "{{ component('Alert', { message: 'hi' }) }}",
        ];
        yield 'ux resolves to UX' => [
            '<ux:Icon name="check" />',
            "{{ component('UX:Icon', { name: 'check' }) }}",
        ];
        yield 'sf is an alias of ux' => [
            '<sf:Icon name="check" />',
            "{{ component('UX:Icon', { name: 'check' }) }}",
        ];
        yield 'symfony is an alias of ux' => [
            '<symfony:Map />',
            "{{ component('UX:Map') }}",
        ];
        yield 'third-party alias' => [
            '<ea:Field />',
            "{{ component('EasyAdminBundle:Field') }}",
        ];
        yield 'empty prefix passes the name through' => [
            '<app:Button label="Save" />',
            "{{ component('Button', { label: 'Save' }) }}",
        ];
        yield 'colons inside the name are kept' => [
            '<ux:Turbo:Frame id="main" />',
            "{{ component('UX:Turbo:Frame', { id: 'main' }) }}",
        ];
        yield 'lowercase component name' => [
            '<ux:icon name="check" />',
            "{{ component('UX:icon', { name: 'check' }) }}",
        ];
        yield 'block conversion' => [
            '<admin:Modal>body</admin:Modal>',
            "{% component 'Admin:Modal' %}{% block content %}body{% endblock %}{% endcomponent %}",
        ];
        yield 'block conversion with attributes' => [
            '<ea:Field name="title">body</ea:Field>',
            "{% component 'EasyAdminBundle:Field' with { name: 'title' } %}{% block content %}body{% endblock %}{% endcomponent %}",
        ];
        yield 'self-closing does not open a block' => [
            '<admin:Card /><admin:Card />',
            "{{ component('Admin:Card') }}{{ component('Admin:Card') }}",
        ];
        yield 'boolean attribute' => [
            '<ea:Field required />',
            "{{ component('EasyAdminBundle:Field', { required: true }) }}",
        ];
        yield 'dynamic attribute' => [
            '<ea:Field :value="user.name" />',
            "{{ component('EasyAdminBundle:Field', { value: user.name }) }}",
        ];
        yield 'spread attributes' => [
            '<ea:Field {{ ...attrs }} />',
            "{{ component('EasyAdminBundle:Field', { ...attrs }) }}",
        ];
        yield 'interpolated attribute value' => [
            '<ea:Field name="a{{ b }}c" />',
            "{{ component('EasyAdminBundle:Field', { name: 'a'~(b)~'c' }) }}",
        ];
    }

    #[DataProvider('provideNestingTests')]
    public function testNamespacesNestFreely(string $input, string $expected)
    {
        $this->assertSame($expected, $this->lex($input));
    }

    public static function provideNestingTests(): iterable
    {
        yield 'ux inside admin' => [
            '<admin:Card><ux:Icon name="user" /></admin:Card>',
            "{% component 'Admin:Card' %}{% block content %}{{ component('UX:Icon', { name: 'user' }) }}{% endblock %}{% endcomponent %}",
        ];
        yield 'three namespaces deep' => [
            '<admin:Card><ea:Field><app:Button>go</app:Button></ea:Field></admin:Card>',
            "{% component 'Admin:Card' %}{% block content %}{% component 'EasyAdminBundle:Field' %}{% block content %}{% component 'Button' %}{% block content %}go{% endblock %}{% endcomponent %}{% endblock %}{% endcomponent %}{% endblock %}{% endcomponent %}",
        ];
        yield 'twig inside a third-party namespace' => [
            '<ea:Field><twig:Alert /></ea:Field>',
            "{% component 'EasyAdminBundle:Field' %}{% block content %}{{ component('Alert') }}{% endblock %}{% endcomponent %}",
        ];
        yield 'siblings in different namespaces' => [
            '<admin:Card /><ea:Field /><app:Button />',
            "{{ component('Admin:Card') }}{{ component('EasyAdminBundle:Field') }}{{ component('Button') }}",
        ];
        yield 'same short name in two namespaces' => [
            '<admin:Card><app:Card /></admin:Card>',
            "{% component 'Admin:Card' %}{% block content %}{{ component('Card') }}{% endblock %}{% endcomponent %}",
        ];
        yield 'unregistered namespace inside a component' => [
            '<ea:Field><nope:Thing /></ea:Field>',
            "{% component 'EasyAdminBundle:Field' %}{% block content %}<nope:Thing />{% endblock %}{% endcomponent %}",
        ];
    }

    // ----------------------------------------------------------------------
    // Strict closing-tag matching
    // ----------------------------------------------------------------------

    #[DataProvider('provideMismatchTests')]
    public function testClosingTagsMustMatchTheOpeningNamespaceAndName(string $input, string $expectedMessage)
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->lex($input);
    }

    public static function provideMismatchTests(): iterable
    {
        yield 'namespace mismatch' => [
            '<admin:Card>x</app:Card>',
            "Expected closing tag '</admin:Card>' but found '</app:Card>' at line 1.",
        ];
        yield 'namespace mismatch between aliases of the same prefix' => [
            // ux and sf resolve to the same prefix: the literal source pair still has to match
            '<ux:Icon>x</sf:Icon>',
            "Expected closing tag '</ux:Icon>' but found '</sf:Icon>' at line 1.",
        ];
        yield 'namespace mismatch between two empty prefixes' => [
            '<app:Card>x</twig:Card>',
            "Expected closing tag '</app:Card>' but found '</twig:Card>' at line 1.",
        ];
        yield 'name mismatch inside one namespace' => [
            '<ea:Field>x</ea:Column>',
            "Expected closing tag '</ea:Field>' but found '</ea:Column>' at line 1.",
        ];
        yield 'unclosed third-party component' => [
            '<ea:Field>x',
            'Expected closing tag "</ea:Field>" not found at line 1.',
        ];
        yield 'unclosed reports the outermost component' => [
            '<admin:Card><ea:Field />',
            'Expected closing tag "</admin:Card>" not found at line 1.',
        ];
        yield 'inner component left unclosed' => [
            '<admin:Card><ea:Field></admin:Card>',
            "Expected closing tag '</ea:Field>' but found '</admin:Card>' at line 1.",
        ];
    }

    public function testMismatchErrorReportsTheRightLine()
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage("Expected closing tag '</ea:Field>' but found '</ea:Column>' at line 4.");

        $this->lex("<ea:Field>\n\n\nx</ea:Column>");
    }

    public function testUnclosedErrorReportsTheRightLine()
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage('Expected closing tag "</ea:Field>" not found at line 3.');

        $this->lex("<ea:Field>\n\nx");
    }

    // ----------------------------------------------------------------------
    // Attribute errors carry the source namespace
    // ----------------------------------------------------------------------

    public function testAttributeErrorMentionsTheSourceTag()
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage('Expected "=" after ":value" when parsing the "<ea:Field" syntax at line 1.');

        $this->lex('<ea:Field :value />');
    }

    public function testAttributeErrorForTheTwigNamespaceIsUnchanged()
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage('Expected "=" after ":value" when parsing the "<twig:Field" syntax at line 1.');

        $this->lex('<twig:Field :value />');
    }

    public function testMissingComponentNameMentionsTheSourceNamespace()
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage('Expected component name when resolving the "<ea:" syntax at line 1.');

        $this->lex('<ea: />');
    }

    // ----------------------------------------------------------------------
    // `block` is a directive under every namespace
    // ----------------------------------------------------------------------

    public function testTwigBlockDirectiveStillWorksInsideANamespacedComponent()
    {
        $this->assertSame(
            "{% component 'Admin:Card' %}{% block footer %}bye{% endblock %}{% endcomponent %}",
            $this->lex('<admin:Card><twig:block name="footer">bye</twig:block></admin:Card>'),
        );
    }

    public function testTwigBlockDirectiveRecursesWithTheSameNamespaces()
    {
        // without propagating the namespaces to the sub-lexer, `<ea:Field />`
        // nested in a block would be emitted verbatim
        $this->assertSame(
            "{% component 'Admin:Card' %}{% block footer %}{{ component('EasyAdminBundle:Field') }}{% endblock %}{% endcomponent %}",
            $this->lex('<admin:Card><twig:block name="footer"><ea:Field /></twig:block></admin:Card>'),
        );
    }

    /**
     * `block` is a Twig keyword, never a sensible component name, so it stays a lexer
     * directive whichever namespace it is written under.
     */
    #[DataProvider('provideBlockDirectiveTests')]
    public function testBlockIsADirectiveUnderEveryNamespace(string $input, string $expected)
    {
        $this->assertSame($expected, $this->lex($input));
    }

    public static function provideBlockDirectiveTests(): iterable
    {
        yield 'prefixed namespace' => [
            '<ux:block name="foo">x</ux:block>',
            '{% block foo %}x{% endblock %}',
        ];
        yield 'empty-prefix namespace' => [
            '<app:block name="foo">x</app:block>',
            '{% block foo %}x{% endblock %}',
        ];
        yield 'inside a component of the same namespace' => [
            '<ux:Icon><ux:block name="foo">x</ux:block></ux:Icon>',
            "{% component 'UX:Icon' %}{% block foo %}x{% endblock %}{% endcomponent %}",
        ];
        yield 'inside a component of another namespace' => [
            '<admin:Card><ea:block name="footer">bye</ea:block></admin:Card>',
            "{% component 'Admin:Card' %}{% block footer %}bye{% endblock %}{% endcomponent %}",
        ];
        yield 'a component nested in it is still lexed' => [
            '<admin:Card><app:block name="footer"><ea:Field /></app:block></admin:Card>',
            "{% component 'Admin:Card' %}{% block footer %}{{ component('EasyAdminBundle:Field') }}{% endblock %}{% endcomponent %}",
        ];
        yield 'nested blocks of the same namespace' => [
            '<admin:Card><app:block name="outer"><admin:Card><app:block name="inner">x</app:block></admin:Card></app:block></admin:Card>',
            "{% component 'Admin:Card' %}{% block outer %}{% component 'Admin:Card' %}{% block inner %}x{% endblock %}{% endcomponent %}{% endblock %}{% endcomponent %}",
        ];
        yield 'nested blocks of different namespaces' => [
            '<admin:Card><twig:block name="outer"><admin:Card><ux:block name="inner">x</ux:block></admin:Card></twig:block></admin:Card>',
            "{% component 'Admin:Card' %}{% block outer %}{% component 'Admin:Card' %}{% block inner %}x{% endblock %}{% endcomponent %}{% endblock %}{% endcomponent %}",
        ];
    }

    public function testABlockMustBeClosedUnderTheNamespaceThatOpenedIt()
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage("Expected closing tag '</ux:block>' for block 'foo'");

        $this->lex('<ux:Icon><ux:block name="foo">x</twig:block></ux:Icon>');
    }

    public function testABlockClosedUnderAnotherNamespaceIsReportedWhenBothTagsExist()
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage("Expected closing tag '</ux:block>' but found '</app:block>'");

        $this->lex('<ux:block name="foo">x</app:block></ux:block>');
    }

    public function testTraditionalTwigBlockStillWorksInsideANamespacedComponent()
    {
        $this->assertSame(
            "{% component 'Admin:Card' %}{% block footer %}bye{% endblock %}{% endcomponent %}",
            $this->lex('<admin:Card>{% block footer %}bye{% endblock %}</admin:Card>'),
        );
    }

    // ----------------------------------------------------------------------
    // Regions the lexer must not rewrite
    // ----------------------------------------------------------------------

    #[DataProvider('provideProtectedRegionTests')]
    public function testProtectedRegionsAreNotRewritten(string $input, string $expected)
    {
        $this->assertSame($expected, $this->lex($input));
    }

    public static function provideProtectedRegionTests(): iterable
    {
        yield 'verbatim' => [
            '{% verbatim %}<ea:Field />{% endverbatim %}',
            '{% verbatim %}<ea:Field />{% endverbatim %}',
        ];
        yield 'twig comment' => [
            '{# <ea:Field /> #}',
            '{# <ea:Field /> #}',
        ];
        yield 'verbatim then a real component' => [
            '{% verbatim %}<ea:Field />{% endverbatim %}<ea:Field />',
            "{% verbatim %}<ea:Field />{% endverbatim %}{{ component('EasyAdminBundle:Field') }}",
        ];
        yield 'comment then a real component' => [
            '{# <ea:Field /> #}<ux:Icon />',
            "{# <ea:Field /> #}{{ component('UX:Icon') }}",
        ];
    }

    // ----------------------------------------------------------------------
    // Namespace set handling
    // ----------------------------------------------------------------------

    public function testLongerNamespacesAreNotShadowedByShorterOnes()
    {
        $namespaces = ['ux' => 'UX', 'uxfoo' => 'Long'];

        $this->assertSame("{{ component('Long:Bar') }}", $this->lex('<uxfoo:Bar />', $namespaces));
        $this->assertSame("{{ component('UX:Bar') }}", $this->lex('<ux:Bar />', $namespaces));
    }

    public function testAnEmptyNamespaceSetLeavesTwigOnly()
    {
        // `twig` is not declared, it is the syntax the lexer exists for
        $this->assertSame("{{ component('Alert') }}<ux:Icon />", $this->lex('<twig:Alert /><ux:Icon />', []));
    }

    public function testTwigNeedsNoDeclaringAndCannotBeRemoved()
    {
        $this->assertSame("{{ component('Alert') }}", $this->lex('<twig:Alert />', ['ux' => 'ux']));
    }

    public function testTrailingColonsInTheGivenPrefixesAreStripped()
    {
        $this->assertSame(
            "{{ component('EasyAdminBundle:Field') }}",
            $this->lex('<ea:Field />', ['ea' => 'EasyAdminBundle:']),
        );
    }

    #[DataProvider('provideInvalidNamespaceKeys')]
    public function testTheConstructorRejectsMalformedNamespaces(string $namespace)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Invalid component namespace "%s": it must start with a letter and contain only letters and digits.', $namespace));

        new TwigPreLexer(1, [$namespace => '']);
    }

    public static function provideInvalidNamespaceKeys(): iterable
    {
        yield 'dash' => ['foo-bar'];
        yield 'underscore' => ['foo_bar'];
        yield 'leading digit' => ['123foo'];
        yield 'accented' => ['élobar'];
        yield 'inner colon' => ['foo:bar'];
        yield 'empty' => [''];
        yield 'space' => ['foo bar'];
        yield 'regex metacharacters' => ['foo.*'];
    }

    public function testStartingLineIsHonoured()
    {
        $lexer = new TwigPreLexer(10, self::NAMESPACES);

        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage('Expected closing tag "</ea:Field>" not found at line 10.');

        $lexer->preLexComponents('<ea:Field>x');
    }
}
