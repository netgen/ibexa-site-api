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

.. _cross_siteaccess_content:

Cross-siteaccess Content
~~~~~~~~~~~~~~~~~~~~~~~~

Cross-siteaccess Content is a feature that enables automatic loading and routing of Content and
Locations in the same Repository, but across different siteaccesses. It's intended for single
Repository multisite installations, where single Repository contains Content intended for different
siteaccesses. Typically, such siteaccesses are configured with different Content tree root
Location IDs. The feature is implemented at the PHP API and Symfony Router level, and it will
work automatically when enabled without requiring special considerations from the developer, both
from PHP and Twig.

However, several caveats apply:

.. caution::

    Search is not affected by Cross-siteaccess Content feature. The way search is implemented
    makes possible to find Content and Locations only for one language configuration, of
    a single (current) siteaccess.

    You can still search across the whole Repository, but, out of the box, doing that will not
    take into account the matching siteaccess language configuration of a specific Content item,
    or whether such Content item can be rendered or linked on a current siteaccess. Trying to
    take care of that post-search execution would only create inconsistencies in the result set.

.. caution::

    Given search is not the recommended way to obtain the Content from a different siteaccess,
    it's possible to obtain such Content only by loading it directly or by creating a relation
    to it.

.. caution::

    No provisions are made out of the box for rendering Content from a different
    siteaccess. This is possible if you take care of configuring the view to render
    such Content on a current siteaccess, but otherwise, out of the box, such Content is only
    safe for linking.

Cross-siteaccess Content is enabled by default, but if needed, it can be disabled per siteaccess
with ``ng_site_api.cross_siteaccess_content.enabled`` configuration option:

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_site_api:
                    cross_siteaccess_content:
                        enabled: false

Or as a shortcut configuration:

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_site_api:
                    cross_siteaccess_content: false

.. note::

    An abstract class for implementing a custom siteaccess resolver is provided, which means
    you can implement and configure your own resolver if the provided one does not match your
    use case.

Matching process
----------------

The logic for resolving the best matching siteaccess considers the following (not in the given
order):

- Current siteaccess
    - Content tree root Location ID
    - prioritized languages configuration
    - excluded URI prefixes configuration (as "external subtree roots")
- Matching siteaccess
    - Content tree root Location ID
    - prioritized languages configuration
- Location
    - Available siteaccesses by the configured Content tree root Location IDs
    - Content translations
    - Content always available flag

.. note::

    The matching process is described below, but the rules could be dense and it might be hard to
    understand all the implications right away. You should look into the test cases to better
    understand the matching logic. They were written to simulate the siteaccess configuration and
    to be easy to read.

Current siteaccess will always be preferred if it matches the given **context**, meaning given the
Location's subtree, available translations and always available flag. Otherwise, the siteaccess
will be chosen among the siteaccesses that match the given context.

If no siteaccess matches the Location's subtree, current siteaccess will be used as a fallback.

If Location is under the configured `external subtree roots`_, current siteaccess will be used.

In case when multiple (non-current) siteaccesses match the context, the logic will choose the best
matching one according to the current siteaccess configured prioritized languages. The matching
logic will respect the order/priority of the configured prioritized languages for both current and
potentially matching siteaccess, resulting in the selection of a siteaccess that allows highest
possible language of the current siteaccess at a highest possible position in the matching
siteaccess. The important thing to note here is that configured prioritized languages take
precedence over the available languages of the Location, which means that in some cases, the
resulting siteaccess will be the best one regarding the prioritized languages, but not the best
one regarding the Location's main language.

It's possible that matching a siteaccess by the current siteaccess prioritized languages will
produce no result. In that case all siteaccesses matching the context will be checked. By default,
the highest positioned match for the Location's main language will be returned if found. This
behavior can be disabled through the ``prefer_main_language`` option:

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_site_api:
                    cross_siteaccess_content:
                        enabled: true
                        prefer_main_language: false

If the main language was not matched or the option was disabled, the highest match for any of the
Location's languages will be returned. If multiple siteaccess match the language configuration
equally well, the first one, according to the configured siteaccess list, will be used. At the same
time, it's not defined in what order Location's languages will be checked, as this is not defined by
the Ibexa Content Repository; aside from the main language, there is no information about the
language priority of a Content item.

Finally, if none of the above matched the Location's context, current siteaccess will be returned if
it matches Location's subtree, otherwise, the first other siteaccess matching the Location's subtree
will be returned.

External subtree roots
----------------------

If ``excluded_uri_prefixes`` option is used on a siteaccess, it should be separately configured for
cross-siteaccess router with the corresponding Location IDs. That is needed because
``excluded_uri_prefixes`` is used for matching an URL, and the configured information as such is not
usable for generating an URL. Counterparts of the "excluded URI prefixes" for generating
cross-siteaccess links are called "external subtree roots", meaning they are external to tree root
of the current siteaccess, and can be configured per siteaccess with ``external_subtree_roots``
option. If the Location is found to be under the configured external tree root, the link to it will
be generated on the current siteaccess. Example configuration:

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_site_api:
                    cross_siteaccess_content:
                        enabled: true
                        external_subtree_roots:
                            - 42
                            - 256

If only a single items needs to be configured, you can also use shortcut configuration:

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_site_api:
                    cross_siteaccess_content:
                        enabled: true
                        external_subtree_roots: 42

Siteaccess and siteaccess group inclusion and exclusion
-------------------------------------------------------

If needed, you can include and exclude siteaccesses and siteaccess groups from the matching process,
for example:

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_site_api:
                    cross_siteaccess_content:
                        enabled: true
                        included_siteaccesses:
                            - sa_a
                            - sa_b
                        included_siteaccess_groups:
                            - group_1
                            - group_2
                        excluded_siteaccesses:
                            - sa_c
                            - sa_d
                        excluded_siteaccess_groups:
                            - group_3
                            - group_4

