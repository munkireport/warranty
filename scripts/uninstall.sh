#!/bin/bash

# Remove warranty script
rm -f "${MUNKIPATH}preflight.d/warranty"

# Remove warranty.plist file
rm -f "${CACHEPATH}warranty.plist"

# Remove old warranty.txt file
rm -f "${CACHEPATH}warranty.txt"