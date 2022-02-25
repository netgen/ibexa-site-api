Custom controllers
==================

Implementing a custom controller is similar to the vanilla Ibexa. First, you have to implement
it with extending the Site API base controller:

.. code-block:: php

    namespace AppBundle\Controller;

    use Netgen\Bundle\IbexaSiteApiBundle\Controller\Controller;
    use Netgen\Bundle\IbexaSiteApiBundle\View\ContentView;

    class DemoController extends Controller
    {
        /**
         * @param \Netgen\Bundle\IbexaSiteApiBundle\View\ContentView $view
         *
         * @return \Netgen\Bundle\IbexaSiteApiBundle\View\ContentView
         */
        public function viewArticleAction(ContentView $view)
        {
            $content = $view->getSiteContent();
            $location = $view->getSiteLocation();

            $filterService = $this->getSite()->getFilterService();

            $hasRelatedItems = false;

            if ($content->hasField('related') && !$content->getField('related')->isEmpty()) {
                $hasRelatedItems = true;
            }

            // Your other custom logic here
            // ...

            // Add variables to the view
            $view->addParameters([
                'has_related_items' => $hasRelatedItems,
            ]);

            return $view;
        }
    }

Since autowiring is enabled, this is sufficient to use your controller in the view configuration:

.. code-block:: yaml

    ezpublish:
        system:
            frontend_group:
                ng_content_view:
                    full:
                        article:
                            template: "@App/content/full/article.html.twig"
                            controller: AppBundle\Controller\DemoController:viewArticleAction
                            match:
                                Identifier\ContentType: article