If only a single items needs to be configured, you can also use shortcut configuration:

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_site_api:
                    cross_siteaccess_content:
                        enabled: true
                        included_siteaccesses: sa_a
                        included_siteaccess_groups: group_1
                        excluded_siteaccesses: sa_c
                        excluded_siteaccess_groups: group_3

There are several specific rules to have in mind:

1. In case of ambiguous configuration, the exclusion will always win over the inclusion

2. Current siteaccess will be implicitly included, but it can be excluded if needed

3. For inclusion options, an empty array is interpreted as "include everything" instead
   "include nothing"

Relative and absolute URLs
--------------------------

Host part of the resulting URL will always be generated if requested, but otherwise only if
necessary, meaning only if it's different from the current host. This is also valid for ``path``
function in Twig, as otherwise it would not be possible to correctly link to a Location on a
siteaccess with a different host configuration.

All configuration options
-------------------------

All configuration options, showing the defaults:

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_site_api:
                    cross_siteaccess_content:
                        enabled: false
                        external_subtree_roots: []
                        included_siteaccesses: []
                        included_siteaccess_groups: []
                        excluded_siteaccesses: []
                        excluded_siteaccess_groups: []
                        prefer_main_language: true

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
Redirections have their own semantic configuration under ``redirect`` key in configuration for a
particular Content view. Available parameters and their default values are:

- ``target`` - identifies the redirect target

    Redirect target can be a ``Content``, ``Location`` or a ``Tag`` object, a Symfony route, or a
    full URL.

- ``target_parameters: []`` - Symfony route parameters used when the target is a Symfony route
- ``permanent: false`` - whether the redirect will be permanent or temporary (``301`` or ``302``)
- ``keep_request_method: false`` - whether to keep the request method

    If enabled, this will result in ``308`` for a permanent and ``307`` for a temporary redirect.

- ``absolute: false`` - whether the generated URL will be absolute or relative

Parameter expressions
---------------------

When defining parameters it's possible to use expressions. These are evaluated by Symfony's
`Expression Language <https://symfony.com/doc/current/components/expression_language.html>`_
component, whose syntax is based on Twig and is documented `here <https://symfony.com/doc/current/components/expression_language/syntax.html>`_.

Expression strings are recognized by ``@=`` prefix. Following sections describe available objects,
services and functions.

Content and Location objects
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

:ref:`Site API Content object<content_object>` is available as ``content``. For example you could
redirect to the main ``Location`` of the related ``Content`` through the ``internal_redirect``
field:

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_content_view:
                    container:
                        redirect:
                            target: '@=content.getFieldRelation("internal_redirect")'
                        match:
                            Identifier\ContentType: container

:ref:`Site API Location object<location_object>` is available as ``location``. In the following
example we use it to redirect to the parent ``Location``:

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_content_view:
                    container:
                        redirect:
                            target: '@=location.parent'
                            permanent: true
                            keep_request_method: false
                        match:
                            Identifier\ContentType: container

Configuration
^^^^^^^^^^^^^

Ibexa ConfigResolver service is available as ``configResolver``. Through it you can access
dynamic (per siteaccess) configuration, for example:

.. code-block:: yaml

    ngsite.eng.redirect: https://netgen.io
    ngsite.jpn.redirect: some_symfony_route

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_content_view:
                    container:
                        redirect:
                            target: '@=configResolver.getParameter("redirect", "ngsite")'
                        match:
                            Identifier\ContentType: container

Function ``config(name, namespace = null, scope = null)`` is a shortcut to ``getParameter()`` method
of ``ConfigResolver`` service:

.. code-block:: yaml

    ngsite.eng.redirect: https://netgen.io
    ngsite.jpn.redirect: some_symfony_route

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_content_view:
                    container:
                        redirect:
                            target: '@=config("redirect", "ngsite")'
                        match:
                            Identifier\ContentType: container

Named Objects
^^^^^^^^^^^^^

Named objects feature provides a way to configure specific objects (``Content``, ``Location`` and
``Tag``) by name and ID, and a way to access them by name from PHP, Twig and Query Type
configuration. Site API NamedObjectProvider service is available as ``namedObject``. Its purpose is
providing access to configured named objects.

.. note::

    Configuration of named objects is documented in more detail :ref:`below<named_object_configuration>`.

The following example shows how to configure redirect to a homepage named ``Location``:

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_site_api:
                    named_objects:
                        locations:
                            homepage: 2

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_content_view:
                    container:
                        redirect:
                            target: '@=namedObject.getLocation("homepage")'
                        match:
                            Identifier\ContentType: container

Shortcut functions are available for accessing each type of named object directly:

- ``namedContent(name)``

    Provides access to named Content.

- ``namedLocation(name)``

    Provides access to named Location.

- ``namedTag(name)``

    Provides access to named Tag.

With the shortcut functions, the example from the above can be written as:

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_content_view:
                    container:
                        redirect:
                            target: '@=namedLocation("homepage")'
                        match:
                            Identifier\ContentType: container

Container parameters
^^^^^^^^^^^^^^^^^^^^

Access to the container parameters is possible both by using the parameter directly, or by using it
through the ``parameter`` function, which also enables negating a boolean parameter value:

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_content_view:
                    match_all:
                        redirect:
                            target: 'login'
                            target_parameters:
                                foo: '@=config("bar")'
                            permanent: '@=!parameter("kernel.debug")'
                            keep_request_method: '%kernel.debug%'
                        match: ~

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
-------------

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
---------------

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
