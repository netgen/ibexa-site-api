Upgrading from 1.0.0 to 2.0.0
=============================

eZ Platform Site API introduces a slight breaking change to ``ContentView`` value object, hence the
bump to version 2.0.

- Site API ``ContentView`` view object does not extend from eZ Platform ``ContentView`` value object
  any more to allow implementation of custom view providers. Class signature did not change,
  however, since all required interfaces are now implemented directly on Site API ``ContentView``
  value object.

- Also, ``Netgen\Bundle\IbexaSiteApiBundle\View\ContentValueView`` interface does not contain
  ``getSiteLocation`` method any more. It is moved to a new interface, ``LocationValueView``, in the
  same namespace. If you used this method in your code, make sure to check for this new interface.
  This was done to keep in line on how eZ kernel uses its ``ContentView`` value object and its
  interfaces.
