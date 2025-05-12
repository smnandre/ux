<?php

namespace Symfony\UX\TwigComponent\Tests\Unit\Twig\HtmlSyntax;

use PHPUnit\Framework\TestCase;
use Symfony\UX\TwigComponent\Twig\HtmlSyntax\HtmlSyntaxTranspiler;

class HtmlSyntaxTranspilerTest extends TestCase
{
    public function testItTransformsSelfClosingComponent()
    {
        $transpiler = new HtmlSyntaxTranspiler();
        $result = $transpiler->transpile('<twig:example />');

        $this->assertSame("{{ component('example') }}", $result);
    }

    public function testItTransformsClosingComponent()
    {
        $transpiler = new HtmlSyntaxTranspiler();
        $result = $transpiler->transpile('<twig:example></twig:example>');

        $this->assertSame("{% component 'example' %}{% endcomponent %}", $result);
    }

    public function testItTransformsComponentWithContent()
    {
        $transpiler = new HtmlSyntaxTranspiler();
        $result = $transpiler->transpile('<twig:example>Content</twig:example>');

        $this->assertSame("{% component 'example' %}Content{% endcomponent %}", $result);
    }

    public function testItIgnoresVerbatim()
    {
        $transpiler = new HtmlSyntaxTranspiler();
        $result = $transpiler->transpile('{% verbatim %}<twig:example />{% endverbatim %}');

        $this->assertSame('{% verbatim %}<twig:example />{% endverbatim %}', $result);
    }

    public function testItHandlesEmptyInput()
    {
        $transpiler = new HtmlSyntaxTranspiler();
        $result = $transpiler->transpile('');

        $this->assertSame('', $result);
    }

    public function testItPreservesTwigBlocksAndComments()
    {
        $transpiler = new HtmlSyntaxTranspiler();
        $result = $transpiler->transpile('{% if condition %}<twig:example />{% endif %}');

        $this->assertSame("{% if condition %}{{ component('example') }}{% endif %}", $result);
    }

    public function testItPreservesPlainText()
    {
        $transpiler = new HtmlSyntaxTranspiler();
        $result = $transpiler->transpile('Plain text <twig:example /> more text');

        $this->assertSame("Plain text {{ component('example') }} more text", $result);
    }

    public function testItNestedComponents()
    {
        $transpiler = new HtmlSyntaxTranspiler();
        $input = '<twig:parent><twig:child /></twig:parent>';

        $result = $transpiler->transpile($input);

        $this->assertSame("{% component 'parent' %}{{ component('child') }}{% endcomponent %}", $result);
    }

    public function testItTransformsComponentWithDashInName()
    {
        $transpiler = new HtmlSyntaxTranspiler();
        $result = $transpiler->transpile('<twig:foo-bar />');
        $this->assertSame("{{ component('foo-bar') }}", $result);
    }

    public function testItTransformsComponentWithColonInName()
    {
        $transpiler = new HtmlSyntaxTranspiler();
        $result = $transpiler->transpile('<twig:foo:bar />');
        $this->assertSame("{{ component('foo:bar') }}", $result);
    }

    public function testItTransformsComponentWithAtInName()
    {
        $transpiler = new HtmlSyntaxTranspiler();
        $result = $transpiler->transpile('<twig:@foo />');
        $this->assertSame("{{ component('@foo') }}", $result);
    }

    public function testItTransformsComponentWithDotInName()
    {
        $transpiler = new HtmlSyntaxTranspiler();
        $result = $transpiler->transpile('<twig:foo.bar />');
        $this->assertSame("{{ component('foo.bar') }}", $result);
    }

    public function testItTransformsComponentWithSimpleAttributes()
    {
        $transpiler = new HtmlSyntaxTranspiler();
        $result = $transpiler->transpile('<twig:example foo="bar" truthy />');
        $this->assertSame("{{ component('example', { foo: 'bar', truthy: true }) }}", $result);
    }

    public function testItTransformsComponentWithDynamicAttribute()
    {
        $transpiler = new HtmlSyntaxTranspiler();
        $result = $transpiler->transpile('<twig:example :foo="bar" />');
        $this->assertSame("{{ component('example', { foo: (bar) }) }}", $result);
    }

    public function testItTransformsComponentWithSpreadAttribute()
    {
        $transpiler = new HtmlSyntaxTranspiler();
        $result = $transpiler->transpile('<twig:example foo="bar" {{...attr}} />');
        $this->assertSame("{{ component('example', { foo: 'bar', ...attr }) }}", $result);
    }

