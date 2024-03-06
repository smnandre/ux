Symfony UX Icons
================

Tools for embedding SVG icons in your Twig templates.

```twig
{{ ux_icon('mdi:symfony', {class: 'w-4 h-4'}) }}
{# or #}
<twig:UX:Icon name="mdi:check" class="w-4 h-4" />

{# renders as: #}
<svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path fill="currentColor" d="M21 7L9 19l-5.5-5.5l1.41-1.41L9 16.17L19.59 5.59z"/></svg>
```

## Installation

```bash
composer require symfony/ux-icons
```

TODO flex stuff

Read following sections to learn how to use this package.

TODO summary

## ?? Icons 

### SVG Files

### Icon Names

## Loading Icons

TODO (your choice)

### Local Icons

TODO (directory tree)

### Importing Icons

TODO (import command)

### On-Demand Icons

TODO (iconify)

### Finding Icons

You can find icons on [ux.symfony.com/icons](https://ux.symfony.com/icons) and include them _on-demand_.

TODO (image + metrics)

## Rendering Icons

### Twig Function

TODO (twig syntax)

### Twig Component 

TODO (html syntax)

### Custom Attributes

TODO (pass attributes)

### Default Attributes

TODO (config)

### Accessibility

TODO (aria-label)

## Performance

### Caching

### Twig Optimizations

### Best Practices

## Learn More

* repo / support / etc



## Icons?

No icons are provided by this package but there are several ways to include and render icons. 

### Local SVG Icons

Add your svg icons to the `assets/icons/` directory and commit them.
The name of the file is used as the _name_ of the icon (`name.svg` will be named `name`).
If located in a subdirectory, the _name_ will be `sub-dir:name`.

### Icons _On-Demand_

[ux.symfony.com/icons](https://ux.symfony.com/icons) has a huge searchable repository of icons
from many different sets. This package provides a way to include any icon found on this site _on-demand_.

1. Visit [ux.symfony.com/icons](https://ux.symfony.com/icons) and search for an icon
   you'd like to use. Once you find one you like, copy one of the code snippets provided.
2. Paste the snippet into your twig template and the icon will be automatically fetched (and cached).
3. That's it!

> [!NOTE]
> [Local SVG Icons](#local-svg-icons) of the same name will have precedence over _on-demand_ icons.

#### Import Command

You can import any icon from [ux.symfony.com/icons](https://ux.symfony.com/icons) to your local
directory using the `ux:icons:import` command:

 ```bash
 bin/console ux:icons:import flowbite:user-solid # saved as `flowbite/user-solid.svg` and name is `flowbite:user-solid`

 # import several at a time
 bin/console ux:icons:import flowbite:user-solid flowbite:home-solid
 ```

## Usage

```twig
{{ ux_icon('user-profile', {class: 'w-4 h-4'}) }} <!-- renders "user-profile.svg" -->

{{ ux_icon('sub-dir:user-profile', {class: 'w-4 h-4'}) }} <!-- renders "sub-dir/user-profile.svg" (sub-directory) -->

{{ ux_icon('flowbite:user-solid') }} <!-- renders "flowbite:user-solid" from ux.symfony.com -->
```

### HTML Syntax

> [!NOTE]
> `symfony/ux-twig-component` is required to use the HTML syntax.

```html
<twig:UX:Icon name="user-profile" class="w-4 h-4" /> <!-- renders "user-profile.svg" -->

<twig:UX:Icon name="sub-dir:user-profile" class="w-4 h-4" /> <!-- renders "sub-dir/user-profile.svg" (sub-directory) -->

<twig:UX:Icon name="flowbite:user-solid" /> <!-- renders "flowbite:user-solid" from ux.symfony.com -->
```

## Caching

To avoid having to parse icon files on every request, icons are cached.

In production, you can pre-warm the cache by running the following command:

```bash
bin/console ux:icons:warm-cache
```

This command looks in all your twig templates for `ux_icon` calls and caches the icons it finds.

> [!NOTE]
> During development, if you change an icon, you will need to clear the cache (`bin/console cache:clear`)
> to see the changes.

## Full Default Configuration

```yaml
ux_icons:
    # The local directory where icons are stored.
    icon_dir: '%kernel.project_dir%/assets/icons'

    # Default attributes to add to all icons.
    default_icon_attributes:
        # Default:
        fill: currentColor

    # Configuration for the Iconify.design functionality.
    iconify:
       enabled:              true

       # The endpoint for the Iconify API.
       endpoint:             'https://api.iconify.design'
```
