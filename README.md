# translator-bundle

A Symfony2 bundle to help you to translate your Symfony2 applications.

## Installation

There are two modes of install this bundle: **in the dev environment** (recommended) or **in the prod environment** (not recommended).

### Installation in the dev environment

This is the recommended method.

#### Step 1: Download the Bundle

Open a command console, change to your project directory and execute the following command to download the latest version of this bundle:

```bash
$ composer require davamigo/translator-bundle dev-master
```

*Note*: You can substitute `dev-master` for a version number like `1.0.0` or `1.0.x` or `^1.0` or something similar. See [Packagist.com](https://packagist.org/packages/davamigo/translator-bundle) or [Github.com](https://github.com/davamigo/translator-bundle) to chose the right version.

#### Step 2: Enable the Bundle

Enable the bundle by adding it to the list of registered bundles in the *dev* environment of the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'), true)) {
            $bundles = array_merge($bundles, array(
                // ...
                new Davamigo\TranslatorBundle\DavamigoTranslatorBundle()
            ));
        }
        // ...
    }
    // ...
}
```

#### Step 3: Load the Routes of the Bundle

Load the routes of the bundle by adding this configuration in the the `app/config/routing_dev.yml` file:

```yaml
# app/config/routing_dev.yml
# ...

davamigo_translator:
    resource: "@DavamigoTranslatorBundle/Resources/config/routing.yml"
    prefix:   /translator

# ...
```

*Note*: You can change the prefix route `/translator` by your own route: `/translation` or `/admin/translator` or something similar.

#### Step 4: Add the bundle to assetic

This bundle uses some JavaScript, CSS and font files and needs to be added to the assetic section of the `app/config/config_dev.yml` file:

```yaml
# app/config/config_dev.yml
# ...

# Assetic Configuration
assetic:
    # ...
    bundles: [ '...', 'DavamigoTranslatorBundle' ]

# ...
```

#### Step 5: Enable the Symfony translation service

You must uncomment the translator item in the frameword section of the `app/config/config.yml` file:

```yaml
# app/config/config.yml
# ...

framework:
    # ...
    translator: { fallbacks: ["%locale%"] }

# ...
```

---

### Installation in prod environment

This installation is not recommended.

#### Step 1: Download the Bundle

Open a command console, change to your project directory and execute the following command to download the latest version of this bundle:

```bash
$ composer require davamigo/translator-bundle dev-master
```

*Note*: You can substitute `dev-master` for a version number like `1.0.0` or `1.0.x` or `^1.0` or something similar. See [Packagist.com](https://packagist.org/packages/davamigo/translator-bundle) or [Github.com](https://github.com/davamigo/translator-bundle) to chose the right version.

#### Step 2: Enable the Bundle

Enable the bundle by adding it to the list of registered bundles in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Davamigo\TranslatorBundle\DavamigoTranslatorBundle()
        );
        // ...
    }
    // ...
}
```

#### Step 3: Load the Routes of the Bundle

Load the routes of the bundle by adding this configuration in the the `app/config/routing.yml` file:

```yaml
# app/config/routing.yml
# ...

davamigo_translator:
    resource: "@DavamigoTranslatorBundle/Resources/config/routing.yml"
    prefix:   /translator

# ...
```

*Note*: You can change the prefix route `/translator` by your own route: `/translation` or `/admin/translator` or something similar.

#### Step 4: Secure the route

This step is optional but strongly recommended.

```yaml
# app/config/security.yml
security:
    # ...
    access_control:
        - { path: "^/translator", role: ROLE_ADMIN }
        # ...
```

*Note*: If you changed the route in the step 4, you must change the route here too.

#### Step 5: Add the bundle to assetic

This bundle uses some JavaScript, CSS and font files and needs to be added to the assetic section of the `app/config/config.yml` file:

```yaml
# app/config/config.yml
# ...

# Assetic Configuration
assetic:
    # ...
    bundles: [ '...', 'DavamigoTranslatorBundle' ]

# ...
```
#### Step 6: Enable the Symfony translation service

You must uncomment the translator item in the frameword section of the `app/config/config.yml` file:

```yaml
# app/config/config.yml
# ...

framework:
    # ...
    translator: { fallbacks: ["%locale%"] }

# ...
```
