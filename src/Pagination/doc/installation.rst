Installation
============

Install the bundle with Composer:

.. code-block:: terminal

    $ composer require symfony/ux-pagination

Symfony Flex registers ``UXPaginationBundle`` once the Flex recipe for this
package is published. Until then, or when the application does not use Flex,
register the bundle manually::

    // config/bundles.php
    return [
        // ...
        Symfony\UX\Pagination\UXPaginationBundle::class => ['all' => true],
    ];

UX Pagination installs no Stimulus controller and no JavaScript runtime. It
renders on the server through Twig and requires TwigBundle. TwigComponent is
optional: install it only for the component syntax.

Requirements
------------

* PHP 8.4 or later;
* Symfony 7.4 or 8.0;
* Symfony TwigBundle 7.4 or 8.0;
* Twig 3.21 or later.

Optional integrations require:

* Doctrine ORM 3 for ORM ``QueryBuilder`` objects;
* Doctrine DBAL 4.4+ for DBAL ``QueryBuilder`` objects;
* UX TwigComponent 3.0+ for component syntax;
* UX LiveComponent 3.0+ for reactive component integration.

The ``ux_pagination()`` Twig function works without TwigComponent. Install
TwigComponent only for the component syntax:

.. code-block:: terminal

    $ composer require symfony/ux-twig-component

.. code-block:: html+twig

    {# templates/product/index.html.twig #}
    <twig:ux:pagination :pagination="products" />

Assets
------

The default theme ships an optional stylesheet. With AssetMapper, link it
directly from the bundle:

.. code-block:: html+twig

    {# templates/base.html.twig #}
    <link
        rel="stylesheet"
        href="{{ asset('@symfony/ux-pagination/style.min.css') }}"
    >

With Webpack Encore, import the stylesheet from the npm package in the
application entrypoint:

.. code-block:: javascript

    // assets/app.js
    import '@symfony/ux-pagination/style.min.css';

The Bootstrap and Tailwind themes rely on their framework classes instead. A
custom theme can own all styling and skip the bundle stylesheet.

Verify the installation
-----------------------

Check that the paginator service and the Twig function are available:

.. code-block:: terminal

    $ php bin/console debug:autowiring PaginatorInterface
    $ php bin/console debug:twig --filter=ux_pagination

Continue with :doc:`getting-started`.
