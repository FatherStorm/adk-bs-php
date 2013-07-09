<?php

/*
 *  LIBRARY CODE -- CHANGE AT YOUR OWN RISK
 */

class LoginObject
{

    public $API_KEY = '';   //Optional usually filled at runtime from the passed array
    public $PASSWORD = '';  //Optional usually filled at runtime from the passed array

    public function __construct($params)
    {
        $this->API_KEY = isset($params['API_KEY']) && trim($params['API_KEY']) ? $params['API_KEY'] : $this->API_KEY;
        $this->PASSWORD = isset($params['PASSWORD']) && trim($params['PASSWORD']) ? $params['PASSWORD'] : $this->PASSWORD;
    }

}

class ADK_SOAP_API
{

    public function __construct($ADK_CONFIG)
    {
        $this->ADK_CONFIG = $ADK_CONFIG;
        $this->ADK_CONFIG['NAMESPACE'] = isset($this->ADK_CONFIG['NAMESPACE']) && trim($this->ADK_CONFIG['NAMESPACE']) ?
                $this->ADK_CONFIG['NAMESPACE'] :
                'https://api.bidsystem.com/2011-10-01/Campaign.svc';

        $this->ADK_CONFIG['WSDL'] = isset($this->ADK_CONFIG['WSDL']) && trim($this->ADK_CONFIG['WSDL']) ?
                $this->ADK_CONFIG['WSDL'] :
                'https://api.bidsystem.com/2011-10-01/Campaign.wsdl';
    }

