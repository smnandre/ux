Symfony UX Icons
================

Renders local and remote SVG icons in your Twig templates.

.. code-block:: html+twig

    {{ ux_icon('mdi:check', {class: 'w-4 h-4'}) }}
    {# or #}
    <twig:UX:Icon name="mdi:check" class="w-4 h-4" />

    {# renders as: #}
    <svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path fill="currentColor" d="M21 7L9 19l-5.5-5.5l1.41-1.41L9 16.17L19.59 5.59z"/></svg>

Installation
------------

.. code-block:: terminal

    $ composer require symfony/ux-icons

Icons
------

The ``symfony/ux-icons`` package aspires to offer simple and easy-to-use ways to
handle SVG icons in your Symfony application.

SVG Icons
~~~~~~~~~

[SVG](https://en.wikipedia.org/wiki/SVG) (Scalable Vector Graphics) is an XML-based
vector image format. SVGs are a great way to add scalable, resolution-independent
graphics to your website. They can be styled with CSS, combined, animated, reused..
for a fraction of the file size of bitmap images.

Making them the perfect choice for icons.


Icon Sets
~~~~~~~~~

There are many icon sets available, each with their own unique style and set of icons.


Icon identifiers
~~~~~~~~~~~~~~~~

In the UX Icons component, icons are referenced using an unique identifier that
follows one of the following syntaxes:

* ``prefix``:``name``  (e.g. ``mdi:check``, ``bi:check``, ``editor:align-left``)
* ``name`` only (e.g. ``check``, ``close``, ``menu``)

Icon Name
^^^^^^^^^

The ``name`` is the... name of the icon, without the file extension (e.g. ``check``).

.. caution::

    The name must match a standard ``slug`` format: ``[a-z0-9-]+(-[0-9a-z])+``.

Icon Prefix
^^^^^^^^^^^

Depending on your configuration, the ``prefix`` can be the name of an icon set,  a directory
where the icon is located, or a combination of both.


For example, the `bi` prefix refers to the Bootstrap Icons set, while the `header` prefix
refers to the icons located in the `header` directory.


Loading Icons
-------------

Local SVG Icons
~~~~~~~~~~~~~~~

Add your svg icons to the ``assets/icons/`` directory and commit them.
The name of the file is used as the _name_ of the icon (``name.svg`` will be named ``name``).
If located in a subdirectory, the _name_ will be ``sub-dir:name``.

.. code-block:: text

    your-project/
    ├─ assets/
    │  └─ icons/
    │     ├─ bi/
    │     │  └─ pause-circle.svg
    │     │  └─ play-circle.svg
    │     ├─ header/
    │     │  ├─ logo.svg
    │     │  └─ menu.svg
    │     ├─ close.svg
    │     ├─ menu.svg
    │     └─ ...
    └─ ...


Icons On-Demand
~~~~~~~~~~~~~~~

`ux.symfony.com/icons`_ has a huge searchable repository of icons
from many different sets. This package provides a way to include any icon found on this site _on-demand_.

1. Visit `ux.symfony.com/icons`_ and search for an icon
   you'd like to use. Once you find one you like, copy one of the code snippets provided.
2. Paste the snippet into your Twig template and the icon will be automatically fetched (and cached).
3. That's it!

This works by using the `Iconify`_ API (to which `ux.symfony.com/icons`_
is a frontend for) to fetch the icon and render it in place. This icon is then cached for future requests
for the same icon.

.. note::

    `Local SVG Icons`_ of the same name will have precedence over _on-demand_ icons.

Import Command
^^^^^^^^^^^^^^

You can import any icon from `ux.symfony.com/icons`_ to your local
directory using the ``ux:icons:import`` command:

.. code-block:: terminal

    $ php bin/console ux:icons:import flowbite:user-solid # saved as `flowbite/user-solid.svg` and name is `flowbite:user-solid`

    # import several at a time
    $ php bin/console ux:icons:import flowbite:user-solid flowbite:home-solid

.. note::

    Imported icons must be committed to your repository.

On-Demand VS Import
^^^^^^^^^^^^^^^^^^^

While *on-demand* icons are great during development, they require http requests to fetch the icon
and always use the *latest version* of the icon. It's possible the icon could change or be removed
in the future. Additionally, the cache warming process will take significantly longer if using
many _on-demand_ icons. You can think of importing the icon as *locking it* (similar to how
``composer.lock`` _locks_ your dependencies).


Rendering Icons
---------------

.. code-block:: html+twig

    {{ ux_icon('user-profile', {class: 'w-4 h-4'}) }} <!-- renders "user-profile.svg" -->

    {{ ux_icon('sub-dir:user-profile', {class: 'w-4 h-4'}) }} <!-- renders "sub-dir/user-profile.svg" (sub-directory) -->

    {{ ux_icon('flowbite:user-solid') }} <!-- renders "flowbite:user-solid" from ux.symfony.com -->

HTML Attributes
~~~~~~~~~~~~~~~

The second argument of the ``ux_icon`` function is an array of attributes to add to the icon.

.. code-block:: twig

    {# renders "user-profile.svg" with class="w-4 h-4" #}
    {{ ux_icon('user-profile', {class: 'w-4 h-4'}) }}

    {# renders "user-profile.svg" with class="w-4 h-4" and aria-hidden="true" #}
    {{ ux_icon('user-profile', {class: 'w-4 h-4', 'aria-hidden': true}) }}

Default Attributes
~~~~~~~~~~~~~~~~~~

You can set default attributes for all icons in your configuration. These attributes will be
added to all icons unless overridden by the second argument of the ``ux_icon`` function.

.. code-block:: yaml

    # config/packages/ux_icons.yaml
    ux_icons:
        default_icon_attributes:
            fill: currentColor

Now, all icons will have the ``fill`` attribute set to ``currentColor`` by default.

.. code-block:: twig

    # renders "user-profile.svg" with fill="currentColor"
    {{ ux_icon('user-profile') }}

    # renders "user-profile.svg" with fill="red"
    {{ ux_icon('user-profile', {fill: 'red'}) }}


HTML Syntax
~~~~~~~~~~~

.. code-block:: html+twig

    <twig:UX:Icon name="user-profile" />

    {# Renders "user-profile.svg" #}
    <twig:UX:Icon name="user-profile" class="w-4 h-4" />

    {# Renders "sub-dir/user-profile.svg" (sub-directory) #}
    <twig:UX:Icon name="sub-dir:user-profile" class="w-4 h-4" />

    {# Renders "flowbite:user-solid" from ux.symfony.com #}
    <twig:UX:Icon name="flowbite:user-solid" />


.. note::

    ``symfony/ux-twig-component`` is required to use the HTML syntax.


Performances
------------

The UXIcon component is designed to be as fast as possible, while offering a
great deal of flexibility. The following are some of the optimizations made to
ensure the best performance possible.

Icon Caching
~~~~~~~~~~~~

To avoid having to parse icon files on every request, icons are cached.

In production, you can pre-warm the cache by running the following command:

.. code-block:: terminal

    $ php bin/console ux:icons:warm-cache

This command looks in all your Twig templates for ``ux_icon`` calls and caches the icons it finds.

.. note::

    During development, if you modify an icon, you will need to clear the cache (``bin/console cache:clear``)
    to see the changes.

.. tip::

    If using `symfony/asset-mapper`_, the cache is warmed automatically when running ``asset-map:compile``.

TwigComponent
~~~~~~~~~~~~~

The ``ux_icon`` function is optimized to be as fast as possible. To deliver the same level
of performance with the TwigComponent (``<twig:UX:Icon name="..." />``), the TwigComponent
usual overhead is reduced to the bare minimum, immediately calling the IconRenderer and
returning the HTML output.

.. warning::

    The <twig:UX:Icon> component does not support embedded content.

    .. code-block:: twig+html

        {# The 🧸 will be ignore in the HTML output #}
        <twig:UX:Icon name="user-profile" class="w-4 h-4">🧸</twig:UX:Icon>

        {# Renders "user-profile.svg" #}
        <svg viewBox="0 0 24 24" class="w-4 h-4">
            <path fill="currentColor" d="M21 7L9 19l-5.5-5.5l1.41-1.41L9 16.17L19.59 5.59z"/>
        </svg>


Configuration
-------------

The UXIcon integrates seamlessly in Symfony applications. All these options are configured under
the ``ux_icons`` key in your application configuration.

.. code-block:: yaml

    # config/packages/ux_icons.yaml
    ux_icons:
        {# ... #}


Debugging Configuration
~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: terminal

    # Displays the default config values
    $ php bin/console config:dump-reference ux_icons

    # Displays the actual config values used by your application
    $ php bin/console debug:config ux_icons

Full Configuration
~~~~~~~~~~~~~~~~~~

.. code-block:: yaml

    ux_icons:
        # The local directory where icons are stored.
        icon_dir: '%kernel.project_dir%/assets/icons'

        # Default attributes to add to all icons.
        default_icon_attributes:
            # Default:
            fill: currentColor

        # Configuration for the "on demand" icons powered by Iconify.design.
        iconify:
           enabled:              true

           # The endpoint for the Iconify API.
           endpoint:             'https://api.iconify.design'

Learn more
----------

* :doc:`Creating and Using Templates </templates>`
* :doc:`How to manage CSS and JavaScript assets in Symfony applications </frontend>`

.. _`ux.symfony.com/icons`: https://ux.symfony.com/icons
.. _`Iconify`: https://iconify.design
.. _`symfony/asset-mapper`: https://symfony.com/doc/current/frontend/asset_mapper.html
