<?php 
require ('adk-bs.class.php');


/*
 * ADK Example class and code for using SOAP interface from PHP code
 * 2013-07-09
 * v 1.0.3
 * mfrancois
 */



$ADK_CONFIG = array(
    'NAMESPACE' => 'https://api.bidsystem.com/2011-10-01/Campaign.svc'    //This Parameter may change and is not required and defaults as shown. It cannot be changed after the class is initialized
    , 'WSDL' => 'https://api.bidsystem.com/2011-10-01/Campaign.wsdl'        //This Parameter may change and is not required and defaults as shown. It cannot be changed after the class is initialized
    , 'API_KEY' => ''                                                       //This parameter is required but can be set using setAPIKey()
    , 'PASSWORD' => ''                                                      //This parameter is required but can be set using setPassword();
    , 'ADVERTISER_ID' => ''                                                 //This parameter is required but can be set after the class is initiated using setAdvertiserID();
    , 'CAMPAIGN_ID' => ''                                                   //This parameter is required can be passed at call time or initiated using setCampaignID();
);


/*
 *
 */
$adk_soap_api = new ADK_SOAP_API($ADK_CONFIG);

/*
 *  NOTE! If a API KEY and Password were NOT specified in the initial $ADK_CONFIG array object, they will need to be expilicitely set before any actions are taken
 */
$adk_soap_api->setAPIKey('ADKNOWLEDGE');
$adk_soap_api->setPassword('08BC5F9AA19371862B489840B39EB2D1');

//Set a Advertiser ID and Campaign ID at runtime and turn a campaign On
$adk_soap_api->setAdvertiserID('378707');
$adk_soap_api->setCampaignID('888994');
echo"<pre>";

/*
 * Dump available functions for this namespace
 */
$status = $adk_soap_api->getFunctions();
echo"<br/>";
echo json_encode($status);

/*
 * Set a campaign active based on ADK_CONFIG variable for campaign_id
 */
$status = $adk_soap_api->setCampaignActive();
echo"<br/>";
echo json_encode($status);

/*
 * set a campaign paused based on campaign_id in ADK_CONFIG
 */
$status = $adk_soap_api->setCampaignPaused();
echo"<br/>";
echo json_encode($status);
/*
 * Set a campaign active based on passed campaign_id
 */
$status = $adk_soap_api->setCampaignActive(888994);
echo"<br/>";
echo json_encode($status);

/*
 * set a campaign paused based on passed campaign_id
 */
$status = $adk_soap_api->setCampaignPaused(888994);
echo"<br/>";
echo json_encode($status);

/*
 * Example get a campaign (default to config
 */
$status = $adk_soap_api->getCampaign();
echo"<br/>";
echo json_encode($status);


/*
 * example get a campaign by ID
 */
$status = $adk_soap_api->getCampaign(888994);
echo"<br/>";
echo json_encode($status);


/*
 * Get stats for the config'd campaign
 */
$status = $adk_soap_api->getCampaignStats();
echo"<br/>";
echo json_encode($status);

/*
 * get Stats by an array of campaigns from yesterday forward
 */
$status = $adk_soap_api->getCampaignStats(array(888994, 884714, 888274), 'yesterday');
echo"<br/>";
echo json_encode($status);

/*
 * get Stats by an array of campaigns for prev 7 day window
 */
$status = $adk_soap_api->getCampaignStats(array(888994, 884714, 888274), '-14 days', '-7 days');
echo"<br/>";
echo json_encode($status);

/*
 * Get Bid cost for a campaign from config file
 */
$status = $adk_soap_api->getCampaignBidCostList(array(888994), 'yesterday');
echo"<br/>";
echo json_encode($status);

/*
 * Get Bid Cost for array of campaigns
 */
$status = $adk_soap_api->getCampaignBidCostList(array(888994, 884714, 888274), 'yesterday');
echo"<br/>";
echo json_encode($status);

/*
 * Get available countries
 */
$status = $adk_soap_api->getGeoCountries();
echo"<br/>";
echo json_encode($status);

/*
 * Get states for a country where available [US]
 */
$adk_soap_api->setGeoCountry('US');
$status = $adk_soap_api->getGeoStates();
echo"<br/>";
echo json_encode($status);

/*
 * get GEO for a campaign
 */

$status = $adk_soap_api->getCampaignGeo();
echo"<br/>";
echo json_encode($status);



/*
 * Enable day parting for a campaign
 */
$status = $adk_soap_api->enableCampaignDayParting();
echo"<br/>";
echo json_encode($status);



/*
 * Get Day Parting for a campaign
 */
$status = $adk_soap_api->getCampaignDayParting();
echo"<br/>";
echo json_encode($status);

/*
 * Set day parting for a campaign
 */

$status['day_parts']['DayPart'][1]['status'] = false;
$status = $adk_soap_api->setCampaignDayParting(0, $status);

/*
 * disable day parting for a campaign
 */
$status = $adk_soap_api->disableCampaignDayParting();
echo"<br/>";
echo json_encode($status);

