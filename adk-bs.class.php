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

    public $_availableServices = array(
        'AdGroup'
        , 'Campaign'
        , 'Transaction'
        , 'AdGroupCategory'
        , 'Category'
        , 'Advertiser'
        , 'Listing'
        , 'BidIndex'
        , 'Network'
    );
    private $_services = array();

    public function __construct($ADK_CONFIG)
    {
        $this->ADK_CONFIG = $ADK_CONFIG;
        $this->ADK_CONFIG['NAMESPACE'] = isset($this->ADK_CONFIG['NAMESPACE']) && trim($this->ADK_CONFIG['NAMESPACE']) ?
                $this->ADK_CONFIG['NAMESPACE'] :
                'https://api.bidsystem.com/2011-10-01/%s.svc';

        $this->ADK_CONFIG['WSDL'] = isset($this->ADK_CONFIG['WSDL']) && trim($this->ADK_CONFIG['WSDL']) ?
                $this->ADK_CONFIG['WSDL'] :
                'https://api.bidsystem.com/2011-10-01/%s.wsdl';
    }

    //Getters
    public function getCampaign($advertiser_id = false, $campaign_id = false)
    {
        $this->push('ADVERTISER_ID', $advertiser_id, 'INT');
        $this->push('CAMPAIGN_ID', $campaign_id, 'INT');
        $connection = $this->connect('Campaign');
        $error = "Error Selecting Campaign {$this->ADK_CONFIG['CAMPAIGN_ID']} ";
        $node = 'Campaign';
        try {
            $returnVal = $this->campaignService->GetCampaign($this->ADK_CONFIG['ADVERTISER_ID'], $this->ADK_CONFIG['CAMPAIGN_ID']);
            return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    public function getCampaignList($advertiser_id = false)
    {
        $this->push('ADVERTISER_ID', $advertiser_id, 'INT');
        $connection = $this->connect('Campaign');
        $error = "Error Selecting Advertiser Campaigns {$this->ADK_CONFIG['CAMPAIGN_ID']} ";
        $node = false;
        $shiftNode = 'Campaigns';
        try {
            $returnVal = $this->campaignService->GetCampaignList($this->ADK_CONFIG['ADVERTISER_ID']);
            return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    public function getActiveCampaignList($advertiser_id = false)
    {
        $this->push('ADVERTISER_ID', $advertiser_id, 'INT');
        $connection = $this->connect('Campaign');
        $error = "Error Selecting Active Advertiser Campaigns {$this->ADK_CONFIG['CAMPAIGN_ID']} ";
        $node = false;
        $shiftNode = 'Campaigns';
        try {
            $returnVal = $this->campaignService->GetActiveCampaignList($this->ADK_CONFIG['ADVERTISER_ID']);
            return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    public function getCampaignStats($advertiser_id = false, $campaign_id = false, $start_date = false, $end_date = false)
    {
        $this->push('ADVERTISER_ID', $advertiser_id, 'INT');
        $this->push('CAMPAIGN_ID', $campaign_id, 'ARRAY');


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
        $this->push('START_DATE', $start_date, 'STR');
        $this->push('END_DATE', $end_date, 'STR');
        $connection = $this->connect('Campaign');

        $error = "Error Selecting  Campaigns Stats " . implode(",", (array) $this->ADK_CONFIG['CAMPAIGN_ID']);
        $node = false;
        $shiftNode = 'Campaigns';
        try {
            $returnVal = $this->campaignService->GetCampaignStats($this->ADK_CONFIG['ADVERTISER_ID'], (array) $this->ADK_CONFIG['CAMPAIGN_ID'], $this->ADK_CONFIG['START_DATE'], $this->ADK_CONFIG['END_DATE']);
            return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    public function getCampaignBidCostList($advertiser_id = false, $campaign_id = false, $start_date = false, $end_date = false)
    {
        $this->push('ADVERTISER_ID', $advertiser_id, 'INT');
        $this->push('CAMPAIGN_ID', $campaign_id, 'ARRAY');


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
        $this->push('START_DATE', $start_date, 'STR');
        $this->push('END_DATE', $end_date, 'STR');
        $connection = $this->connect('Campaign');
        #######################################################################################################################
        #TODO: NOTE:
        # There is currently a bug in the SOAP layer that expects that this will ALWAYS be an array of more than one campaigns.
        # but the service DOES de-dup the campaigns sent in so we will just dup the campaign_id if there is just one
        ########################################################################################################################
        if(count((array)$this->ADK_CONFIG['CAMPAIGN_ID'])==1){
            $this->ADK_CONFIG['CAMPAIGN_ID']=array($this->ADK_CONFIG['CAMPAIGN_ID'],$this->ADK_CONFIG['CAMPAIGN_ID']);
        }
        $error = "Error Selecting Campaigns Bid Cost List " . implode(",", (array) $this->ADK_CONFIG['CAMPAIGN_ID']);
        $node = false;
        $shiftNode = 'Campaigns';
        try {
            $returnVal = $this->campaignService->GetCampaignBidCostList($this->ADK_CONFIG['ADVERTISER_ID'], (array) $this->ADK_CONFIG['CAMPAIGN_ID'], $this->ADK_CONFIG['START_DATE'], $this->ADK_CONFIG['END_DATE']);
            return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    //Setters
    public function setCampaignPaused($advertiser_id = false, $campaign_id = false)
    {
        $this->push('ADVERTISER_ID', $advertiser_id, 'INT');
        $this->push('CAMPAIGN_ID', $campaign_id, 'INT');
        $connection = $this->connect('Campaign');
        $error = "Error Pausing Campaign {$this->ADK_CONFIG['CAMPAIGN_ID']} ";
        $node = false;
        $shiftNode = 'return';
        try {
            $returnVal = $this->campaignService->SetCampaignPaused($this->ADK_CONFIG['ADVERTISER_ID'], $this->ADK_CONFIG['CAMPAIGN_ID']);
            return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    public function setCampaignActive($advertiser_id = false, $campaign_id = false)
    {
        $this->push('ADVERTISER_ID', $advertiser_id, 'INT');
        $this->push('CAMPAIGN_ID', $campaign_id, 'INT');
        $connection = $this->connect('Campaign');
        $error = "Error Activating Campaign {$this->ADK_CONFIG['CAMPAIGN_ID']} ";
        $node = false;
        $shiftNode = 'return';
        try {
            $returnVal = $this->campaignService->SetCampaignActive($this->ADK_CONFIG['ADVERTISER_ID'], $this->ADK_CONFIG['CAMPAIGN_ID']);
            return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    private function processReturn($returnVal, $error = false, $function = false, $node = false, $shiftNode = false)
    {

        if ($returnVal) {
            if ($node) {
                return array('status' => 'success', 'call' => $function, $node => $returnVal);
            }
            if ($shiftNode) {
                $returnVal = (array) $returnVal;
                $returnVal = array_shift($returnVal);
                return array('status' => 'success', 'call' => $function, $shiftNode => $returnVal);
            }
            return array('status' => 'success', 'call' => $function, $returnVal);
        } else {
            if (!$error)
                $error = var_export(debug_backtrace());
            return array('status' => 'error', 'call' => $function, 'message' => $error);
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

    public function __destruct()
    {
        if (isset($this->status) && $this->status['status'] == 'error') {
            echo json_encode($this->status);
        }
    }

    private function push($fld = 'CAMPAIGN_ID', $val = false, $type = 'INT')
    {
        if ($val == false) {
            return;
        }
        switch (strtoupper(substr($type, 0, 3))) {
            case 'ARR':
                if (!count($val)) {
                    return;
                }
                $this->ADK_CONFIG[$fld] = (array) $val;
                break;
            case 'INT':
                $this->ADK_CONFIG[$fld] = (int) $val;
                break;
            case 'STR':
                $this->ADK_CONFIG[$fld] = (string) $val;
                break;
            default:
                $this->ADK_CONFIG[$fld] = $val;
        }
        return;
    }

    private function connect($service = 'Campaign')
    {
        if (isset($this->_services[$service])) {
            $this->campaignService = $this->_services[$service];
            return;
        }
        $this->push('SERVICE', $service, 'STRING');
        try {
            $this->campaignService = false;
            $this->loginObject = new Soapheader(sprintf($this->ADK_CONFIG['NAMESPACE'], $this->ADK_CONFIG['SERVICE']), 'LoginObject', new LoginObject($this->ADK_CONFIG));

            $this->_services[$service] = new SoapClient(sprintf($this->ADK_CONFIG['WSDL'], $this->ADK_CONFIG['SERVICE']));
            $this->_services[$service]->__setSoapHeaders(array($this->loginObject));

            $this->campaignService = $this->_services[$service];
        } catch (Exception $e) {
            $this->status = array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
            exit;
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
