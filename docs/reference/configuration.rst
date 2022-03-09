Configuration
=============

Site API has its own view configuration, available under ``ng_content_view`` key. Aside from
:doc:`Query Type </reference/query_types>` configuration that is documented separately, this is
exactly the same as Ibexa CMS default view configuration under ``content_view`` key. You can use
this configuration right after the installation, but note that it won't be used for full views
rendered for Ibexa CMS URL aliases right away. Until you configure that, it will be used only when
calling its controller explicitly with ``ng_content::viewAction``.

All other configuration is grouped under ``ng_site_api`` key under Ibexa CMS semantic
configuration. If you need to fetch this configuration directly in your code, combine
``ng_site_api`` with the specific key name, for example:

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_site_api:
                    site_api_is_primary_content_view: true

.. code-block:: php

    $configResolver->get('ng_site_api.site_api_is_primary_content_view');

**Content on this page:**

.. contents::
    :depth: 1
    :local:

Configure handling of URL aliases
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To use Site API view rules for pages rendered from Ibexa CMS URL aliases, you have to enable it
for a specific siteaccess with the following semantic configuration:

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_site_api:
                    site_api_is_primary_content_view: true

Here ``frontend_group`` is the siteaccess group (or a siteaccess) for which you want to activate the
Site API. This switch is useful if you have a siteaccess that can't use it, for example a custom
admin or intranet interface.

.. note::

    To use Site API view configuration automatically on pages rendered from Ibexa CMS URL aliases,
    you need to enable it manually per siteaccess.

Site API Content views
~~~~~~~~~~~~~~~~~~~~~~

Once you enable ``site_api_is_primary_content_view`` for a siteaccess, all your **full view**
templates and controllers will need to use Site API to keep working. They will be resolved from Site
API view configuration, available under ``ng_content_view`` key. That means Content and Location
variables inside Twig templates will be instances of Site API Content and Location value objects,
``$view`` variable passed to your custom controllers will be an instance of Site API ContentView
variable, and so on.

If needed you can still use ``content_view`` rules. This will allow you to have both Site API
template override rules as well as original Ibexa CMS template override rules, so you can rewrite
your templates bit by bit. You can decide which one to use by directly rendering either
``ng_content::viewAction`` or ``ibexa_content::viewAction`` controller.

It's also possible to configure fallback between Site API and Ibexa CMS views. With it, if the
rule is not matched in one view configuration, the fallback mechanism will try to match it in the
other. Find out more about that in the following section.

.. tip::

    | View configuration is the only Ibexa CMS configuration regularly edited
    | by frontend developers.

For example, if using the following configuration:

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_content_view:
                    line:
                        article:
                            template: '@App/content/line/article.html.twig'
                            match:
                                Identifier\ContentType: article
                content_view:
                    line:
                        article:
                            template: '@App/content/line/ibexa_article.html.twig'
                            match:
                                Identifier\ContentType: article

Rendering a line view for an article with ``ng_content::viewAction`` would use
``@App/content/line/article.html.twig`` template, while rendering a line view for an article with
``ibexa_content::viewAction`` would use ``@App/content/line/ibexa_article.html.twig`` template.

It is also possible to use custom controllers, this is documented on
:doc:`Custom controllers reference</reference/custom_controllers>` documentation page.

.. _content_view_fallback_configuration:

Content View fallback
~~~~~~~~~~~~~~~~~~~~~

You can configure fallback between Site API and Ibexa CMS views. Fallback can be controlled
through two configuration options (showing default values):

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_site_api:
                    fallback_to_secondary_content_view: true
                    fallback_without_subrequest: true

- ``fallback_to_secondary_content_view``

    With this option you control whether **automatic fallback** will be used. By default, automatic
    fallback is disabled. Secondary content view means the fallback can be used both from Site API
    to Ibexa CMS views, and from Ibexa CMS to Site API content views. Which one will be used is
    defined by ``site_api_is_primary_content_view`` configuration documented above.

- ``fallback_without_subrequest``

    With this option you can control whether the fallback will use a subrequest (default), or Twig
    functions that can render content view without a subrequest. That applies both to automatic and
    manually configured fallback. Rendering views without a subrequest is faster in debug mode,
    where profiling is turned on. Depending on the number of views used on a page, performance
    improvement when not using subrequest can be significant.