    public function getCampaign($campaign_id = false)
    {
        $this->ADK_CONFIG['CAMPAIGN_ID'] = $campaign_id ? $campaign_id : $this->ADK_CONFIG['CAMPAIGN_ID'];
        if (!$this->ADK_CONFIG['CAMPAIGN_ID']) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => 'No Campaign ID specified');
        }
        $connection = $this->connect();
        if ($connection['status'] != 'success') {
            $return(connection);
        }
        try {
            $campaigns = $this->campaignService->GetCampaign($this->ADK_CONFIG['ADVERTISER_ID'], $this->ADK_CONFIG['CAMPAIGN_ID']);
            if ($campaigns) {
                return array('status' => 'success', 'Campaigns' => $campaigns);
            } else {
                return array('status' => 'error', 'line' => __LINE__, 'message' => "Error activating Campaign { $this->ADK_CONFIG['CAMPAIGN_ID']} ");
            }
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    public function getCampaignDayParting($campaign_id = false)
    {
        $this->ADK_CONFIG['CAMPAIGN_ID'] = $campaign_id ? $campaign_id : $this->ADK_CONFIG['CAMPAIGN_ID'];
        if (!$this->ADK_CONFIG['CAMPAIGN_ID']) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => 'No Campaign ID specified');
        }
        $connection = $this->connect();
        if ($connection['status'] != 'success') {
            $return(connection);
        }
        try {
            $day_parting = $this->campaignService->GetCampaignDayParting($this->ADK_CONFIG['ADVERTISER_ID'], $this->ADK_CONFIG['CAMPAIGN_ID']);
            if ($day_parting) {

                $day_parting = json_decode(json_encode($day_parting), true);
                $day_parting['status'] = 'success';
                return $day_parting;
            } else {
                return array('status' => 'error', 'line' => __LINE__, 'message' => "Error getting Campaign Date Parting {$this->ADK_CONFIG['CAMPAIGN_ID']} ");
            }
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage(), 'params' => $this->ADK_CONFIG);
        }
    }

    public function setCampaignDayParting($advertiser_id, $day_parting = array())
    {
        unset($day_parting['status']);
        $this->ADK_CONFIG['ADVERTISER_ID'] = $advertiser_id ? $advertiser_id : $this->ADK_CONFIG['ADVERTISER_ID'];
        if (!$this->ADK_CONFIG['ADVERTISER_ID']) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => 'No Campaign ID specified');
        }
        $connection = $this->connect();
        if ($connection['status'] != 'success') {
            $return(connection);
        }
        try {
            $campaigns = $this->campaignService->SetCampaignDayParting($this->ADK_CONFIG['ADVERTISER_ID'], $day_parting);
            if ($campaigns) {
                return array('status' => 'success', 'status' => $campaigns);
            } else {
                return array('status' => 'error', 'line' => __LINE__, 'message' => "Error adding parting to campaign {$this->ADK_CONFIG['CAMPAIGN_ID']} ");
            }
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    public function enableCampaignDayParting($campaign_id = false)
    {
        $this->ADK_CONFIG['CAMPAIGN_ID'] = $campaign_id ? $campaign_id : $this->ADK_CONFIG['CAMPAIGN_ID'];
        if (!$this->ADK_CONFIG['CAMPAIGN_ID']) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => 'No Campaign ID specified');
        }
        $connection = $this->connect();
        if ($connection['status'] != 'success') {
            $return(connection);
        }
        try {
            $campaigns = $this->campaignService->EnableCampaignDayParting($this->ADK_CONFIG['ADVERTISER_ID'], $this->ADK_CONFIG['CAMPAIGN_ID']);
            if ($campaigns) {
                return array('status' => 'success', 'Dayparting Changed to Enabled for '.$this->ADK_CONFIG['CAMPAIGN_ID'] => $campaigns);
            } else {
                return array('status' => 'error', 'line' => __LINE__, 'message' => "Error activating Campaign { $this->ADK_CONFIG['CAMPAIGN_ID']} ");
            }
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    public function disableCampaignDayParting($campaign_id = false)
    {
        $this->ADK_CONFIG['CAMPAIGN_ID'] = $campaign_id ? $campaign_id : $this->ADK_CONFIG['CAMPAIGN_ID'];
        if (!$this->ADK_CONFIG['CAMPAIGN_ID']) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => 'No Campaign ID specified');
        }
        $connection = $this->connect();
        if ($connection['status'] != 'success') {
            $return(connection);
        }
        try {
            $campaigns = $this->campaignService->DisableCampaignDayParting($this->ADK_CONFIG['ADVERTISER_ID'], $this->ADK_CONFIG['CAMPAIGN_ID']);
            if ($campaigns) {
                return array('status' => 'success', 'Dayparting Changed to Disabled for '.$this->ADK_CONFIG['CAMPAIGN_ID'] => $campaigns);
            } else {
                return array('status' => 'error', 'line' => __LINE__, 'message' => "Error activating Campaign { $this->ADK_CONFIG['CAMPAIGN_ID']} ");
            }
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    public function getCampaignGeo($campaign_id = false)
    {
        $this->ADK_CONFIG['CAMPAIGN_ID'] = $campaign_id ? $campaign_id : $this->ADK_CONFIG['CAMPAIGN_ID'];
        if (!$this->ADK_CONFIG['CAMPAIGN_ID']) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => 'No Campaign ID specified');
        }
        $connection = $this->connect();
        if ($connection['status'] != 'success') {
            $return(connection);
        }
        try {
            $geo = $this->campaignService->GetCampaignGeo($this->ADK_CONFIG['ADVERTISER_ID'], $this->ADK_CONFIG['CAMPAIGN_ID']);
            if ($geo) {
                return array('status' => 'success', 'Geo' => $geo);
            } else {
                return array('status' => 'error', 'line' => __LINE__, 'message' => "Error activating Campaign { $this->ADK_CONFIG['CAMPAIGN_ID']} ");
            }
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    public function getGeoCountries()
    {

        $connection = $this->connect();
        if ($connection['status'] != 'success') {
            $return(connection);
        }
        try {
            $countries = $this->campaignService->GetGeoCountries();
            $countries = json_decode(json_encode($countries), true);
            if ($countries) {
                return array('status' => 'success', 'Countries' => $countries['GeoCountry']);
            } else {
                return array('status' => 'error', 'line' => __LINE__, 'message' => "Error retrieving countries");
            }
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    public function getGeoStates($country = false)
    {
        if ($country) {
            $this->ADK_CONFIG['GEO_COUNTRY'] = $country;
        }
        if (strlen($this->ADK_CONFIG['GEO_COUNTRY']) != 2) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => "Country codes must be 2 characters in length {$this->ADK_CONFIG['GEO_COUNTRY']}");
        }
        $connection = $this->connect();
        if ($connection['status'] != 'success') {
            $return(connection);
        }
        try {
            $states = $this->campaignService->GetGeoStates(strtoupper($this->ADK_CONFIG['GEO_COUNTRY']));
            if (count($states)) {
                return array('status' => 'success', 'States' => $states, 'Country' => $this->ADK_CONFIG['GEO_COUNTRY']);
            } else {
                return array('status' => 'error', 'line' => __LINE__, 'message' => "Error retrieving States for {$this->ADK_CONFIG['GEO_COUNTRY']}");
            }
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    public function getCampaignStats($campaign_id = false, $start_date = false, $end_date = false)
    {
        if (!$campaign_id) {
            $campaign_id = $this->ADK_CONFIG['CAMPAIGN_ID'];
        }
        if (!is_array($campaign_id)) {
            $campaign_id = array($campaign_id);
        }

        if (!count($campaign_id)) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => 'No Campaign ID(s) specified');
        }
        if (!$start_date) {
            $start_date = date("Y-m-d h:i:s", strtotime('-1 month'));
        } else {
            $start_date = date("Y-m-d h:i:s", strtotime($start_date));
        }
        if (!$end_date) {
            $end_date = date("Y-m-d h:i:s", strtotime('tomorrow'));
        } else {
            $end_date = date("Y-m-d h:i:s", strtotime($end_date));
        }
        $connection = $this->connect();
        if ($connection['status'] != 'success') {
            $return(connection);
        }
        try {
            $stats = $this->campaignService->GetCampaignStats($this->ADK_CONFIG['ADVERTISER_ID'], $campaign_id, $start_date, $end_date);
            if ($stats) {
                return array('status' => 'success', 'Stats' => $stats);
            } else {
                return array('status' => 'error', 'line' => __LINE__, 'message' => "Error Accessing stats for Campaign(s)  " . var_export($campaign_id));
            }
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    public function getCampaignBidCostList($campaign_id = false, $start_date = false, $end_date = false)
    {
        if (!$campaign_id) {
            $campaign_id = $this->ADK_CONFIG['CAMPAIGN_ID'];
        }
        $campaign_id=(array)$campaign_id;
         if (!count($campaign_id)) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => 'No Campaign ID(s) specified');
        }

        /*
         * There is a undocumented "feature" in the SOAP layer that expects this call to be made ONLY on multiple campaign ID's.
         * it WILL however de-dupe them, so if we only need results for one, we dup it on the call stack.
         */
        if(count($campaign_id)==1){
            $campaign_id[1]=$campaign_id[0];
        }


        if (!$start_date) {
            $start_date = date("Y-m-d", strtotime('-1 month'));
        } else {
            $start_date = date("Y-m-d", strtotime($start_date));
        }
        if (!$end_date) {
            $end_date = date("Y-m-d", strtotime('tomorrow'));
        } else {
            $end_date = date("Y-m-d", strtotime($end_date));
        }
        $connection = $this->connect();
        if ($connection['status'] != 'success') {
            $return(connection);
        }
        try {
            $costList = $this->campaignService->GetCampaignBidCostList($this->ADK_CONFIG['ADVERTISER_ID'], (array)$campaign_id, $start_date, $end_date);
            if ($costList) {
                return array('status' => 'success', 'BidCostList' => $costList);
            } else {
                return array('status' => 'error', 'line' => __LINE__, 'message' => "Error Accessing stats for Campaign(s)  " . var_export($campaign_id));
            }
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage(), 'params' => array('campaign_id'=>$campaign_id, 'start_date' => $start_date, 'end_date' => $end_date));
        }
    }

    public function getCampaignList($advertiser_id)
    {
        $this->ADK_CONFIG['ADVERTISER_ID'] = $advertiser_id ? $advertiser_id : $this->ADK_CONFIG['ADVERTISER_ID'];
        if (!$this->ADK_CONFIG['CAMPAIGN_ID']) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => 'No Campaign ID specified');
        }
        $connection = $this->connect();
        if ($connection['status'] != 'success') {
            $return(connection);
        }
        try {
            $campaigns = $this->campaignService->GetCampaignList($this->ADK_CONFIG['ADVERTISER_ID']);
            if ($campaigns) {
                return array('status' => 'success', 'Campaign' => $campaigns);
            } else {
                return array('status' => 'error', 'line' => __LINE__, 'message' => "Error activating Campaign { $this->ADK_CONFIG['CAMPAIGN_ID']} ");
            }
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    public function getActiveCampaignList($advertiser_id)
    {
        $this->ADK_CONFIG['ADVERTISER_ID'] = $advertiser_id ? $advertiser_id : $this->ADK_CONFIG['ADVERTISER_ID'];
        if (!$this->ADK_CONFIG['CAMPAIGN_ID']) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => 'No Campaign ID specified');
        }
        $connection = $this->connect();
        if ($connection['status'] != 'success') {
            $return(connection);
        }
        try {
            $campaigns = $this->campaignService->GetActiveCampaignList($this->ADK_CONFIG['ADVERTISER_ID']);
            if ($campaigns) {
                return array('status' => 'success', 'Campaign' => $campaigns);
            } else {
                return array('status' => 'error', 'line' => __LINE__, 'message' => "Error activating Campaign { $this->ADK_CONFIG['CAMPAIGN_ID']} ");
            }
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    public function setCampaignActive($campaign_id = false)
    {
        $this->ADK_CONFIG['CAMPAIGN_ID'] = $campaign_id ? $campaign_id : $this->ADK_CONFIG['CAMPAIGN_ID'];
        if (!$this->ADK_CONFIG['CAMPAIGN_ID']) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => 'No Campaign ID specified');
        }
        $connection = $this->connect();
        if ($connection['status'] != 'success') {
            $return(connection);
        }
        try {
            $campaigns = $this->campaignService->SetCampaignActive($this->ADK_CONFIG['ADVERTISER_ID'], $this->ADK_CONFIG['CAMPAIGN_ID']);
            if ($campaigns == 1) {
                return array('status' => 'success', 'message' => "Campaign {$this->ADK_CONFIG['CAMPAIGN_ID']}  successfully activated");
            } else {
                return array('status' => 'error', 'line' => __LINE__, 'message' => "Error activating Campaign { $this->ADK_CONFIG['CAMPAIGN_ID']} ");
            }
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    public function setCampaignInactive($campaign_id = false)
    {
        $this->ADK_CONFIG['CAMPAIGN_ID'] = $campaign_id ? $campaign_id : $this->ADK_CONFIG['CAMPAIGN_ID'];
        if (!$this->ADK_CONFIG['CAMPAIGN_ID']) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => 'No Campaign ID specified');
        }
        $connection = $this->connect();
        if ($connection['status'] != 'success') {
            $return(connection);
        }
        try {
            $campaigns = $this->campaignService->SetCampaignInactive($this->ADK_CONFIG['ADVERTISER_ID'], $this->ADK_CONFIG['CAMPAIGN_ID']);
            if ($campaigns == 1) {
                return array('status' => 'success', 'message' => "Campaign {$this->ADK_CONFIG['CAMPAIGN_ID']}  successfully deactivated");
            } else {
                return array('status' => 'error', 'line' => __LINE__, 'message' => "Error activating Campaign { $this->ADK_CONFIG['CAMPAIGN_ID']} ");
            }
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    public function setCampaignPaused($campaign_id=false)
    {
        $this->ADK_CONFIG['CAMPAIGN_ID'] = $campaign_id ? $campaign_id : $this->ADK_CONFIG['CAMPAIGN_ID'];
        if (!$this->ADK_CONFIG['CAMPAIGN_ID']) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => 'No Campaign ID specified');
        }
        $connection = $this->connect();
        if ($connection['status'] != 'success') {
            $return(connection);
        }
        try {
            $campaigns = $this->campaignService->SetCampaignPaused($this->ADK_CONFIG['ADVERTISER_ID'], $this->ADK_CONFIG['CAMPAIGN_ID']);
            if ($campaigns == 1) {
                return array('status' => 'success', 'message' => "Campaign {$this->ADK_CONFIG['CAMPAIGN_ID']}  successfully paused");
            } else {
                return array('status' => 'error', 'line' => __LINE__, 'message' => "Error pausing Campaign { $this->ADK_CONFIG['CAMPAIGN_ID']} ");
            }
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    public function getFunctions()
    {
        $connection = $this->connect();
        if ($connection['status'] != 'success') {
            $return(connection);
        }
        return(array('status' => 'success', 'functions' => var_export($this->campaignService->__getFunctions())));
    }

    public function connect()
    {
        try {
            $this->loginObject = new Soapheader($this->ADK_CONFIG['NAMESPACE'], 'LoginObject', new LoginObject($this->ADK_CONFIG));

            $this->campaignService = new SoapClient($this->ADK_CONFIG['WSDL']);
            $this->campaignService->__setSoapHeaders(array($this->loginObject));
            return(array('status' => 'success'));
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    public function setAPIKey($api_key)
    {
        $this->ADK_CONFIG['API_KEY'] = $api_key;
    }

    public function setPassword($password)
    {
        $this->ADK_CONFIG['PASSWORD'] = $password;
    }

    public function setAdvertiserID($advertiser_id)
    {
        $this->ADK_CONFIG['ADVERTISER_ID'] = $advertiser_id;
    }

    public function setCampaignID($campaign_id)
    {
        $this->ADK_CONFIG['CAMPAIGN_ID'] = $campaign_id;
    }

    public function setGeoCountry($country)
    {
        $this->ADK_CONFIG['GEO_COUNTRY'] = $country;
    }

}
