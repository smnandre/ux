# Symfony UX Icons

## Installation

```bash
composer require symfony/ux-icons
```

## Icons?

No icons are provided by this package but there are several ways to include and render icons. 

### Local SVG Icons

Add your svg icons to the `assets/icons/` directory and commit them.
The name of the file is used as the _name_ of the icon (`name.svg` will be named `name`).
If located in a subdirectory, the _name_ will be `sub-dir:name`.

### Iconify Icons

[Iconify Design](https://iconify.design/) is a huge searchable repository of icons from many different icon sets.
This package provides a way to include any icon found on this site _on-demand_.

1. Visit [Iconify Design](https://icon-sets.iconify.design/) and search for an icon
   you'd like to use. Once you find one you like, visit the icon's profile page and use the widget
   to copy its name. For instance, https://icon-sets.iconify.design/flowbite/user-solid/ has the name
   `flowbite:user-solid`.
2. Just use this name in the [`ux_icon()`](#usage) function and the icon will be fetched (and cached)
   from the Iconify API.
3. That's it!

> [!NOTE]
> [Local SVG Icons](#local-svg-icons) of the same name will have precedence over _Iconify-on-demand_ icons.

#### Import Command

You can import any icon from Iconify to your local directory using the `ux:icons:import` command:

 ```bash
 bin/console ux:icons:import flowbite:user-solid # saved as `flowbite/user-solid.svg` and name is `flowbite:user-solid`

 # import several at a time
 bin/console ux:icons:import flowbite:user-solid flowbite:home-solid
 ```

## Usage

```twig
{{ ux_icon('user-profile', {class: 'w-4 h-4'}) }} <!-- renders "user-profile.svg" -->

{{ ux_icon('sub-dir:user-profile', {class: 'w-4 h-4'}) }} <!-- renders "sub-dir/user-profile.svg" (sub-directory) -->

{{ ux_icon('flowbite:user-solid') }} <!-- renders "flowbite:user-solid" from Iconify -->
```

### HTML Syntax

> [!NOTE]
> `symfony/ux-twig-component` is required to use the HTML syntax.

```html
<twig:UX:Icon name="user-profile" class="w-4 h-4" /> <!-- renders "user-profile.svg" -->

<twig:UX:Icon name="sub-dir:user-profile" class="w-4 h-4" /> <!-- renders "sub-dir/user-profile.svg" (sub-directory) -->

<twig:UX:Icon name="flowbite:user-solid" /> <!-- renders "flowbite:user-solid" from Iconify -->
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