.. warning::

    Because of reverse siteaccess matching limitations, when ``fallback_without_subrequest`` is
    turned off, links in the preview in the admin UI will not be correctly generated. To work around
    that problem, turn the option on.

.. note::

    When fallback is enabled default templates for the primary view will not be used. Otherwise the
    fallback would never happen, because the primary view would always use the default templates
    instead of falling back to the secondary view. Similarly, when falling back to the secondary
    view, if its view configuration doesn't match, the default template of the secondary view will
    be rendered.


You can also configure fallback manually, per view. This is done by configuring a view to render one
of two special templates, depending if the fallback is from Site API to Ibexa CMS views or the
opposite.

- ``@NetgenIbexaSiteApi/content_view_fallback/to_ibexa/view.html.twig``

  This template is used for fallback from Site API to Ibexa CMS views. In the following example
  it's used to configure fallback for ``line`` view of ``article`` ContentType:

  .. code-block:: yaml

      ibexa:
          system:
              frontend_group:
                  ng_content_view:
                      line:
                          article:
                              template: '@NetgenIbexaSiteApi/content_view_fallback/to_ibexa/view.html.twig'
                              match:
                                  Identifier\ContentType: article

- ``@NetgenIbexaSiteApi/content_view_fallback/to_site_api/view.html.twig``

  This template is used for fallback from Ibexa CMS to Site API views. In the following example
  it's used to configure fallback for all ``full`` views:

  .. code-block:: yaml

      ibexa:
          system:
              frontend_group:
                  content_view:
                      full:
                          catch_all:
                              template: '@NetgenIbexaSiteApi/content_view_fallback/to_site_api/view.html.twig'
                              match: ~

.. _show_hidden_items_configuration:

Internal Content View route on frontend siteaccesses
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Ibexa allows use of internal Content View route from the admin UI on the frontend
siteaccesses. That might not be desirable in all cases, so Site API provides two configuration
options to control whether the internal route will be enabled on a frontend siteaccess and, if
enabled, whether it will permanently (HTTP code 308) redirect to the URL alias.

By default, both options are set to true and the route will be enabled and it will permanently
redirect to the URL alias:

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_site_api:
                    enable_internal_view_route: true
                    redirect_internal_view_route_to_url_alias: true

Configure showing hidden items
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can configure whether hidden Content and Location objects will be shown by default through
``show_hidden_items`` configuration option (``false`` by default):

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_site_api:
                    show_hidden_items: false

This affects loading Location's children and siblings, Content's relations and search through Query
Types. In Query Types you can override the configured option by explicitly defining ``visible``
condition, see :doc:`the Query Type documentation</reference/query_types>` for more details.

Redirections
~~~~~~~~~~~~

With Site API, it's also possible to configure redirects directly from the view configuration.
You can set up temporary or permanent redirect to either ``Content``, ``Location``, ``Tag``, Symfony route or any full url.

For the target configuration you can use expression language, meaning it is easily possible to redirect, for example,
to the parent of the current location, or to a named object.

Example configuration:

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_content_view:
                    container:
                        redirect:
                            target: '@=location.parent'
                            target_parameters:
                                foo: bar
                            permanent: false
                            keep_request_method: true
                        match:
                            Identifier\ContentType: container
                    article:
                        redirect:
                            target: '@=namedLocation("homepage")'
                            target_parameters:
                                foo: bar
                                siteaccess: cro
                            permanent: true
                            keep_request_method: '%kernel.debug%'
                            absolute: true
                        match:
                            Identifier\ContentType: article
                    category:
                        redirect:
                            target: '@=location.firstChild("article")'
                            permanent: true
                            keep_request_method: false
                        match:
                            Identifier\ContentType: category
                    news:
                        redirect:
                            target: 'login'
                            target_parameters:
                                foo: bar
                            permanent: false
                        match:
                            Identifier\ContentType: news
                    blog:
                        redirect:
                            target: 'https://netgen.io'
                        match:
                            Identifier\ContentType: blog

There also shortcuts available for simplified configuration:

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_content_view:
                    container:
                        temporary_redirect: '@=namedObject.getTag("running")'
                        match:
                            Identifier\ContentType: container
                    category:
                        permanent_redirect: '@=content.getFieldRelation("internal_redirect")'
                        match:
                            Identifier\ContentType: container

