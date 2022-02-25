Migration
=========

If you are starting with a new project on top of vanilla Ibexa, then you're starting with a
clean slate and of course there is no need to migrate anything. In that case it's enough to :doc:`install </reference/installation>`
and :doc:`configure </reference/configuration>` the Site API and you can start working with it.

If that's the case, we recommend that you look into our `Media Site`_, which is built with Site API
and will provide you with a comprehensive base for building a web project on Ibexa.

On the other hand if you want to add the Site API to an existing project or you have a base site of
your own, read on to find out about your options.

.. _Media site: https://github.com/netgen/media-site

Choosing your migration strategy
--------------------------------

You can :doc:`install </reference/installation>` the Site API on a existing project without worrying
that something will break -- everything should just keep working as before. However, nothing will
use the Site API -- you will first have to develop new features or migrate existing ones.

At this point, you can:

1. use Site API services as you would normally do in a Symfony application. For example you could
   use it in a custom route.

2. use Site API's view :doc:`configuration </reference/configuration>`, available under
   ``ng_content_view`` key. You need to know that Ibexa URL alias routes still won't be
   handled through it at this point. Until you explicitly turn that on for a siteaccess or configure
   view fallback, you can only use it by making a subrequest to Site API's Content view controller
   ``ng_content::viewAction``.

Handling Ibexa URL alias routes through Site API's view configuration has to be enabled per
siteaccess, with the following configuration:

.. code-block:: yaml

    ezpublish:
        system:
            frontend_group:
                ng_site_api:
                    site_api_is_primary_content_view: true

Once you do this, all URL alias routes on the siteaccess will be handled through Site API's view
configuration. That means you will need to migrate or adapt all full view templates, otherwise
expect that things will break. Similar to the point 2. from above will be valid for Ibexa's
view configuration, available under ``content_view`` key. You will still be able to use it, but
unless you configure view fallback, that will be possible only through explicit subrequests to
Ibexa's view controller ``ez_content::viewAction``.

You can configure automatic :ref:`view fallback<content_view_fallback_configuration>`, from Site API
(if ``site_api_is_primary_content_view`` is enabled) to Ibexa, and from Ibexa
(when ``site_api_is_primary_content_view`` is disabled) to Site API. This is controlled
through the ``fallback_to_secondary_content_view`` configuration option:

.. code-block:: yaml

    ezpublish:
        system:
            frontend_group:
                ng_site_api:
                    fallback_to_secondary_content_view: false

If you are introducing Site API into an existing project, configuring automatic view fallback will
enable having a fully functional site from the beginning. If needed, it's also possible to configure
fallback manually, per content view.

In Ibexa pagelayout is configured separately from content view configuration. The configured
pagelayout is available in the content view templates as ``pagelayout`` variable, which is usually
extended in full view templates. When using both views at the same time, you will have to choose
which one to support with your configured pagelayout - Ibexa with its Content and Location
objects, or Site API with its own Content and Location counterparts. Whichever you choose, you can
support the other one by defining the pagelayout explicitly, instead using it through a configured
variable.

All Site API objects contain their Ibexa counterparts. This will enable initial mixing of both
Site API and vanilla Ibexa ways of doing things. Coupled with content view fallback, you will
be able to migrate your project one step at a time.

Knowing all that gives you quite some flexibility in choosing exactly how you want to adapt your
project to use Site API.

Comparison with Ibexa
---------------------

Here's a comparison table of Site API and `Ibexa Twig functions`_ to provide a quick overview
of changes needed in the templates.

.. _Ibexa Twig functions: https://doc.ezplatform.com/en/2.2/guide/twig_functions_reference/

+---------------------------------------------------------------------+-------------------------------------------------------------------------------+
| Ibexa                                                               | Netgen's Site API                                                             |
+=====================================================================+===============================================================================+
| ``{{ ibexa_content_name( content ) }}``                             | ``{{ content.name }}``                                                        |
+---------------------------------------------------------------------+-------------------------------------------------------------------------------+
| ``{{ ibexa_field_name( content, 'title' ) }}``                      | ``{{ content.fields.title.name }}``                                           |
+---------------------------------------------------------------------+-------------------------------------------------------------------------------+
| ``{{ ibexa_field_description( content, 'title' ) }}``               | ``{{ content.fields.title.description }}``                                    |
+---------------------------------------------------------------------+-------------------------------------------------------------------------------+
| ``{{ ibexa_field( content, 'title' ) }}``                           | ``{{ content.fields.title }}``                                                |
+---------------------------------------------------------------------+-------------------------------------------------------------------------------+
| ``{{ ibexa_render_field( content, 'title' ) }}``                    | ``{{ ng_render_field( content.fields.title ) }}``                             |
+---------------------------------------------------------------------+-------------------------------------------------------------------------------+
| ``{{ ibexa_field_value( content, 'title' ) }}``                     | ``{{ content.fields.title.value }}``                                          |
+---------------------------------------------------------------------+-------------------------------------------------------------------------------+
| ``{{ ibexa_is_field_empty( content, 'title' ) }}``                  | ``{{ content.fields.title.empty }}``                                          |
+---------------------------------------------------------------------+-------------------------------------------------------------------------------+
| .. code::                                                           | .. code::                                                                     |
|                                                                     |                                                                               |
|     {{ ibexa_image_alias(                                           |      {{ ng_image_alias(                                                       |
|         content.field( 'image' ),                                   |          content.fields.image,                                                |
|         content.versionInfo,                                        |           'large'                                                             |
|         'large'                                                     |      ) }}                                                                     |
|     ) }}                                                            |                                                                               |
+---------------------------------------------------------------------+-------------------------------------------------------------------------------+

Search and replace regexes
--------------------------

Here are some regular expressions that you can use to migrate your Twig templates. The list is not
complete, but it should get you started. If you're using PHP Storm, follow the steps:

1. Open your PHPStorm
2. Navigate to template
3. Press CTRL + R or Command + R
4. Enter the one of the search/replace pairs from below and replace away

``ez_is_field_empty``
~~~~~~~~~~~~~~~~~~~~~

+--------------+-----------------------------------------------------------------------------------+
| search for   | ``ez_is_field_empty\s*\(\s*([a-zA-Z0-9\_]+)\s*,\s*['"]([a-zA-Z0-9\_]+)['"]\s*\)`` |
+--------------+-----------------------------------------------------------------------------------+
| replace with | ``$1.fields.$2.empty``                                                            |
+--------------+-----------------------------------------------------------------------------------+

``ez_field_value``
~~~~~~~~~~~~~~~~~~

+--------------+--------------------------------------------------------------------------------+
| search for   | ``ez_field_value\s*\(\s*([a-zA-Z0-9\_]+)\s*,\s*['"]([a-zA-Z0-9\_]+)['"]\s*\)`` |
+--------------+--------------------------------------------------------------------------------+
| replace with | ``$1.fields.$2.value``                                                         |
+--------------+--------------------------------------------------------------------------------+

``ez_render_field``
~~~~~~~~~~~~~~~~~~~

+--------------+----------------------------------------------------------------------------------+
| search for   | ``ez_render_field[ ]?\(\s+([a-zA-Z0-9\_]+),\s+['"]([a-zA-Z0-9\_]+)['"](.*?)?\)`` |
+--------------+----------------------------------------------------------------------------------+
| replace with | ``ng_render_field( $1.fields.$2$3 )``                                            |
+--------------+----------------------------------------------------------------------------------+
