Traits
======

Site API comes with several traits that will help you minimize the amount of boilerplate code.

PagerfantaTrait
---------------

This trait provides methods to obtain ``Pagerfanta`` pager instance using  :ref:`FilterService<filter_service>` or
:ref:`FindService<find_service>` directly from the given ``Query`` object.

+--------------------------------+----------------------------------------------------------+
| **FQN**                        | ``Netgen\IbexaSiteApi\Core\Traits\PagerfantaTrait``      |
+--------------------------------+----------------------------------------------------------+

.. note::

    Abstract controller from Site API already uses this trait.

The trait provides two methods for creating a ``Pagerfanta`` instance:

``getFilterPager()``
~~~~~~~~~~~~~~~~~~~~

This method will return ``Pagerfanta`` instance which will use  :ref:`FilterService<filter_service>` to load  its items.
Content or Location search will be used depending on the provided ``$query`` parameter instance.

+----------------------------------------+------------------------------------------------------------------------------------+
| **Parameters**                         | 1. ``Ibexa\Contracts\Core\Repository\Values\Content\Query $query``                 |
|                                        | 2. ``int $currentPage``                                                            |
|                                        | 3. ``int $maxPerPage``                                                             |
+----------------------------------------+------------------------------------------------------------------------------------+
| **Returns**                            | An instance of ``Pagerfanta\Pagerfanta``                                           |
+----------------------------------------+------------------------------------------------------------------------------------+
| **Example**                            | .. code-block:: php                                                                |
|                                        |                                                                                    |
|                                        |     use Ibexa\Contracts\Core\Repository\Values\Content\Query;                      |
|                                        |                                                                                    |
|                                        |     use Netgen\IbexaSiteApi\Core\Traits\PagerfantaTrait;                           |
|                                        |                                                                                    |
|                                        |     $query = new Query();                                                          |
|                                        |                                                                                    |
|                                        |     $pager = $this->getFilterPager($query, 1, 10);                                 |
|                                        |                                                                                    |
+----------------------------------------+------------------------------------------------------------------------------------+

``getFindPager()``
~~~~~~~~~~~~~~~~~~

This method will return ``Pagerfanta`` instance which will use  :ref:`FindService<find_service>` to load  its items.
Content or Location search will be used depending on the provided ``$query`` parameter instance.

+----------------------------------------+------------------------------------------------------------------------------------+
| **Parameters**                         | 1. ``Ibexa\Contracts\Core\Repository\Values\Content\Query $query``                 |
|                                        | 2. ``int $currentPage``                                                            |
|                                        | 3. ``int $maxPerPage``                                                             |
+----------------------------------------+------------------------------------------------------------------------------------+
| **Returns**                            | An instance of ``Pagerfanta\Pagerfanta``                                           |
+----------------------------------------+------------------------------------------------------------------------------------+
| **Example**                            | .. code-block:: php                                                                |
|                                        |                                                                                    |
|                                        |     use Ibexa\Contracts\Core\Repository\Values\Content\Query;                      |
|                                        |                                                                                    |
|                                        |     use Netgen\IbexaSiteApi\Core\Traits\PagerfantaTrait;                           |
|                                        |                                                                                    |
|                                        |     $query = new Query();                                                          |
|                                        |                                                                                    |
|                                        |     $pager = $this->getFindPager($query, 1, 10);                                   |
|                                        |                                                                                    |
+----------------------------------------+------------------------------------------------------------------------------------+

SearchResultExtractorTrait
--------------------------

This trait provides methods to extract type hinted value objects from the given ``SearchResult`` object.

.. note::

    Abstract controller from Site API already uses this trait.

+--------------------------------+---------------------------------------------------------------------+
| **FQN**                        | ``Netgen\IbexaSiteApi\Core\Traits\SearchResultExtractorTrait``      |
+--------------------------------+---------------------------------------------------------------------+

The trait provides three methods for extracting value objects:

``extractContentItems()``
~~~~~~~~~~~~~~~~~~~~~~~~~

This method will extract :ref:`Content items<content_object>` from the given ``SearchResult``.

