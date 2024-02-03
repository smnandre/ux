UX Icons
===============

.. caution::

    This component is currently experimental and is likely to change, or even
    change drastically.

The UX Icons component provides a way to manage and render SVG icons in your application.

Installation
------------

Install the bundle using Composer and Symfony Flex:

.. code-block:: terminal

    $ composer require symfony/ux-icons


Configuration
-------------


```yaml
# config/packages/ux_icons.yaml
ux_icons:

  path: '%kernel.project_dir%/templates/icons'

  # ...

```


Render Icons
------------

### Icon names

```
    {{ icon('arrow-up') }}   
```

### Attributes

```twig
    {{ icon('arrow-up', {class: 'icon'}) }}   
```

### Twig Component

HTML syntax (requires symfony/ux-twig-component)

```twig
    <twig:ux:icon name="arrow-up" class="icon" />
```

```twig
    <twig:ux:icon name="arrow-up" />
```

Debugging Icons
---------------

### Clear cache

```console
    php bin/console cache:clear
```

### Debugging icons
