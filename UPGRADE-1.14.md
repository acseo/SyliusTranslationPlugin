# UPGRADE FROM `v1.13.X` TO `v1.14.x`

### Service alias change for locale provider

In Sylius 1.14, the locale provider service was renamed. If you were previously using the following configuration:

```yaml
$localeProvider: '@sylius.locale_provider'
```
You need to update it to:
```yaml
$localeProvider: '@sylius.provider.locale'
```