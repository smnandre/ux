# Symfony UX Icons

## Setup

If planning on using the [`defer`](#deferred-icons) option, add the following to your base
template (`templates/base.html.twig`), right before the closing `</body>` tag:

```twig
{{ ux_defered_icons() }}
```

## Add Icons

No icons are provided by this package. Add your svg icons to the `templates/icons/` directory.
The name of the file is used as the name of the icon (`name.svg` will be named `name`).

When icons are rendered, any attributes (except `viewBox`) on the file's `<svg>` element will
be removed. This allows you to copy/paste icons from sites like
[heroicons.com](https://heroicons.com/) and not worry about hard-coded attributes interfering with
your design.

### Import Command

The [Iconify Design](https://iconify.design/) has a huge searchable repository of icons from
many different icon sets. This package provides a command to locally install icons from this
site.

1. Visit [Iconify Design](https://icon-sets.iconify.design/) and search for an icon
   you'd like to use. Once you find one you like, visit the icon's profile page and use the widget
   to copy its name. For instance, https://icon-sets.iconify.design/flowbite/user-solid/ has the name
   `flowbite:user-solid`.
2. Run the following command, replacing `flowbite:user-solid` with the name of the icon you'd like
   to install:

    ```bash
    bin/console ux:icons:import flowbite:user-solid # saved as `user-solid.svg` and name is `user-solid`

    # adjust the local name
    bin/console ux:icons:import flowbite:user-solid@user # saved as `user.svg` and name is `user`
   
    # import several at a time
    bin/console ux:icons:import flowbite:user-solid flowbite:home-solid
    ```

## Usage

```twig
{{ ux_icon('user-profile', {class: 'w-4 h-4'}) }} <!-- renders "user-profile.svg" -->

{{ ux_icon('sub-dir/user-profile', {class: 'w-4 h-4'}) }} <!-- renders "sub-dir/user-profile.svg" (sub-directory) -->
```

### Deferred Icons

By default, icons are rendered inline. If you have a lot of duplicate icons on a page, you can
_defer_ the rendering of an icon. This will render the icon once and then reuse the rendered
icon for all other instances.

```twig
{{ ux_icon('user-profile', {class: 'w-4 h-4', defer: true}) }}
```

> [!IMPORTANT]  
> `{{ ux_defered_icons() }}` ([as shown above](#setup)) needs to be on the page
> for deferred icons to work.

### List Available Icons

```bash
bin/console ux:icons:list
```

## Caching

To avoid having to parse icon files on every request, icons are cached (`app.cache` by
default but can be [configured](#full-default-configuration)).

If using a tagged cache adapter, cached icons are tagged with `ux-icon`.

During container warmup (`cache:warmup` and `cache:clear`), the icon cache is warmed.
This behavior can be disabled via [configuration](#full-default-configuration).

### Manual Cache Warmup

If you chose to disable container icon cache warmup, a warmup command is provided:

```bash
bin/console ux:icons:warm-cache
```

## Full Default Configuration

```yaml
ux_icons:

    # The local directory where icons are stored.
    icon_dir:             '%kernel.project_dir%/templates/icons'

    # The cache pool to use for icons.
    cache:                cache.app

    # Whether to warm the icon cache when the container is warmed up.
    cache_on_container_warmup: true

    # Default attributes to add to all icons.
    default_icon_attributes:

        # Default:
        fill:                currentColor

    # Default attributes to add to deferred icon set.
    default_deferred_attributes:

        # Default:
        style:               display:none;
```