    public function testItTransformsComponentWithMixedAttributeStringTwig()
    {
        $transpiler = new HtmlSyntaxTranspiler();
        $result = $transpiler->transpile('<twig:example text="hello {{ var }}!" />');
        $this->assertSame("{{ component('example', { text: 'hello '~(var)~'!' }) }}", $result);
    }

    public function testItTransformsComponentWithSingleQuoteAttribute()
    {
        $transpiler = new HtmlSyntaxTranspiler();
        $result = $transpiler->transpile("<twig:example foo='bar' />");
        $this->assertSame("{{ component('example', { foo: 'bar' }) }}", $result);
    }

    public function testItTransformsComponentWithBlockContent()
    {
        $transpiler = new HtmlSyntaxTranspiler();
        $result = $transpiler->transpile('<twig:example><twig:block name="header">Hello</twig:block></twig:example>');
        $this->assertSame("{% component 'example' %}{% block header %}Hello{% endblock %}{% endcomponent %}", $result);
    }

    public function testItThrowsOnMismatchedClose()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Mismatched closing tag: expected </twig:example> but found </twig:wrong>');
        $transpiler = new HtmlSyntaxTranspiler();
        $transpiler->transpile('<twig:example></twig:wrong>');
    }

    public function testItThrowsOnUnclosedComponent()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unclosed component tag: <twig:example>');
        $transpiler = new HtmlSyntaxTranspiler();
        $transpiler->transpile('<twig:example>');
    }

    public function testItIgnoresVerbatimWithNestedComponent()
    {
        $transpiler = new HtmlSyntaxTranspiler();
        $result = $transpiler->transpile('{% verbatim %}<twig:foo></twig:foo>{% endverbatim %}');
        $this->assertSame('{% verbatim %}<twig:foo></twig:foo>{% endverbatim %}', $result);
    }

    public function testItIgnoresVerbatimEvenWithTwigInside()
    {
        $transpiler = new HtmlSyntaxTranspiler();
        $result = $transpiler->transpile('{% verbatim %}hello {{ variable }} <twig:foo />{% endverbatim %}');
        $this->assertSame('{% verbatim %}hello {{ variable }} <twig:foo />{% endverbatim %}', $result);
    }

    public function testItPreservesTwigComment()
    {
        $transpiler = new HtmlSyntaxTranspiler();
        $result = $transpiler->transpile('{# Ceci est un commentaire <twig:foo /> #}');
        $this->assertSame('{# Ceci est un commentaire <twig:foo /> #}', $result);
    }

    public function testItPreservesTwigCommentWithComponentBeforeAndAfter()
    {
        $transpiler = new HtmlSyntaxTranspiler();
        $result = $transpiler->transpile('start {# comment <twig:foo/> #} <twig:bar/> end');
        $this->assertSame('start {# comment <twig:foo/> #} {{ component(\'bar\') }} end', $result);
    }

    public function testItHandlesStringWithComponentLikePattern()
    {
        $transpiler = new HtmlSyntaxTranspiler();
        $result = $transpiler->transpile('{% set str = "<twig:foo />" %}<twig:bar/>');
        $this->assertSame('{% set str = "<twig:foo />" %}{{ component(\'bar\') }}', $result);
    }

    public function testItHandlesStringWithQuotesInAttribute()
    {
        $transpiler = new HtmlSyntaxTranspiler();
        $result = $transpiler->transpile('<twig:foo bar="baz\'qux" />');
        $this->assertSame("{{ component('foo', { bar: 'baz\\'qux' }) }}", $result);
    }

    public function testItHandlesAttributeWithTwigInsideString()
    {
        $transpiler = new HtmlSyntaxTranspiler();
        $result = $transpiler->transpile('<twig:foo text="Hello {{ name }}!" />');
        $this->assertSame("{{ component('foo', { text: 'Hello '~(name)~'!' }) }}", $result);
    }

    public function testItTransformsComponentWithDefaultContentBlock()
    {
        $transpiler = new HtmlSyntaxTranspiler();
        $result = $transpiler->transpile('<twig:example>Hello</twig:example>');
        $this->assertSame("{% component 'example' %}Hello{% endcomponent %}", $result);
    }

    public function testItHandlesEmptyAttributeValue()
    {
        $transpiler = new HtmlSyntaxTranspiler();
        $result = $transpiler->transpile('<twig:foo bar="" />');
        $this->assertSame("{{ component('foo', { bar: '' }) }}", $result);
    }

    public function testSelfClosingComponent()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo') }}",
            $t->transpile('<twig:foo />')
        );
    }

    public function testSelfClosingComponentWithStaticAttribute()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { bar: 'baz' }) }}",
            $t->transpile('<twig:foo bar="baz" />')
        );
    }

    public function testSelfClosingComponentWithEmptyAttribute()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { bar: '' }) }}",
            $t->transpile('<twig:foo bar="" />')
        );
    }

    public function testSelfClosingComponentWithTruthiness()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { bar: true }) }}",
            $t->transpile('<twig:foo bar />')
        );
    }

    public function testSelfClosingComponentWithMultipleAttributes()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { bar: 'baz', qux: true }) }}",
            $t->transpile('<twig:foo bar="baz" qux />')
        );
    }

    public function testSelfClosingComponentWithSpread()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { ...attrs }) }}",
            $t->transpile('<twig:foo {{...attrs}} />')
        );
    }

    public function testSelfClosingComponentWithMultipleSpreads()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { ...a, ...b }) }}",
            $t->transpile('<twig:foo {{...a}} {{...b}} />')
        );
    }

    public function testSelfClosingComponentWithSpreadAndOthers()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { bar: 'baz', ...attrs }) }}",
            $t->transpile('<twig:foo bar="baz" {{...attrs}} />')
        );
    }

    public function testSelfClosingComponentWithDynamicAttribute()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { bar: (baz) }) }}",
            $t->transpile('<twig:foo :bar="baz" />')
        );
    }

    public function testSelfClosingComponentWithTwigDynamicAttribute()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { bar: (baz) }) }}",
            $t->transpile('<twig:foo bar={{ baz }} />')
        );
    }

    public function testSelfClosingComponentWithAllAttrTypes()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { a: 'A', b: true, ...attrs, c: (baz), d: '' }) }}",
            $t->transpile('<twig:foo a="A" b {{...attrs}} :c="baz" d="" />')
        );
    }

    public function testComponentWithContent()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{% component 'foo' %}Hello{% endcomponent %}",
            $t->transpile('<twig:foo>Hello</twig:foo>')
        );
    }

    public function testComponentWithContentAndAttributes()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{% component 'foo' with { bar: 'baz' } %}Hello{% endcomponent %}",
            $t->transpile('<twig:foo bar="baz">Hello</twig:foo>')
        );
    }

    public function testComponentWithMultipleLinesContent()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{% component 'foo' %}\nLine1\nLine2\n{% endcomponent %}",
            $t->transpile("<twig:foo>\nLine1\nLine2\n</twig:foo>")
        );
    }

    // Attributs mixtes texte/Twig
    public function testAttributeMixteTextTwig()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { bar: 'Hello '~(name)~'!' }) }}",
            $t->transpile('<twig:foo bar="Hello {{ name }}!" />')
        );
    }

    public function testAttributeMixteTwigTextTwig()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { bar: (x)~' - '~(y) }) }}",
            $t->transpile('<twig:foo bar="{{ x }} - {{ y }}" />')
        );
    }

    public function testAttributeOnlyTwig()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { bar: (var) }) }}",
            $t->transpile('<twig:foo bar="{{ var }}" />')
        );
    }

    public function testAttributeOnlyText()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { bar: 'baz' }) }}",
            $t->transpile('<twig:foo bar="baz" />')
        );
    }

    // Blocks
    public function testBlockSimple()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            '{% block header %}Hello{% endblock %}',
            $t->transpile('<twig:block name="header">Hello</twig:block>')
        );
    }

    public function testBlockInComponent()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{% component 'foo' %}{% block header %}Hello{% endblock %}{% endcomponent %}",
            $t->transpile('<twig:foo><twig:block name="header">Hello</twig:block></twig:foo>')
        );
    }

    public function testBlockWithContentBeforeAndAfter()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{% component 'foo' %}Before{% block head %}Block{% endblock %}After{% endcomponent %}",
            $t->transpile('<twig:foo>Before<twig:block name="head">Block</twig:block>After</twig:foo>')
        );
    }

    public function testNestedBlocks()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{% component 'foo' %}{% block outer %}Out{% block inner %}In{% endblock %}Out{% endblock %}{% endcomponent %}",
            $t->transpile('<twig:foo><twig:block name="outer">Out<twig:block name="inner">In</twig:block>Out</twig:block></twig:foo>')
        );
    }

    // Noms spéciaux
    public function testComponentWithDashInName()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo-bar') }}",
            $t->transpile('<twig:foo-bar />')
        );
    }

    public function testComponentWithColonInName()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo:bar') }}",
            $t->transpile('<twig:foo:bar />')
        );
    }

    public function testComponentWithDotInName()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo.bar') }}",
            $t->transpile('<twig:foo.bar />')
        );
    }

    public function testComponentWithAtInName()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo@bar') }}",
            $t->transpile('<twig:foo@bar />')
        );
    }

    // Edge cases
    public function testComponentWithAttributeWithSingleQuote()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { bar: 'it\\'s ok' }) }}",
            $t->transpile("<twig:foo bar=\"it's ok\" />")
        );
    }

    public function testComponentWithAttributeWithDoubleQuotesInValue()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { bar: 'a \"value\"' }) }}",
            $t->transpile('<twig:foo bar="a \"value\"" />')
        );
    }

    public function testComponentWithNewlinesAndSpacesInAttributes()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { bar: 'baz', qux: true }) }}",
            $t->transpile("<twig:foo \n bar=\"baz\"\nqux   />")
        );
    }

    public function testComponentWithMultipleSpreadsAndAttrs()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { ...a, ...b, c: 'd' }) }}",
            $t->transpile('<twig:foo {{...a}} {{...b}} c="d" />')
        );
    }

    // Erreurs & Robustesse
    public function testMismatchedClosingThrows()
    {
        $this->expectException(\RuntimeException::class);
        $t = new HtmlSyntaxTranspiler();
        $t->transpile('<twig:foo></twig:bar>');
    }

    public function testUnclosedComponentThrows()
    {
        $this->expectException(\RuntimeException::class);
        $t = new HtmlSyntaxTranspiler();
        $t->transpile('<twig:foo>');
    }

    // Verbatim, comments, etc
    public function testPreservesTwigVerbatim()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            '{% verbatim %}<twig:foo />{% endverbatim %}',
            $t->transpile('{% verbatim %}<twig:foo />{% endverbatim %}')
        );
    }

    public function testPreservesTwigComments()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            '{# this is a comment #}',
            $t->transpile('{# this is a comment #}')
        );
    }

    public function testPreservesTwigBlocks()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            '{% if foo %}{{ component(\'bar\') }}{% endif %}',
            $t->transpile('{% if foo %}<twig:bar />{% endif %}')
        );
    }

    // Divers
    public function testComponentWithNestedComponent()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{% component 'foo' %}{% component 'bar' %}Yo{% endcomponent %}{% endcomponent %}",
            $t->transpile('<twig:foo><twig:bar>Yo</twig:bar></twig:foo>')
        );
    }

    public function testComponentWithNestedSelfClosingComponent()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{% component 'foo' %}Hello{{ component('bar') }}{% endcomponent %}",
            $t->transpile('<twig:foo>Hello<twig:bar /></twig:foo>')
        );
    }

    public function testComponentWithWeirdAttributeNames()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { 'data-foo': 'bar', 'x:bar': 'baz' }) }}",
            $t->transpile('<twig:foo data-foo="bar" x:bar="baz" />')
        );
    }

    public function testSelfClosingComponentIsTranspiled()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo') }}",
            $t->transpile('<twig:foo />')
        );
    }

    public function testComponentWithStaticAttributes()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { 'data-foo': 'bar', 'x:bar': 'baz' }) }}",
            $t->transpile('<twig:foo data-foo="bar" x:bar="baz" />')
        );
    }

    public function testComponentInsideTwigIf()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{% if foo %}{{ component('bar') }}{% endif %}",
            $t->transpile('{% if foo %}<twig:bar />{% endif %}')
        );
    }

    public function testBlockWithNameIsTranspiled()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{% block sidebar %}{{ component('foo') }}{% endblock %}",
            $t->transpile('<twig:block name="sidebar"><twig:foo /></twig:block>')
        );
    }

    public function testBlockWithoutNameDefaultsToContent()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{% block content %}{{ component('foo') }}{% endblock %}",
            $t->transpile('<twig:block><twig:foo /></twig:block>')
        );
    }

    public function testNestedBlocksAndComponents()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{% block sidebar %}{% block inner %}{{ component('foo') }}{% endblock %}{% endblock %}",
            $t->transpile('<twig:block name="sidebar"><twig:block name="inner"><twig:foo /></twig:block></twig:block>')
        );
    }

    public function testComponentNotTranspiledInVerbatim()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            '{% verbatim %}<twig:foo />{% endverbatim %}',
            $t->transpile('{% verbatim %}<twig:foo />{% endverbatim %}')
        );
    }

    public function testComponentNotTranspiledInRaw()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            '{% raw %}<twig:foo />{% endraw %}',
            $t->transpile('{% raw %}<twig:foo />{% endraw %}')
        );
    }

    public function testComponentNotTranspiledInTwigComment()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            '{# <twig:foo /> #}',
            $t->transpile('{# <twig:foo /> #}')
        );
    }

    public function testComplexImbrication()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{% block sidebar %}{{ component('foo') }}{% block content %}{% verbatim %}<twig:bar />{% endverbatim %}{% endblock %}{% endblock %}",
            $t->transpile('<twig:block name="sidebar"><twig:foo /><twig:block>{% verbatim %}<twig:bar />{% endverbatim %}</twig:block></twig:block>')
        );
    }

    public function testComponentWithSpreadAttributes()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { ...bar }) }}",
            $t->transpile('<twig:foo {{ ...bar }} />')
        );
    }

    public function testComponentWithDynamicAttribute()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { 'data-bar': (baz) }) }}",
            $t->transpile('<twig:foo :data-bar="{{ baz }}" />')
        );
    }

    public function testComponentWithTruthyAttribute()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { featured: true }) }}",
            $t->transpile('<twig:foo featured />')
        );
    }

    public function testComponentWithMixedAttributes()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { 'a-b': 'str', bar: (baz), ...spread, enabled: true }) }}",
            $t->transpile('<twig:foo a-b="str" :bar="{{ baz }}" {{ ...spread }} enabled />')
        );
    }

    public function testComponentWithTwigExpressionInValue()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { label: 'prefix '~(baz)~' suffix' }) }}",
            $t->transpile('<twig:foo label="prefix {{ baz }} suffix" />')
        );
    }

    public function testBlockWithoutCloseThrows()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->expectException(\RuntimeException::class);
        $t->transpile('<twig:block name="main"><twig:foo /></twig:block><twig:block name="sidebar">');
    }

    public function testComponentWithUnclosedThrows()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->expectException(\RuntimeException::class);
        $t->transpile('<twig:foo>');
    }

    public function testMalformedCloseThrows()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->expectException(\RuntimeException::class);
        $t->transpile('<twig:foo></twig:bar>');
    }

    public function testDeeplyNestedBlocksAndComponents()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{% block a %}{% block b %}{{ component('foo', { x: 'y' }) }}{% endblock %}{% endblock %}",
            $t->transpile('<twig:block name="a"><twig:block name="b"><twig:foo x="y" /></twig:block></twig:block>')
        );
    }

    public function testComponentInsideVerbatimBlock()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{% verbatim %}<twig:foo x=\"y\" />{% endverbatim %}",
            $t->transpile('{% verbatim %}<twig:foo x="y" />{% endverbatim %}')
        );
    }

    public function testComponentInsideRawBlock()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            '{% raw %}<twig:foo bar />{% endraw %}',
            $t->transpile('{% raw %}<twig:foo bar />{% endraw %}')
        );
    }

    public function testComponentInsideTwigComment()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            '{# <twig:foo bar /> #}',
            $t->transpile('{# <twig:foo bar /> #}')
        );
    }

    public function testMixedTwigBlocksAndComponents()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{% if cond %}{% block sidebar %}{{ component('foo') }}{% block inner %}{{ component('bar', { a: 'b' }) }}{% endblock %}{% endblock %}{% endif %}",
            $t->transpile('{% if cond %}<twig:block name="sidebar"><twig:foo /><twig:block name="inner"><twig:bar a="b" /></twig:block></twig:block>{% endif %}')
        );
    }

    public function testComponentWithMultipleAttributesIncludingColon()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { 'x:bar': (baz), simple: 'y' }) }}",
            $t->transpile('<twig:foo x:bar="{{ baz }}" simple="y" />')
        );
    }

    public function testStaticAttributeMultiline()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { label: 'Hello\\nworld\\n!' }) }}",
            $t->transpile("<twig:foo label=\"Hello\nworld\n!\" />")
        );
    }

    public function testInterpolatedAttributeMultiline()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { label: 'Hello\\n'~(name)~'\\n!' }) }}",
            $t->transpile("<twig:foo label=\"Hello\n{{ name }}\n!\" />")
        );
    }

    public function testMultipleInterpolationsMultiline()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { label: '\\n'~(first)~'\\n-\\n'~(second)~'\\n' }) }}",
            $t->transpile('<twig:foo label="
{{ first }}
-
{{ second }}
" />')
        );
    }

    public function testAttributeDynamicMultilineArray()
    {
        $t = new HtmlSyntaxTranspiler();
        $this->assertSame(
            "{{ component('foo', { foobar: ([\n    'bar',\n    'baz',\n]) }) }}",
            $t->transpile("<twig:foo foobar=\"{{ [\n    'bar',\n    'baz',\n]}}\" />")
        );
    }
}


