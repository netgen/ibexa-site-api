All tag fields Location relations Query Type
================================================================================

This Query Type is used to build queries that fetch Locations with Content tag field relations from
all tag fields of a given Content.

.. hint::

    Tag field Content relations are Content items tagged with a tag contained in the tag fields of a
    given Content.

.. note::

    Only visible main Locations of the related Content will be used.

.. hint::

    This query type assumes `Netgen's TagsBundle`_ is used for tagging functionality.

+-------------+------------------------------------------------------------------------------------+
| Identifier  | ``SiteAPI:Location/Relations/AllTagFields``                                        |
+-------------+------------------------------------------------------------------------------------+
| Own         | - `content`_                                                                       |
| conditions  | - `exclude_self`_                                                                  |
+-------------+------------------------------------------------------------------------------------+
| Inherited   | - `main`_                                                                          |
| Location    | - `priority`_                                                                      |
| conditions  | - `visible`_                                                                       |
+-------------+------------------------------------------------------------------------------------+
| Common      | - `content_type`_                                                                  |
| Content     | - `field`_                                                                         |
| conditions  | - `is_field_empty`_                                                                |
|             | - `creation_date`_                                                                 |
|             | - `modification_date`_                                                             |
|             | - `section`_                                                                       |
|             | - `state`_                                                                         |
+-------------+------------------------------------------------------------------------------------+
| Common      | - `limit`_                                                                         |
| query       | - `offset`_                                                                        |
| parameters  | - `sort`_                                                                          |
+-------------+------------------------------------------------------------------------------------+

Examples
--------------------------------------------------------------------------------

On full view for ``product`` type Content fetch all Locations with Content of type ``article`` that
is tagged with any of the tags from the given product. Sort them by name and paginate them by 10 per
page using URL query parameter ``page``:

.. code-block:: yaml

    ibexa:
        system:
            frontend_group:
                ng_content_view:
                    full:
                        product:
                            template: '@ibexadesign/content/full/product.html.twig'
                            match:
                                Identifier\ContentType: product
                            queries:
                                related_articles:
                                    query_type: SiteAPI:Location/Relations/AllTagFields
                                    max_per_page: 10
                                    page: '@=queryParam("page", 1)'
                                    parameters:
                                        content_type: article
                                        sort: name

.. code-block:: twig

    {% set articles = ng_query( 'related_articles' ) %}

    <h3>Related articles</h3>

    <ul>
    {% for article in articles %}
        <li>{{ article.content.name }}</li>
    {% endfor %}
    </ul>

    {{ pagerfanta( articles, 'twitter_bootstrap' ) }}

Own conditions
--------------------------------------------------------------------------------

``content``
~~~~~~~~~~~~

Defines the source (from) relation Content, which is the one containing tag fields.

.. note::

  This condition is required. It's also automatically set to the ``Content`` instance resolved by
  the view builder if the query is defined in the view builder configuration.

- **value type**: ``Content``
- **value format**: ``single``
- **operators**: none
- **target**: none
- **required**: ``true``
- **default**: not defined

Examples:

.. code-block:: yaml

    # this is also automatically set when using from view builder configuration
    content: '@=content'

.. code-block:: yaml

    # fetch relations from Content's main Location parent Location's Content
    content: '@=content.mainLocation.parent.content'

.. code-block:: yaml

    # fetch relations from Content's main Location parent Location's parent Location's Content
    content: '@=content.mainLocation.parent.parent.content'

``exclude_self``
~~~~~~~~~~~~~~~~

Defines whether to include Content defined by the ``content`` condition in the result set.
If ``null`` is used as a value, the condition won't be added.

- **value type**: ``boolean``, ``null``
- **value format**: ``single``
- **operators**: none
- **target**: none
- **required**: ``false``
- **default**: ``true``

Examples:

.. code-block:: yaml

    # do not include the source relation Content, this is also the default behaviour
    exclude_self: true

.. code-block:: yaml

    # include the source relation Content
    exclude_self: false

Inherited Location conditions
--------------------------------------------------------------------------------
.. include:: /reference/query_types/parameters/common/location/main/title.rst.inc

Defines whether returned Locations are main Locations or not.
Use ``true`` to get main Locations (which is also the default behaviour), ``false`` to get non-main Locations and ``null`` to get both.

- **value type**: ``boolean``, ``null``
- **value format**: ``single``
- **operators**: ``eq``
- **target**: none
- **required**: ``false``
- **default**: ``true``

.. include:: /reference/query_types/parameters/common/location/main/examples.rst.inc

.. include:: /reference/query_types/parameters/common/location/priority.rst.inc

.. include:: /reference/query_types/parameters/common/location/visible/title.rst.inc

Defines whether returned Locations are main Locations or not.
Use ``true`` to get main Locations (which is also the default behaviour), ``false`` to get non-main Locations and ``null`` to get both.

- **value type**: ``boolean``, ``null``
- **value format**: ``single``
- **operators**: ``eq``
- **target**: none
- **required**: ``false``
- **default**: ``true``

.. include:: /reference/query_types/parameters/common/location/visible/examples.rst.inc

.. include:: /reference/query_types/parameters/common_content_parameters.rst.inc
.. include:: /reference/query_types/parameters/common_query_parameters.rst.inc

.. _`Netgen's TagsBundle`: https://github.com/netgen/TagsBundle
