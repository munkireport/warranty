Warranty module
==============

Provides warranty status information that is uploaded via a csv file.

The table provides the following information:

* purchase_date (string) Date in the following format: yyyy-mm-dd
* end_date (string) Date in the following format: yyyy-mm-dd
* status (string) One of the following strings: 
  * Supported
  * Expired

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

* The admin needs to update the warranty status
* Only machines that are already in the 


