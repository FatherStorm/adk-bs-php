adk-bs-php
==========

Adknowledge Bidsystem SOAP API PHP wrapper
------------------------------------------

####Note:
 This class was written as a side-project. This project is not affiliated with Adknowledge and makes no warranties or guarantees as to performance, correctness or completeness.

####Info:
 I wrote this class because I needed a way to easily interface with the official [Adknowledge SOAP API](http://api.bidsystem.com/ "Adknowledge SOAP API")  from inside any extensible php framework. At the moment, actual coded functionality is limited, BUT that doesn't mean it can't do just about everything that the SOAP API allows.  To achieve that, I'm using the magic __call() function in php to dynamically build any missing functions at request-time based on the information available about them using the SOAP API's getFunctions call. SO. That means that even though I've commented out the perfectly fine working function "getCampaignList", calling it with '$adk_soap_api->getCampaignList();' will still work. As I have time, I will add more actual functions and do whatever one-off and validation and so-on but until then, this class still can fullfill just about every need..

####Contact:
 You are welcome to contact me with any questions, but I do not guarantee that I will be able to respond in any meaningful amount of time. Again, this is a personal project and NOT in any way officially sanctioned or supported by Adknowledge or Bidsystem.
