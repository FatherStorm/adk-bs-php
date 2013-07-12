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

####Personal Disclaimer:
 This code seems to work well enough so far in my testing. That does not mean that it won't put salt in your kool-aid while you're sleeping, and I make no warranties as to it's quality and that it won't end up on [theDailyWTF](http://www.thedailtwtf.com) tomorrow.
 

###License/Disclaimer:
<pre>
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
</pre>