+----------------------------------------+-------------------------------------------------------------------------------------------------------+
| **Parameters**                         | 1. ``Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult $searchResult``               |
+----------------------------------------+-------------------------------------------------------------------------------------------------------+
| **Returns**                            | An array of :ref:`Content items<content_object>`                                                      |
+----------------------------------------+-------------------------------------------------------------------------------------------------------+
| **Example**                            | .. code-block:: php                                                                                   |
|                                        |                                                                                                       |
|                                        |     use Netgen\IbexaSiteApi\Core\Traits\SearchResultExtractorTrait;                                   |
|                                        |                                                                                                       |
|                                        |     /** @var $searchResult \Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult */     |
|                                        |                                                                                                       |
|                                        |     $contentItems = $this->extractContentItems($searchResult);                                        |
|                                        |                                                                                                       |
+----------------------------------------+-------------------------------------------------------------------------------------------------------+

``extractLocations()``
~~~~~~~~~~~~~~~~~~~~~~

This method will extract :ref:`Locations<location_object>` from the given ``SearchResult``.

+----------------------------------------+-------------------------------------------------------------------------------------------------------+
| **Parameters**                         | 1. ``Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult $searchResult``               |
+----------------------------------------+-------------------------------------------------------------------------------------------------------+
| **Returns**                            | An array of :ref:`Locations<location_object>`                                                         |
+----------------------------------------+-------------------------------------------------------------------------------------------------------+
| **Example**                            | .. code-block:: php                                                                                   |
|                                        |                                                                                                       |
|                                        |     use Netgen\IbexaSiteApi\Core\Traits\SearchResultExtractorTrait;                                   |
|                                        |                                                                                                       |
|                                        |     /** @var $searchResult \Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult */     |
|                                        |                                                                                                       |
|                                        |     $locations = $this->extractLocations($searchResult);                                              |
|                                        |                                                                                                       |
+----------------------------------------+-------------------------------------------------------------------------------------------------------+

``extractValueObjects()``
~~~~~~~~~~~~~~~~~~~~~~~~~

This method will extract value objects from the given ``SearchResult``. The generic value object is not useful for type
hinting, as it's already hinted in the ``SearchResult``, but it enables you to avoid writing the code yourself.

+----------------------------------------+-------------------------------------------------------------------------------------------------------+
| **Parameters**                         | 1. ``Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult $searchResult``               |
+----------------------------------------+-------------------------------------------------------------------------------------------------------+
| **Returns**                            | An array of ``Ibexa\Contracts\Core\Repository\Values\ValueObject`` instances                          |
+----------------------------------------+-------------------------------------------------------------------------------------------------------+
| **Example**                            | .. code-block:: php                                                                                   |
|                                        |                                                                                                       |
|                                        |     use Netgen\IbexaSiteApi\Core\Traits\SearchResultExtractorTrait;                                   |
|                                        |                                                                                                       |
|                                        |     /** @var $searchResult \Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult */     |
|                                        |                                                                                                       |
|                                        |     $valueObjects = $this->extractValueObjects($searchResult);                                        |
|                                        |                                                                                                       |
+----------------------------------------+-------------------------------------------------------------------------------------------------------+

SiteAwareTrait
--------------

+--------------------------------+----------------------------------------------------------+
| **FQN**                        | ``Netgen\IbexaSiteApi\Core\Traits\SiteAwareTrait``       |
+--------------------------------+----------------------------------------------------------+

This trait provides setter injection of the ``Site`` object in your service. It provides methods for setting and getting
the ``Site`` instance:

``setSite()``
~~~~~~~~~~~~~

+----------------------------------------+--------------------------------------------+
| **Parameters**                         | 1. ``Netgen\IbexaSiteApi\API\Site $site``  |
+----------------------------------------+--------------------------------------------+
| **Returns**                            | Void                                       |
+----------------------------------------+--------------------------------------------+

``getSite()``
~~~~~~~~~~~~~

+----------------------------------------+---------------------------------------------------+
| **Parameters**                         | None                                              |
+----------------------------------------+---------------------------------------------------+
| **Parameters**                         | An instance of ``Netgen\IbexaSiteApi\API\Site``   |
+----------------------------------------+---------------------------------------------------+
