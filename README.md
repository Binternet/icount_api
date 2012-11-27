#iCount.co.il API PHP Class
A Basic Class which works with iCount.co.il's API, based on the official documentation from icount.co.il

##v.0.1: November 27, 2012

__Description__
This basic version mostly eases the process of working with the API.
Instead of passing one huge array, it seperates it to couple of logical steps.

__Installation__
Just include it in your PHP project, set the credentials (company,user,pass) inside it and keep going.

__Note__
When working on a local development machine (windows/linux) - if you're missing the SSL certificates, you need
to execute:

$icount->ssl_verify_peer = FALSE;

This skip the SSL certificate validation on the cURL library.