Installation
============

To install Site API first add it as a dependency to your project:

.. code-block:: console

    composer require netgen/ibexa-site-api

Once Site API is installed, activate the bundle in ``config/bundles.php`` file by adding it to the
returned array, together with other required bundles:

.. code-block:: php

    <?php

    return [
        //...

        Netgen\Bundle\IbexaSearchExtraBundle\IbexaSiteApiBundle\NetgenIbexaSiteApiBundle::class => ['all' => true],
        Netgen\Bundle\IbexaSearchExtraBundle\NetgenIbexaSearchExtraBundle::class => ['all' => true],
    }

And that's it. Once you finish the installation you will be able to use Site API services as you
would normally do in a Symfony application. However, at this point Site API is not yet fully
enabled. That is done per siteaccess, see :doc:`Configuration </reference/configuration>` page to
learn more.
