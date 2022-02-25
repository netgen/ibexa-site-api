#!/bin/sh

if [ "$KERNEL_VERSION" != "" ] ; then
    echo "> Update ibexa/core requirement to ${KERNEL_VERSION}"
    composer require --no-update ibexa/core="${KERNEL_VERSION}"
fi
