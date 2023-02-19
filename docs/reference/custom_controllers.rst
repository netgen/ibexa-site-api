Custom controllers
==================

Implementing a custom controller is quite similar to how you would do it with the vanilla Ibexa CMS.
The only difference would be using Site API version of the ``ContentView`` object, as shown in the example below.

Site API comes with a base controller implementation that contains a number of subscribed services that
you will frequently need in development. To take advantage of it, implement your own controller by extending it:

.. code-block:: php

    namespace App\Controller;

    use Netgen\Bundle\IbexaSiteApiBundle\Controller\Controller;
    use Netgen\Bundle\IbexaSiteApiBundle\View\ContentView;

    class DemoController extends Controller
    {
        public function __invoke(ContentView $view): ContentView
        {
            $content = $view->getSiteContent();
            $location = $view->getSiteLocation();

            // Your custom logic here
            // ...

            return $view;
        }
    }

And if you have autoconfiguration enabled, this is already sufficient to use your controller
in the Content view configuration:

.. code-block:: yaml

    services:
        _defaults:
            autowire: true
            autoconfigure: true

        App\Controller\:
            resource: '../src/Controller/*'

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_content_view:
                    full:
                        article:
                            template: "@App/content/full/article.html.twig"
                            controller: App\Controller\DemoController
                            match:
                                Identifier\ContentType: article

If you are not using container automation, here's an example relying on the base controller service definition:

.. code-block:: yaml

    services:
        App\Controller\DemoController:
            parent: netgen.ibexa_site_api.controller.base
            tags:
                - { name: 'container.service_subscriber' }

And a fully expanded example:

.. code-block:: yaml

    services:
        App\Controller\DemoController:
            calls:
                - setContainer: ['@Psr\Container\ContainerInterface']
            tags:
                - { name: 'container.service_subscriber' }
            public: true
