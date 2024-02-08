Upgrading from 5.4.0 to 6.0.0
=============================

Version ``6.0.0`` is a major release that introduces a backward compatibility breaking change, where ``Location::$path``
property has been changed to aggregate an instance of a new ``Path`` object, instead of an array of ancestor Location
IDs. The array of Location IDs has been moved to a new property ``Location::$pathArray``.

To upgrade, simply update your code using ``Location::$path`` property to use new ``Location::$pathArray`` property.