Which is functionally identical to:

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_content_view:
                    container:
                        redirect:
                            target: '@=namedObject.getTag("running")'
                            permanent: false
                            keep_request_method: true
                        match:
                            Identifier\ContentType: container
                    category:
                        redirect:
                            target: '@=content.getFieldRelation("internal_redirect")'
                            permanent: true
                            keep_request_method: true
                        match:
                            Identifier\ContentType: container

.. note::

    Configuration of named objects is documented in more detail below.

Shortcut functions are available for accessing each type of named object directly:

- ``namedContent(name)``

    Provides access to named Content.

- ``namedLocation(name)``

    Provides access to named Location.

- ``namedTag(name)``

    Provides access to named Tag.

.. _named_object_configuration:

Named objects
~~~~~~~~~~~~~

Named objects feature provides a way to configure specific objects (``Content``, ``Location`` and
``Tag``) by name and ID, and a way to access them by name from PHP, Twig and Query Type
configuration.

Example configuration:

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_site_api:
                    named_objects:
                        content:
                            certificate: 42
                            site_info: 'abc123'
                        locations:
                            homepage: 2
                            articles: 'zxc456'
                        tags:
                            categories: 24
                            colors: 'bnm789'

From the example, ``certificate`` and ``site_info`` are names of Content objects, ``homepage`` and
``articles`` are names of Location objects and ``categories`` and ``colors`` are names of Tag
objects. The example also shows it's possible to use both a normal ID (integer) or remote ID
(string). Hence, these two types of IDs are distinguished by their respective value type.

Configuring IDs through expressions
-----------------------------------

When defining parameters it's possible to use expressions. These are evaluated by Symfony's
`Expression Language <https://symfony.com/doc/current/components/expression_language.html>`_
component, whose syntax is based on Twig and is documented `here <https://symfony.com/doc/current/components/expression_language/syntax.html>`_.

Expression strings are recognized by ``@=`` prefix. Following sections describe available objects,
services and functions.

Configuration
~~~~~~~~~~~~~

Ibexa ConfigResolver service is available as ``configResolver``. Through it you can access
dynamic (per siteaccess) configuration, for example the location tree root:

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_site_api:
                    named_objects:
                        locations:
                            homepage: '@=configResolver.getParameter("content.tree_root.location_id")'

Function ``config(name, namespace = null, scope = null)`` is a shortcut to ``getParameter()`` method
of ``ConfigResolver`` service:

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_site_api:
                    named_objects:
                        locations:
                            homepage: '@=config("content.tree_root.location_id")'

Current user ID
~~~~~~~~~~~~~~~

Repository's current user ID is available as ``currentUserId`` variable:

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_site_api:
                    named_objects:
                        locations:
                            current_user: '@=currentUserId'

Accessing named objects
-----------------------

- access from PHP is :ref:`documented on the Services page<named_object_php>`
- access from Twig is :ref:`documented on Templating page<named_object_template>`
- access from Query Type configuration is :ref:`documented on Query Types page<named_object_query_types>`

.. _content_field_inconsistencies:

Content Field inconsistencies
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Sometimes when the content model is changed or for any reason the data is not consistent, it can
happen that some Content Fields are missing. In case of content model change that is a temporary
situation lasting while the data is being updated in the background. But even in the case of
inconsistent database, typically you do not want that to result in site crash.

To account for this Site API provides the following semantic configuration:

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_site_api:
                    fail_on_missing_field: true
                    render_missing_field_info: false

By default ``fail_on_missing_field`` is set to ``%kernel.debug%`` container parameter, which means
accessing a nonexistent field in ``dev`` environment will fail and result in a ``RuntimeException``.

On the other hand, when not in debug mode (in ``prod`` environment), the system will not crash, but
will instead return a special ``Surrogate`` type field, which always evaluates as empty and renders
to an empty string. In this case, a ``critical`` level message will be logged, so you can find and
fix the problem.

Second configuration option ``render_missing_field_info`` controls whether ``Surrogate`` field will
render as an empty string or it will render useful debug information. By default its value is
``false``, meaning it will render as an empty string. That behavior is also what you should use in
the production environment. Setting this option to ``true`` can be useful in debug mode, together
with setting ``fail_on_missing_field`` to ``false``, as that will provide a visual cue about the
missing field without the page crashing and without the need to go into the web debug toolbar to
find the logged message.

.. note::

    You can configure both ``render_missing_field_info`` and ``fail_on_missing_field`` per
    siteaccess or siteaccess group.
