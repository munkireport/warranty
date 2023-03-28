Warranty module
==============

Provides warranty status information that is uploaded via a csv file through the admin page.

Starting with Apple's new random serial numbers introduced with the 14"/16" 2021 Macbook Pro, it is not possible for MunkiReport to estimate when a Mac was manufactured.

This module can also get the machine's warranty using the on device warranty information that is present on macOS Big Sur or higher. This requires at least one user on the Mac to be actively signed into iCloud. If your org blocks iCloud sign in, this module will not automatically update warranty information because of Apple's requirement of having an active iCloud account.

csv file format
---

```
"serial_number","purchase_date","end_date"
"3X6RHPJ3P7QM","2016-06-09","2020-06-09"
"CLJW1VCQMD6N","2020-04-14","2024-04-14"
"8WSF8O4BHDNK","2019-10-18","2023-10-18"
```

Remarks
---

* The admin needs to update the warranty status via the admin page
* Only machines that are already in MunkiReport can be uploaded 

Table Schema
---
* purchase_date (string) Date in the following format: yyyy-mm-dd
* end_date (string) Date in the following format: yyyy-mm-dd
* status (string) One of the following strings: 
  * Supported
  * Expired
  * Limited Warranty
  * Unknown
  * AppleCare
* est_mfg_date (string) Date in the following format: yyyy-mm-dd
* icloud_logged_in (boolean) If the Mac has a user that is signed into iCloud, required for automated warranty lookups new in Big Sur

