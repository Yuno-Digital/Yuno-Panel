# Plugins

Drop plugin folders here. Each plugin is a directory with a `plugin.json`
manifest; it shows up under **Admin → Plugins** where it can be enabled.

```
plugins/
  hello-world/
    plugin.json
    src/HelloWorldServiceProvider.php
```

`plugin.json`:

```json
{
  "id": "hello-world",
  "name": "Hello World",
  "version": "1.0.0",
  "description": "Example plugin.",
  "author": "you@example.com",
  "namespace": "Yuno\\Plugins\\HelloWorld",
  "provider": "Yuno\\Plugins\\HelloWorld\\HelloWorldServiceProvider"
}
```

When enabled, the panel registers the plugin's `namespace` (PSR-4, rooted at the
plugin's `src/`) and boots its `provider` — a normal Laravel service provider, so
it can add routes, views, migrations, etc.

Get plugins from the [plugins repository](https://github.com/Yuno-Digital/Yuno-Panel-Plugins).
