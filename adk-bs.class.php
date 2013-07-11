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
            $returnVal = $this->adkBidsystemService->GetCampaign($this->ADK_CONFIG['ADVERTISER_ID'], $this->ADK_CONFIG['CAMPAIGN_ID']);
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
            $returnVal = $this->adkBidsystemService->GetCampaignList($this->ADK_CONFIG['ADVERTISER_ID']);
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
            $returnVal = $this->adkBidsystemService->GetActiveCampaignList($this->ADK_CONFIG['ADVERTISER_ID']);
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
            $returnVal = $this->adkBidsystemService->GetCampaignStats($this->ADK_CONFIG['ADVERTISER_ID'], (array) $this->ADK_CONFIG['CAMPAIGN_ID'], $this->ADK_CONFIG['START_DATE'], $this->ADK_CONFIG['END_DATE']);
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
        if (count((array) $this->ADK_CONFIG['CAMPAIGN_ID']) == 1) {
            $this->ADK_CONFIG['CAMPAIGN_ID'] = array($this->ADK_CONFIG['CAMPAIGN_ID'], $this->ADK_CONFIG['CAMPAIGN_ID']);
        }
        $error = "Error Selecting Campaigns Bid Cost List " . implode(",", (array) $this->ADK_CONFIG['CAMPAIGN_ID']);
        $node = false;
        $shiftNode = 'Campaigns';
        try {
            $returnVal = $this->adkBidsystemService->GetCampaignBidCostList($this->ADK_CONFIG['ADVERTISER_ID'], (array) $this->ADK_CONFIG['CAMPAIGN_ID'], $this->ADK_CONFIG['START_DATE'], $this->ADK_CONFIG['END_DATE']);
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
            $returnVal = $this->adkBidsystemService->SetCampaignPaused($this->ADK_CONFIG['ADVERTISER_ID'], $this->ADK_CONFIG['CAMPAIGN_ID']);
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
            $returnVal = $this->adkBidsystemService->SetCampaignActive($this->ADK_CONFIG['ADVERTISER_ID'], $this->ADK_CONFIG['CAMPAIGN_ID']);
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
        $this->_availableFunctions = array('implementedFunctions'=>array(),'unimplementedFunctions'=>array());
        foreach ($this->_availableServices as $service) {
            $this->_availableFunctions[$service] = array('status' => '', 'Functions' => array());
            $connection = $this->connect($service);
            echo"<pre>";
            print_r($connection);
            if ($connection['status'] != 'success') {
                $this->_availableFunctions[$service]['status'] = 'unavailable';
            } else {
                $this->_availableFunctions[$service]['status'] = 'available';
                $functionList = json_decode(json_encode($this->adkBidsystemService->__getFunctions()),true);

                foreach ($functionList as $fn) {
                    list($returnType, $functionCall) = explode(" ", trim($fn), 2);
                    list($functionName, $args) = explode("(", str_replace(")", "", $functionCall), 2);
                    $pseudoName = strtolower(substr($functionName,0,1)).substr($functionName,1);
                    $args = explode(",", $args);
                    $parameters=array();
                    foreach ($args as $arg) {
                       $arg=trim($arg);
                        list($argType, $arg) = explode(" ", $arg, 2);
                        $parameters[$arg] = array('parameterType' => $argType, 'parameterName' => $arg);
                    }
                    if( method_exists($this, $pseudoName)){
                       $available='Yes';
                       $this->_availableFunctions['implementedFunctions'][$pseudoName]=array(
                           'remoteName'=>$functionName
                           , 'localName'=>$pseudoName
                           ,'parameters'=>$parameters
                           ,'returnType'=>$returnType);
                    }else{
                        $available='No';
                        $this->_availableFunctions['unimplementedFunctions'][$pseudoName]=array(
                           'remoteName'=>$functionName
                           , 'localName'=>$pseudoName.' [PENDING IMPLEMENTATION]'
                           ,'parameters'=>$parameters
                           ,'returnType'=>$returnType);
                    }
                    $this->_availableFunctions[$service]['Functions'][$functionName] = array(
                        'SOAP_API' => $functionName
                        , 'adk-bs.class' => $pseudoName
                        , 'available' => $available
                        , 'returnType' => $returnType
                        , 'functionCall' =>  strtolower(substr($functionCall,0,1)).substr($functionCall,1)
                        , 'parameters' => $parameters
                    );


                }
            }

        }
        ksort($this->_availableFunctions['implementedFunctions']);
        ksort($this->_availableFunctions['unimplementedFunctions']);
        return(array('status' => 'success', 'Services' => $this->_availableFunctions));
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
            $this->adkBidsystemService = $this->_services[$service];
            return array('status' => 'success', 'type' => 'cached');
        }
        $this->push('SERVICE', $service, 'STRING');
        try {
            $this->adkBidsystemService = false;
            $this->loginObject = new Soapheader(sprintf($this->ADK_CONFIG['NAMESPACE'], $this->ADK_CONFIG['SERVICE']), 'LoginObject', new LoginObject($this->ADK_CONFIG));

            $this->_services[$service] = new SoapClient(sprintf($this->ADK_CONFIG['WSDL'], $this->ADK_CONFIG['SERVICE']));
            $this->_services[$service]->__setSoapHeaders(array($this->loginObject));

            $this->adkBidsystemService = $this->_services[$service];
            return array('status' => 'success', 'type' => 'new');
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
