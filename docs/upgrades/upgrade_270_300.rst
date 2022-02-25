Upgrading from 2.7.0 to 3.0.0
=============================

Version 3.0.0 is a major release where all previous deprecations are removed.

Removed methods for loading ``ContentInfo``
-------------------------------------------

* ``Netgen\IbexaSiteApi\API\FilterService::filterContentInfo()``
* ``Netgen\IbexaSiteApi\API\FindService::findContentInfo()``
* ``Netgen\IbexaSiteApi\API\LoadService::loadContentInfo()``
* ``Netgen\IbexaSiteApi\API\LoadService::loadContentInfoByRemoteId()``

Since ``Content`` object lazy-loads its ``Fields``, it is no longer necessary to have
``ContentInfo`` as a light weight version of the  ``Content``. ``ContentInfo`` is still kept to
retain similarity with vanilla eZ Platform, but it's only accessible from the ``Content`` object and
all methods to load it separately are hereby removed. Upgrade by loading ``Content`` instead.

Removed methods for accessing ``Locations`` from ``ContentInfo``
----------------------------------------------------------------

* ``Netgen\IbexaSiteApi\API\Values\ContentInfo::getLocations()``
* ``Netgen\IbexaSiteApi\API\Values\ContentInfo::filterLocations()``

Since ``ContentInfo`` is now "degraded" to a simple container of properties, methods to access
``Locations`` from it are also removed. Upgrade by accessing corresponding methods from the
``Content`` object instead.

Removed ``Node`` object
-----------------------

* ``Netgen\IbexaSiteApi\API\Values\Node``
* ``Netgen\IbexaSiteApi\API\FindService::findNodes()``
* ``Netgen\IbexaSiteApi\API\LoadService::loadNode()``
* ``Netgen\IbexaSiteApi\API\LoadService::loadNodeByRemoteId()``

Since it's now possible to access ``Content`` from the ``Location`` object (lazy-loaded), it is no
longer necessary to have a separate ``Node`` object, which was just a ``Location`` with the
aggregated ``Content``. Upgrade by using ``Location`` instead.

Removed ``PagerfantaFindTrait`` and corresponding adapters
----------------------------------------------------------

* ``Netgen\IbexaSiteApi\Core\Traits\PagerfantaFindTrait``
* ``Netgen\IbexaSiteApi\Core\Site\Pagination\Pagerfanta\ContentInfoSearchAdapter``
* ``Netgen\IbexaSiteApi\Core\Site\Pagination\Pagerfanta\ContentInfoSearchHitAdapter``
* ``Netgen\IbexaSiteApi\Core\Site\Pagination\Pagerfanta\ContentSearchAdapter``
* ``Netgen\IbexaSiteApi\Core\Site\Pagination\Pagerfanta\ContentSearchFilterAdapter``
* ``Netgen\IbexaSiteApi\Core\Site\Pagination\Pagerfanta\ContentSearchHitAdapter``
* ``Netgen\IbexaSiteApi\Core\Site\Pagination\Pagerfanta\LocationSearchAdapter``
* ``Netgen\IbexaSiteApi\Core\Site\Pagination\Pagerfanta\LocationSearchFilterAdapter``
* ``Netgen\IbexaSiteApi\Core\Site\Pagination\Pagerfanta\LocationSearchHitAdapter``
* ``Netgen\IbexaSiteApi\Core\Site\Pagination\Pagerfanta\NodeSearchAdapter``
* ``Netgen\IbexaSiteApi\Core\Site\Pagination\Pagerfanta\NodeSearchHitAdapter``

Since searching for ``ContentInfo`` is removed and ``Node`` is completely removed, it's possible
to search only for ``Content`` and ``Locations``. This is now distinguished by the type of query
object. Upgrade by using new ``PagerfantaTrait``, ``FilterAdapter`` and ``FindAdapter`` instead.

Removed ``BaseAdapter``, ``Slice`` and ``SearchResultExtras``
-------------------------------------------------------------

* ``Netgen\IbexaSiteApi\Core\Site\Pagination\Pagerfanta\BaseAdapter``
* ``Netgen\IbexaSiteApi\Core\Site\Pagination\Pagerfanta\Slice``
* ``Netgen\IbexaSiteApi\Core\Site\Pagination\Pagerfanta\SearchResultExtras``

These classes and interfaces are moved to ``netgen/ezplatform-search-extra``, which is more
appropriate place as other code aside from Site API can benefit from it. Upgrade by using the same
classes and interfaces from ``netgen/ezplatform-search-extra`` repository.

Replaced Query Type condition ``publication_date`` with ``creation_date``
-------------------------------------------------------------------------

Since with Site API we are normally (aside from preview) dealing only with published ``Content``,
``creation_date`` is a more appropriate name, particularly considering we will introduce
``modification_date`` in the future. Upgrade by searching your view configuration for queries using
``publication_date`` and replace it with ``creation_date`` instead.
