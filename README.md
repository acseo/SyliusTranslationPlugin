## Sylius Translation plugin

The main goal of this plugin is to enable you to edit translation phrases, without having to manually create or modify translation files on the server. This plugin allows you to change any phrase that is in Silyus in admin panel. All custom translations are saved in the `xliff` format.

![v0.3.0](http://i.piccy.info/i9/86d7046fd523f8ee3a6f3538e988a96e/1557071306/118770/1316169/Screenshot_20190505_184128.png)


In the admin panel view for each locale (enabled in the Sylius) shows how many messages have been translated, how many are left and translation progress.

![v0.3.0](http://i.piccy.info/i9/c5571e45f04d5ae2c8657bee83f98506/1557071334/128239/1316169/Screenshot_20190505_183825.png)

See in action on [youtube](https://www.youtube.com/watch?v=yGjeBMeTwqA):

[![IMAGE ALT TEXT HERE](https://img.youtube.com/vi/yGjeBMeTwqA/0.jpg)](https://www.youtube.com/watch?v=yGjeBMeTwqA)

## Installation

```bash
$ composer require yaroslavche/sylius-translation-plugin
```

Register bundle:
```php
# config/bundles.php

Yaroslavche\SyliusTranslationPlugin\YaroslavcheSyliusTranslationPlugin::class => ['all' => true],
```

Import services:
```yaml
# config/services.yaml

imports:
    # ...
    - { resource: "@YaroslavcheSyliusTranslationPlugin/Resources/config/services.yml" }
```

Import routing
```yaml
# config/routes.yaml

yaroslavche_sylius_translation_plugin:
    resource: "@YaroslavcheSyliusTranslationPlugin/Resources/config/admin_routing.yml"
```

Add twig_hook in v2 :

```yaml
sylius_twig_hooks:
    hooks:
        'sylius_admin.base#stylesheets':
            app_styles:
                template: 'admin/stylesheets.html.twig'
        'sylius_admin.base#javascripts':
            app_javascripts:
                template: 'admin/javascripts.html.twig'

        'sylius_shop.base#stylesheets':
            app_styles:
                template: 'shop/stylesheets.html.twig'
        'sylius_shop.base#javascripts':
            app_javascripts:
                template: 'shop/javascripts.html.twig'

        'sylius_admin.translation.index':
            sidebar:
                template: '@SyliusAdmin/shared/crud/common/sidebar.html.twig'
                priority: 200
            navbar:
                template: '@SyliusAdmin/shared/crud/common/navbar.html.twig'
                priority: 100
            content:
                template: '@YaroslavcheSyliusTranslationPlugin/translation.html.twig'
                priority: 0
```




```



Install assets and clear cache.
```bash
$ yarn build
$ bin/console assets:install
$ bin/console cache:clear
```

see on `/admin/translation/`
