<?php
/*
 *  LIBRARY CODE -- CHANGE AT YOUR OWN RISK
 */
class LoginObject
{

    public $api_key = '';   //Optional usually filled at runtime from the passed array
    public $password = '';  //Optional usually filled at runtime from the passed array

    public function __construct($params)
    {
        /*
         * NOTE: These two items are capitalized on purpose due to the SOAP connection layer requirements.
         */
        $this->API_KEY = isset($params['api_key']) && trim($params['api_key']) ? $params['api_key'] : $this->api_key;
        $this->PASSWORD = isset($params['password']) && trim($params['password']) ? $params['password'] : $this->password;
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
        /*
         * Build up a flexObject that contains everything we need and do. we can reuse parts of this object at will, shove it around by reference, do whatever.
         * This is some seriously bad frankencode but makes for fast development...
         * Rewrite may come at some point.
         */
        $this->flexOBJECT = $ADK_CONFIG;
        $this->flexOBJECT['namespace'] = isset($this->flexOBJECT['namespace']) && trim($this->flexOBJECT['namespace']) ?
                $this->flexOBJECT['namespace'] :
                'https://api.bidsystem.com/2011-10-01/%s.svc';

        $this->flexOBJECT['wsdl'] = isset($this->flexOBJECT['wsdl']) && trim($this->flexOBJECT['wsdl']) ?
                $this->flexOBJECT['wsdl'] :
                'https://api.bidsystem.com/2011-10-01/%s.wsdl';
    }

    public function __call($func, $params)
    {
        if(!isset($this->soap)){
            $this->soap = $this->getFunctions();
        }
        if (array_key_exists($func, $this->soap['Services']['unimplementedFunctions'])) {
            $magicFn = $this->soap['Services']['unimplementedFunctions'][$func];
            $magicUserFuncParams=array();
           foreach($magicFn['parameters'] as $offset=>$param){
              $this->push(str_replace("$","",$param['parameterName']),$params[$offset],$param['parameterType']);
              $magicUserFuncParams= $this->flexOBJECT[$param['parameterName']];
           }
            $connection = $this->connect($magicFn['service']);
            $error="There was an error attempting to return data for magicClass $func()";
            $node=$magicFn['returnType'];
            $magicUserFunc= "{$magicFn['remoteName']}";
            try {
                $returnVal=  call_user_func(array($this->adkBidsystemService,$magicUserFunc), $magicUserFuncParams);
                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
            } catch (Exception $e) {
                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
            }


        }
    }

    //Getters
    public function getAdGroup($advertiser_id = false, $ad_group_id = false)
    {
        $this->push('advertiser_id', $advertiser_id, 'INT');
        $this->push('ad_group_id', $ad_group_id, 'INT');
        $connection = $this->connect('AdGroup');
        $error = "Error Selecting Campaign {$this->flexOBJECT['campaign_id']} ";
        $node = 'AdGroup';
        $shiftNode = false;
        try {
            $returnVal = $this->adkBidsystemService->GetAdGroup($this->flexOBJECT['advertiser_id'], $this->flexOBJECT['ad_group_id']);
            return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    public function getAdGroupsByCampaign($advertiser_id = false, $campaign_id = false)
    {
        $this->push('advertiser_id', $advertiser_id, 'INT');
        $this->push('campaign_id', $campaign_id, 'ARRAY');
        $connection = $this->connect('AdGroup');
        $error = "Error Selecting Ad Groups for Campaign {$this->flexOBJECT['campaign_id']} ";
        $node = 'AdGroups';
        $pseudoNode = false;
        try {
            $returnVal = $this->adkBidsystemService->GetAdGroupsByCampaign($this->flexOBJECT['advertiser_id'], (array) $this->flexOBJECT['campaign_id']);
            return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage() . var_export($this->flexOBJECT));
        }
    }

    public function getActiveAdGroupsByCampaign($advertiser_id = false, $campaign_id = false)
    {
        $this->push('advertiser_id', $advertiser_id, 'INT');
        $this->push('campaign_id', $campaign_id, 'ARRAY');
        $connection = $this->connect('AdGroup');
        $error = "Error Selecting Ad Groups for Campaign {$this->flexOBJECT['campaign_id']} ";
        $node = 'AdGroups';
        $pseudoNode = false;
        try {
            $returnVal = $this->adkBidsystemService->GetActiveAdGroupsByCampaign($this->flexOBJECT['advertiser_id'], (array) $this->flexOBJECT['campaign_id']);
            return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage() . var_export($this->flexOBJECT));
        }
    }

    public function getAdGroupStats($advertiser_id = false, $ad_group_id = false, $start_date = false, $end_date = false)
    {
        $this->push('advertiser_id', $advertiser_id, 'INT');
        $this->push('ad_group_id', $ad_group_id, 'ARRAY');


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
        $this->push('start_date', $start_date, 'STR');
        $this->push('end_date', $end_date, 'STR');
        $connection = $this->connect('AdGroup');
        #######################################################################################################################
        #TODO: NOTE:
        # There is currently a bug in the SOAP layer that expects that this will ALWAYS be an array of more than one campaigns.
        # but the service DOES de-dup the campaigns sent in so we will just dup the campaign_id if there is just one
        ########################################################################################################################
        if (count((array) $this->flexOBJECT['ad_group_id']) == 1) {
            $this->flexOBJECT['ad_group_id'] = array($this->flexOBJECT['ad_group_id'], $this->flexOBJECT['ad_group_id']);
        }
        $error = "Error Selecting Ad Group Stats " . implode(",", (array) $this->flexOBJECT['ad_group_id']);
        $node = false;
        $shiftNode = 'AdGroups';
        try {
            $returnVal = $this->adkBidsystemService->GetAdGroupStats($this->flexOBJECT['advertiser_id'], (array) $this->flexOBJECT['ad_group_id'], $this->flexOBJECT['start_date'], $this->flexOBJECT['end_date']);
            return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    public function getCampaign($advertiser_id = false, $campaign_id = false)
    {
        $this->push('advertiser_id', $advertiser_id, 'INT');
        $this->push('campaign_id', $campaign_id, 'INT');
        $connection = $this->connect('Campaign');
        $error = "Error Selecting Campaign {$this->flexOBJECT['campaign_id']} ";
        $node = 'Campaign';
        $pseudoNode = false;
        try {
            $returnVal = $this->adkBidsystemService->GetCampaign($this->flexOBJECT['advertiser_id'], $this->flexOBJECT['campaign_id']);
            return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    //Commented out to show magicFn working
//    public function getCampaignList($advertiser_id = false)
//    {
//        $this->push('advertiser_id', $advertiser_id, 'INT');
//        $connection = $this->connect('Campaign');
//        $error = "Error Selecting Advertiser Campaigns {$this->flexOBJECT['campaign_id']} ";
//        $node = false;
//        $shiftNode = 'Campaigns';
//        try {
//            $returnVal = $this->adkBidsystemService->GetCampaignList($this->flexOBJECT['advertiser_id']);
//            return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//        } catch (Exception $e) {
//            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//        }
//    }

    public function getActiveCampaignList($advertiser_id = false)
    {
        $this->push('advertiser_id', $advertiser_id, 'INT');
        $connection = $this->connect('Campaign');
        $error = "Error Selecting Active Advertiser Campaigns {$this->flexOBJECT['campaign_id']} ";
        $node = false;
        $shiftNode = 'Campaigns';
        try {
            $returnVal = $this->adkBidsystemService->GetActiveCampaignList($this->flexOBJECT['advertiser_id']);
            return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    public function getCampaignStats($advertiser_id = false, $campaign_id = false, $start_date = false, $end_date = false)
    {
        $this->push('advertiser_id', $advertiser_id, 'INT');
        $this->push('campaign_id', $campaign_id, 'ARRAY');


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
        $this->push('start_date', $start_date, 'STR');
        $this->push('end_date', $end_date, 'STR');
        $connection = $this->connect('Campaign');

        $error = "Error Selecting  Campaigns Stats " . implode(",", (array) $this->flexOBJECT['campaign_id']);
        $node = false;
        $shiftNode = 'Campaigns';
        try {
            $returnVal = $this->adkBidsystemService->GetCampaignStats($this->flexOBJECT['advertiser_id'], (array) $this->flexOBJECT['campaign_id'], $this->flexOBJECT['start_date'], $this->flexOBJECT['end_date']);
            return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    public function getCampaignBidCostList($advertiser_id = false, $campaign_id = false, $start_date = false, $end_date = false)
    {
        $this->push('advertiser_id', $advertiser_id, 'INT');
        $this->push('campaign_id', $campaign_id, 'ARRAY');


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
        $this->push('start_date', $start_date, 'STR');
        $this->push('end_date', $end_date, 'STR');
        $connection = $this->connect('Campaign');
        #######################################################################################################################
        #TODO: NOTE:
        # There is currently a bug in the SOAP layer that expects that this will ALWAYS be an array of more than one campaigns.
        # but the service DOES de-dup the campaigns sent in so we will just dup the campaign_id if there is just one
        ########################################################################################################################
        if (count((array) $this->flexOBJECT['campaign_id']) == 1) {
            $this->flexOBJECT['campaign_id'] = array($this->flexOBJECT['campaign_id'], $this->flexOBJECT['campaign_id']);
        }
        $error = "Error Selecting Campaigns Bid Cost List " . implode(",", (array) $this->flexOBJECT['campaign_id']);
        $node = false;
        $shiftNode = 'Campaigns';
        try {
            $returnVal = $this->adkBidsystemService->GetCampaignBidCostList($this->flexOBJECT['advertiser_id'], (array) $this->flexOBJECT['campaign_id'], $this->flexOBJECT['start_date'], $this->flexOBJECT['end_date']);
            return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    //Setters
    public function setAdGroupActive($advertiser_id = false, $ad_group_id = false)
    {
        $this->push('advertiser_id', $advertiser_id, 'INT');
        $this->push('ad_group_id', $ad_group_id, 'INT');
        $connection = $this->connect('AdGroup');
        $error = "Error Activating AdGroup {$this->flexOBJECT['ad_group_id']} ";
        $node = false;
        $shiftNode = 'return';
        try {
            $returnVal = $this->adkBidsystemService->SetAdGroupActive($this->flexOBJECT['advertiser_id'], $this->flexOBJECT['ad_group_id']);
            return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    public function setAdGroupPaused($advertiser_id = false, $ad_group_id = false)
    {
        $this->push('advertiser_id', $advertiser_id, 'INT');
        $this->push('ad_group_id', $ad_group_id, 'INT');
        $connection = $this->connect('AdGroup');
        $error = "Error Pausing AdGroup {$this->flexOBJECT['ad_group_id']} ";
        $node = false;
        $shiftNode = 'return';
        try {
            $returnVal = $this->adkBidsystemService->SetAdGroupPaused($this->flexOBJECT['advertiser_id'], $this->flexOBJECT['ad_group_id']);
            return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    public function setCampaignActive($advertiser_id = false, $campaign_id = false)
    {
        $this->push('advertiser_id', $advertiser_id, 'INT');
        $this->push('campaign_id', $campaign_id, 'INT');
        $connection = $this->connect('Campaign');
        $error = "Error Activating Campaign {$this->flexOBJECT['campaign_id']} ";
        $node = false;
        $shiftNode = 'return';
        try {
            $returnVal = $this->adkBidsystemService->SetCampaignActive($this->flexOBJECT['advertiser_id'], $this->flexOBJECT['campaign_id']);
            return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

    public function setCampaignPaused($advertiser_id = false, $campaign_id = false)
    {
        $this->push('advertiser_id', $advertiser_id, 'INT');
        $this->push('campaign_id', $campaign_id, 'INT');
        $connection = $this->connect('Campaign');
        $error = "Error Pausing Campaign {$this->flexOBJECT['campaign_id']} ";
        $node = false;
        $shiftNode = 'return';
        try {
            $returnVal = $this->adkBidsystemService->SetCampaignPaused($this->flexOBJECT['advertiser_id'], $this->flexOBJECT['campaign_id']);
            return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
        } catch (Exception $e) {
            return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
        }
    }

//iNTERNAL PRIVATE STUFF
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
        $maxParams=0;
        $this->_availableFunctions = array('implementedFunctions' => array(), 'unimplementedFunctions' => array());
        foreach ($this->_availableServices as $service) {
            $this->_availableFunctions[$service] = array('status' => '', 'Functions' => array());
            $connection = $this->connect($service);
            if ($connection['status'] != 'success') {
                $this->_availableFunctions[$service]['status'] = 'unavailable';
            } else {
                $this->_availableFunctions[$service]['status'] = 'available';
                $functionList = json_decode(json_encode($this->adkBidsystemService->__getFunctions()), true);

                foreach ($functionList as $fn) {
                    list($returnType, $functionCall) = explode(" ", trim($fn), 2);
                    list($functionName, $args) = explode("(", str_replace(")", "", $functionCall), 2);
                    $pseudoName = strtolower(substr($functionName, 0, 1)) . substr($functionName, 1);
                    $args = explode(",", $args);
                    $parameters = array();
                    foreach ($args as $arg) {
                        $arg = trim($arg);
                        list($argType, $arg) = explode(" ", $arg, 2);
                        $parameters[] = array('parameterType' => $argType, 'parameterName' => str_replace("$","",$arg));
                    }
                    $maxParams=count($parameters)>$maxParams?count($parameters):$maxParams;
                    if (method_exists($this, $pseudoName)) {
                        $available = 'Yes';
                        $this->_availableFunctions['implementedFunctions'][$pseudoName] = array(
                            'remoteName' => $functionName
                            , 'localName' => $pseudoName
                            , 'parameters' => $parameters
                            , 'returnType' => $returnType
                            ,'service'=>$service
                            );
                    } else {
                        $available = 'No';
                        $this->_availableFunctions['unimplementedFunctions'][$pseudoName] = array(
                            'remoteName' => $functionName
                            , 'localName' => $pseudoName . ' [PENDING IMPLEMENTATION]'
                            , 'parameters' => $parameters
                            , 'returnType' => $returnType
                             ,'service'=>$service
                            );
                    }
                    $this->_availableFunctions[$service]['Functions'][$functionName] = array(
                        'soap_api' => $functionName
                        , 'adk-bs.class' => $pseudoName
                        , 'available' => $available
                        , 'returnType' => $returnType
                        , 'functionCall' => strtolower(substr($functionCall, 0, 1)) . substr($functionCall, 1)
                        , 'parameters' => $parameters
                    );
                }
            }
        }
        ksort($this->_availableFunctions['implementedFunctions']);
        ksort($this->_availableFunctions['unimplementedFunctions']);
        return(array('status' => 'success','Most Parameters in any function'=>$maxParams, 'Services' => $this->_availableFunctions));
    }

    public function __destruct()
    {
        if (isset($this->status) && $this->status['status'] == 'error') {
            echo json_encode($this->status);
        }
    }

    private function push($fld = 'campaign_id', $val = false, $type = 'INT')
    {
        if ($val == false) {
            return;
        }
        switch (strtoupper(substr($type, 0, 3))) {
            case 'ARR':
                if (!count($val)) {
                    return;
                }
                $this->flexOBJECT[$fld] = (array) $val;
                break;
            case 'INT':
                $this->flexOBJECT[$fld] = (int) $val;
                break;
            case 'STR':
                $this->flexOBJECT[$fld] = (string) $val;
                break;
            default:
                $this->flexOBJECT[$fld] = $val;
        }
        return;
    }

    private function connect($service = 'Campaign')
    {
        if (isset($this->_services[$service])) {
            $this->adkBidsystemService = $this->_services[$service];
            return array('status' => 'success', 'type' => 'cached');
        }
        $this->push('service', $service, 'STRING');
        try {
            $this->adkBidsystemService = false;
            $this->loginObject = new Soapheader(sprintf($this->flexOBJECT['namespace'], $this->flexOBJECT['service']), 'LoginObject', new LoginObject($this->flexOBJECT));

            $this->_services[$service] = new SoapClient(sprintf($this->flexOBJECT['wsdl'], $this->flexOBJECT['service']));
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
        $this->flexOBJECT['api_key'] = $api_key;
    }

    public function setPassword($password)
    {
        $this->flexOBJECT['password'] = $password;
    }

    public function setAdvertiserID($advertiser_id)
    {
        $this->flexOBJECT['advertiser_id'] = $advertiser_id;
    }

    public function setCampaignID($campaign_id)
    {
        $this->flexOBJECT['campaign_id'] = $campaign_id;
    }

    public function setAdGroupID($ad_group_id)
    {
        $this->flexOBJECT['ad_group_id'] = $ad_group_id;
    }

    public function setGeoCountry($country)
    {
        $this->flexOBJECT['geo_country'] = $country;
    }



######################################################################################################
######################################################################################################
##
##      NOTE! The below code was all auto-generated and should not be used as production code.
##      Thik of this as stub code. As such, i've commented it out, but you can move parts in as needed
##      and flavor to taste
##
######################################################################################################
######################################################################################################
//
//######################################################################################################
//######################################################################################################
//##      AdGroup
//######################################################################################################
//######################################################################################################
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: AdGroup
//     * createAdGroup($advertiser_id [int],$ad_group [AdGroup])
//     *
//     */
//
//    public function createAdGroup($advertiser_id = false,$ad_group = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('ad_group',$ad_group,'ADGROUP');
//    	$connection = $this->connect('AdGroup');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[ad_group]} ';
//    	$node = 'AdGroup';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->CreateAdGroup($this->flexObject[advertiser_id],$this->flexObject[ad_group]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: AdGroup
//     * createAdGroupList($advertiser_id [int],$ad_groups [ArrayOfAdGroup])
//     *
//     */
//
//    public function createAdGroupList($advertiser_id = false,$ad_groups = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('ad_groups',$ad_groups,'ARRAYOFADGROUP');
//    	$connection = $this->connect('AdGroup');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[ad_groups]} ';
//    	$node = 'AdGroup';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->CreateAdGroupList($this->flexObject[advertiser_id],$this->flexObject[ad_groups]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: AdGroup
//     * updateAdGroup($advertiser_id [int],$ad_group [AdGroup])
//     *
//     */
//
//    public function updateAdGroup($advertiser_id = false,$ad_group = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('ad_group',$ad_group,'ADGROUP');
//    	$connection = $this->connect('AdGroup');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[ad_group]} ';
//    	$node = 'AdGroup';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->UpdateAdGroup($this->flexObject[advertiser_id],$this->flexObject[ad_group]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: AdGroup
//     * updateAdGroupList($advertiser_id [int],$ad_groups [ArrayOfAdGroup])
//     *
//     */
//
//    public function updateAdGroupList($advertiser_id = false,$ad_groups = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('ad_groups',$ad_groups,'ARRAYOFADGROUP');
//    	$connection = $this->connect('AdGroup');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[ad_groups]} ';
//    	$node = 'AdGroup';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->UpdateAdGroupList($this->flexObject[advertiser_id],$this->flexObject[ad_groups]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//######################################################################################################
//######################################################################################################
//##      AdGroupCategory
//######################################################################################################
//######################################################################################################
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: AdGroupCategory
//     * addAdGroupCategory($advertiser_id [int],$category [AdGroupCategory])
//     *
//     */
//
//    public function addAdGroupCategory($advertiser_id = false,$category = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('category',$category,'ADGROUPCATEGORY');
//    	$connection = $this->connect('AdGroupCategory');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[category]} ';
//    	$node = 'AdGroupCategory';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->AddAdGroupCategory($this->flexObject[advertiser_id],$this->flexObject[category]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: AdGroupCategory
//     * addAdGroupCategoryList($advertiser_id [int],$categories [ArrayOfAdGroupCategory])
//     *
//     */
//
//    public function addAdGroupCategoryList($advertiser_id = false,$categories = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('categories',$categories,'ARRAYOFADGROUPCATEGORY');
//    	$connection = $this->connect('AdGroupCategory');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[categories]} ';
//    	$node = 'AdGroupCategory';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->AddAdGroupCategoryList($this->flexObject[advertiser_id],$this->flexObject[categories]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: AdGroupCategory
//     * getActiveAdGroupCategoryList($advertiser_id [int],$ad_group_id [int])
//     *
//     */
//
//    public function getActiveAdGroupCategoryList($advertiser_id = false,$ad_group_id = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('ad_group_id',$ad_group_id,'INT');
//    	$connection = $this->connect('AdGroupCategory');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[ad_group_id]} ';
//    	$node = 'AdGroupCategory';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetActiveAdGroupCategoryList($this->flexObject[advertiser_id],$this->flexObject[ad_group_id]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: AdGroupCategory
//     * getAdGroupCategoriesByCampaign($advertiser_id [int],$campaign_id [int])
//     *
//     */
//
//    public function getAdGroupCategoriesByCampaign($advertiser_id = false,$campaign_id = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('campaign_id',$campaign_id,'INT');
//    	$connection = $this->connect('AdGroupCategory');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[campaign_id]} ';
//    	$node = 'AdGroupCategory';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetAdGroupCategoriesByCampaign($this->flexObject[advertiser_id],$this->flexObject[campaign_id]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: AdGroupCategory
//     * getAdGroupCategory($advertiser_id [int],$ad_group_id [int],$category_id [int])
//     *
//     */
//
//    public function getAdGroupCategory($advertiser_id = false,$ad_group_id = false,$category_id = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('ad_group_id',$ad_group_id,'INT');
//	$this->push('category_id',$category_id,'INT');
//    	$connection = $this->connect('AdGroupCategory');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[ad_group_id],$this->flexObject[category_id]} ';
//    	$node = 'AdGroupCategory';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetAdGroupCategory($this->flexObject[advertiser_id],$this->flexObject[ad_group_id],$this->flexObject[category_id]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: AdGroupCategory
//     * getAdGroupCategoryList($advertiser_id [int],$ad_group_id [int])
//     *
//     */
//
//    public function getAdGroupCategoryList($advertiser_id = false,$ad_group_id = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('ad_group_id',$ad_group_id,'INT');
//    	$connection = $this->connect('AdGroupCategory');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[ad_group_id]} ';
//    	$node = 'AdGroupCategory';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetAdGroupCategoryList($this->flexObject[advertiser_id],$this->flexObject[ad_group_id]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: AdGroupCategory
//     * getAdGroupCategoryStats($advertiser_id [int],$ad_group_id [int],$category_ids [ArrayOfInt],$start_date [string],$end_date [string])
//     *
//     */
//
//    public function getAdGroupCategoryStats($advertiser_id = false,$ad_group_id = false,$category_ids = false,$start_date = false,$end_date = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('ad_group_id',$ad_group_id,'INT');
//	$this->push('category_ids',$category_ids,'ARRAYOFINT');
//	$this->push('start_date',$start_date,'STRING');
//	$this->push('end_date',$end_date,'STRING');
//    	$connection = $this->connect('AdGroupCategory');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[ad_group_id],$this->flexObject[category_ids],$this->flexObject[start_date],$this->flexObject[end_date]} ';
//    	$node = 'AdGroupCategory';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetAdGroupCategoryStats($this->flexObject[advertiser_id],$this->flexObject[ad_group_id],$this->flexObject[category_ids],$this->flexObject[start_date],$this->flexObject[end_date]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: AdGroupCategory
//     * removeAdgroupCategory($advertiser_id [int],$ad_group_id [int],$category_id [int])
//     *
//     */
//
//    public function removeAdgroupCategory($advertiser_id = false,$ad_group_id = false,$category_id = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('ad_group_id',$ad_group_id,'INT');
//	$this->push('category_id',$category_id,'INT');
//    	$connection = $this->connect('AdGroupCategory');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[ad_group_id],$this->flexObject[category_id]} ';
//    	$node = 'AdGroupCategory';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->RemoveAdgroupCategory($this->flexObject[advertiser_id],$this->flexObject[ad_group_id],$this->flexObject[category_id]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: AdGroupCategory
//     * setAdGroupCategoryPaused($advertiser_id [int],$ad_group_id [int],$category_id [int])
//     *
//     */
//
//    public function setAdGroupCategoryPaused($advertiser_id = false,$ad_group_id = false,$category_id = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('ad_group_id',$ad_group_id,'INT');
//	$this->push('category_id',$category_id,'INT');
//    	$connection = $this->connect('AdGroupCategory');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[ad_group_id],$this->flexObject[category_id]} ';
//    	$node = 'AdGroupCategory';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->SetAdGroupCategoryPaused($this->flexObject[advertiser_id],$this->flexObject[ad_group_id],$this->flexObject[category_id]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: AdGroupCategory
//     * setAdgroupCategoryActive($advertiser_id [int],$ad_group_id [int],$category_id [int])
//     *
//     */
//
//    public function setAdgroupCategoryActive($advertiser_id = false,$ad_group_id = false,$category_id = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('ad_group_id',$ad_group_id,'INT');
//	$this->push('category_id',$category_id,'INT');
//    	$connection = $this->connect('AdGroupCategory');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[ad_group_id],$this->flexObject[category_id]} ';
//    	$node = 'AdGroupCategory';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->SetAdgroupCategoryActive($this->flexObject[advertiser_id],$this->flexObject[ad_group_id],$this->flexObject[category_id]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: AdGroupCategory
//     * updateAdGroupCategory($advertiser_id [int],$category [AdGroupCategory])
//     *
//     */
//
//    public function updateAdGroupCategory($advertiser_id = false,$category = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('category',$category,'ADGROUPCATEGORY');
//    	$connection = $this->connect('AdGroupCategory');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[category]} ';
//    	$node = 'AdGroupCategory';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->UpdateAdGroupCategory($this->flexObject[advertiser_id],$this->flexObject[category]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: AdGroupCategory
//     * updateAdGroupCategoryList($advertiser_id [int],$categories [ArrayOfAdGroupCategory])
//     *
//     */
//
//    public function updateAdGroupCategoryList($advertiser_id = false,$categories = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('categories',$categories,'ARRAYOFADGROUPCATEGORY');
//    	$connection = $this->connect('AdGroupCategory');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[categories]} ';
//    	$node = 'AdGroupCategory';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->UpdateAdGroupCategoryList($this->flexObject[advertiser_id],$this->flexObject[categories]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//######################################################################################################
//######################################################################################################
//##      Advertiser
//######################################################################################################
//######################################################################################################
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Advertiser
//     * getAdvertiser($advertiser_id [int])
//     *
//     */
//
//    public function getAdvertiser($advertiser_id = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//    	$connection = $this->connect('Advertiser');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id]} ';
//    	$node = 'Advertiser';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetAdvertiser($this->flexObject[advertiser_id]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Advertiser
//     * getAdvertiserGeo($advertiser_id [int])
//     *
//     */
//
//    public function getAdvertiserGeo($advertiser_id = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//    	$connection = $this->connect('Advertiser');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id]} ';
//    	$node = 'Advertiser';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetAdvertiserGeo($this->flexObject[advertiser_id]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Advertiser
//     * getAdvertiserList()
//     *
//     */
//
//    public function getAdvertiserList(){
//
//    	$connection = $this->connect('Advertiser');
//    	$error = 'Error Selecting Campaign {} ';
//    	$node = 'Advertiser';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetAdvertiserList();
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Advertiser
//     * getAdvertiserStats($advertiser_id [int],$start_date [string],$end_date [string])
//     *
//     */
//
//    public function getAdvertiserStats($advertiser_id = false,$start_date = false,$end_date = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('start_date',$start_date,'STRING');
//	$this->push('end_date',$end_date,'STRING');
//    	$connection = $this->connect('Advertiser');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[start_date],$this->flexObject[end_date]} ';
//    	$node = 'Advertiser';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetAdvertiserStats($this->flexObject[advertiser_id],$this->flexObject[start_date],$this->flexObject[end_date]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Advertiser
//     * getGeoCountries()
//     *
//     */
//
//    public function getGeoCountries(){
//
//    	$connection = $this->connect('Advertiser');
//    	$error = 'Error Selecting Campaign {} ';
//    	$node = 'Advertiser';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetGeoCountries();
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Advertiser
//     * getGeoStates($country_code [string])
//     *
//     */
//
//    public function getGeoStates($country_code = false){
// 	$this->push('country_code',$country_code,'STRING');
//    	$connection = $this->connect('Advertiser');
//    	$error = 'Error Selecting Campaign {$this->flexObject[country_code]} ';
//    	$node = 'Advertiser';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetGeoStates($this->flexObject[country_code]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Advertiser
//     * setAdvertiserGeo($advertiser_id [int],$countries [ArrayOfGeoCountry])
//     *
//     */
//
//    public function setAdvertiserGeo($advertiser_id = false,$countries = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('countries',$countries,'ARRAYOFGEOCOUNTRY');
//    	$connection = $this->connect('Advertiser');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[countries]} ';
//    	$node = 'Advertiser';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->SetAdvertiserGeo($this->flexObject[advertiser_id],$this->flexObject[countries]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//######################################################################################################
//######################################################################################################
//##      BidIndex
//######################################################################################################
//######################################################################################################
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: BidIndex
//     * getBidIndex($advertiser_id [int],$ad_group_id [int],$category_id [int],$objective_id [int])
//     *
//     */
//
//    public function getBidIndex($advertiser_id = false,$ad_group_id = false,$category_id = false,$objective_id = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('ad_group_id',$ad_group_id,'INT');
//	$this->push('category_id',$category_id,'INT');
//	$this->push('objective_id',$objective_id,'INT');
//    	$connection = $this->connect('BidIndex');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[ad_group_id],$this->flexObject[category_id],$this->flexObject[objective_id]} ';
//    	$node = 'BidIndex';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetBidIndex($this->flexObject[advertiser_id],$this->flexObject[ad_group_id],$this->flexObject[category_id],$this->flexObject[objective_id]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//######################################################################################################
//######################################################################################################
//##      Campaign
//######################################################################################################
//######################################################################################################
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Campaign
//     * createCampaign($advertiser_id [int],$campaign [Campaign])
//     *
//     */
//
//    public function createCampaign($advertiser_id = false,$campaign = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('campaign',$campaign,'CAMPAIGN');
//    	$connection = $this->connect('Campaign');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[campaign]} ';
//    	$node = 'Campaign';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->CreateCampaign($this->flexObject[advertiser_id],$this->flexObject[campaign]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Campaign
//     * createCampaignList($advertiser_id [int],$campaigns [ArrayOfCampaign])
//     *
//     */
//
//    public function createCampaignList($advertiser_id = false,$campaigns = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('campaigns',$campaigns,'ARRAYOFCAMPAIGN');
//    	$connection = $this->connect('Campaign');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[campaigns]} ';
//    	$node = 'Campaign';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->CreateCampaignList($this->flexObject[advertiser_id],$this->flexObject[campaigns]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Campaign
//     * disableCampaignDayParting($advertiser_id [int],$campaign_id [int])
//     *
//     */
//
//    public function disableCampaignDayParting($advertiser_id = false,$campaign_id = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('campaign_id',$campaign_id,'INT');
//    	$connection = $this->connect('Campaign');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[campaign_id]} ';
//    	$node = 'Campaign';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->DisableCampaignDayParting($this->flexObject[advertiser_id],$this->flexObject[campaign_id]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Campaign
//     * enableCampaignDayParting($advertiser_id [int],$campaign_id [int])
//     *
//     */
//
//    public function enableCampaignDayParting($advertiser_id = false,$campaign_id = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('campaign_id',$campaign_id,'INT');
//    	$connection = $this->connect('Campaign');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[campaign_id]} ';
//    	$node = 'Campaign';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->EnableCampaignDayParting($this->flexObject[advertiser_id],$this->flexObject[campaign_id]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Campaign
//     * getCampaignDayParting($advertiser_id [int],$campaign_id [int])
//     *
//     */
//
//    public function getCampaignDayParting($advertiser_id = false,$campaign_id = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('campaign_id',$campaign_id,'INT');
//    	$connection = $this->connect('Campaign');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[campaign_id]} ';
//    	$node = 'Campaign';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetCampaignDayParting($this->flexObject[advertiser_id],$this->flexObject[campaign_id]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Campaign
//     * getCampaignGeo($advertiser_id [int],$campaign_id [int])
//     *
//     */
//
//    public function getCampaignGeo($advertiser_id = false,$campaign_id = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('campaign_id',$campaign_id,'INT');
//    	$connection = $this->connect('Campaign');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[campaign_id]} ';
//    	$node = 'Campaign';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetCampaignGeo($this->flexObject[advertiser_id],$this->flexObject[campaign_id]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Campaign
//     * getCampaignList($advertiser_id [int])
//     *
//     */
//
//    public function getCampaignList($advertiser_id = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//    	$connection = $this->connect('Campaign');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id]} ';
//    	$node = 'Campaign';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetCampaignList($this->flexObject[advertiser_id]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Campaign
//     * getCampaignListDayParting($advertiser_id [int],$campaign_ids [ArrayOfInt])
//     *
//     */
//
//    public function getCampaignListDayParting($advertiser_id = false,$campaign_ids = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('campaign_ids',$campaign_ids,'ARRAYOFINT');
//    	$connection = $this->connect('Campaign');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[campaign_ids]} ';
//    	$node = 'Campaign';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetCampaignListDayParting($this->flexObject[advertiser_id],$this->flexObject[campaign_ids]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Campaign
//     * setCampaignDayParting($advertiser_id [int],$day_parting [DayParting])
//     *
//     */
//
//    public function setCampaignDayParting($advertiser_id = false,$day_parting = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('day_parting',$day_parting,'DAYPARTING');
//    	$connection = $this->connect('Campaign');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[day_parting]} ';
//    	$node = 'Campaign';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->SetCampaignDayParting($this->flexObject[advertiser_id],$this->flexObject[day_parting]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Campaign
//     * setCampaignGeo($advertiser_id [int],$campaign_id [int],$countries [ArrayOfGeoCountry])
//     *
//     */
//
//    public function setCampaignGeo($advertiser_id = false,$campaign_id = false,$countries = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('campaign_id',$campaign_id,'INT');
//	$this->push('countries',$countries,'ARRAYOFGEOCOUNTRY');
//    	$connection = $this->connect('Campaign');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[campaign_id],$this->flexObject[countries]} ';
//    	$node = 'Campaign';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->SetCampaignGeo($this->flexObject[advertiser_id],$this->flexObject[campaign_id],$this->flexObject[countries]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Campaign
//     * setCampaignListDayParting($advertiser_id [int],$day_partings [ArrayOfDayParting])
//     *
//     */
//
//    public function setCampaignListDayParting($advertiser_id = false,$day_partings = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('day_partings',$day_partings,'ARRAYOFDAYPARTING');
//    	$connection = $this->connect('Campaign');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[day_partings]} ';
//    	$node = 'Campaign';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->SetCampaignListDayParting($this->flexObject[advertiser_id],$this->flexObject[day_partings]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Campaign
//     * updateCampaign($advertiser_id [int],$campaign [Campaign])
//     *
//     */
//
//    public function updateCampaign($advertiser_id = false,$campaign = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('campaign',$campaign,'CAMPAIGN');
//    	$connection = $this->connect('Campaign');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[campaign]} ';
//    	$node = 'Campaign';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->UpdateCampaign($this->flexObject[advertiser_id],$this->flexObject[campaign]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Campaign
//     * updateCampaignList($advertiser_id [int],$campaigns [ArrayOfCampaign])
//     *
//     */
//
//    public function updateCampaignList($advertiser_id = false,$campaigns = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('campaigns',$campaigns,'ARRAYOFCAMPAIGN');
//    	$connection = $this->connect('Campaign');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[campaigns]} ';
//    	$node = 'Campaign';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->UpdateCampaignList($this->flexObject[advertiser_id],$this->flexObject[campaigns]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//######################################################################################################
//######################################################################################################
//##      Category
//######################################################################################################
//######################################################################################################
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Category
//     * getCategoryList()
//     *
//     */
//
//    public function getCategoryList(){
//
//    	$connection = $this->connect('Category');
//    	$error = 'Error Selecting Campaign {} ';
//    	$node = 'Category';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetCategoryList();
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//######################################################################################################
//######################################################################################################
//##      Listing
//######################################################################################################
//######################################################################################################
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Listing
//     * createListing($advertiser_id [int],$listing [Listing])
//     *
//     */
//
//    public function createListing($advertiser_id = false,$listing = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('listing',$listing,'LISTING');
//    	$connection = $this->connect('Listing');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[listing]} ';
//    	$node = 'Listing';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->CreateListing($this->flexObject[advertiser_id],$this->flexObject[listing]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Listing
//     * createListingList($advertiser_id [int],$listings [ArrayOfListing])
//     *
//     */
//
//    public function createListingList($advertiser_id = false,$listings = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('listings',$listings,'ARRAYOFLISTING');
//    	$connection = $this->connect('Listing');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[listings]} ';
//    	$node = 'Listing';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->CreateListingList($this->flexObject[advertiser_id],$this->flexObject[listings]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Listing
//     * getActiveListingsByAdGroup($advertiser_id [int],$ad_group_ids [ArrayOfInt])
//     *
//     */
//
//    public function getActiveListingsByAdGroup($advertiser_id = false,$ad_group_ids = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('ad_group_ids',$ad_group_ids,'ARRAYOFINT');
//    	$connection = $this->connect('Listing');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[ad_group_ids]} ';
//    	$node = 'Listing';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetActiveListingsByAdGroup($this->flexObject[advertiser_id],$this->flexObject[ad_group_ids]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Listing
//     * getActiveListingsByCampaign($advertiser_id [int],$campaign_ids [ArrayOfInt])
//     *
//     */
//
//    public function getActiveListingsByCampaign($advertiser_id = false,$campaign_ids = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('campaign_ids',$campaign_ids,'ARRAYOFINT');
//    	$connection = $this->connect('Listing');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[campaign_ids]} ';
//    	$node = 'Listing';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetActiveListingsByCampaign($this->flexObject[advertiser_id],$this->flexObject[campaign_ids]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Listing
//     * getListing($advertiser_id [int],$listing_id [int])
//     *
//     */
//
//    public function getListing($advertiser_id = false,$listing_id = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('listing_id',$listing_id,'INT');
//    	$connection = $this->connect('Listing');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[listing_id]} ';
//    	$node = 'Listing';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetListing($this->flexObject[advertiser_id],$this->flexObject[listing_id]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Listing
//     * getListingList($advertiser_id [int],$listing_ids [ArrayOfInt])
//     *
//     */
//
//    public function getListingList($advertiser_id = false,$listing_ids = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('listing_ids',$listing_ids,'ARRAYOFINT');
//    	$connection = $this->connect('Listing');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[listing_ids]} ';
//    	$node = 'Listing';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetListingList($this->flexObject[advertiser_id],$this->flexObject[listing_ids]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Listing
//     * getListingStats($advertiser_id [int],$listing_ids [ArrayOfInt],$start_date [string],$end_date [string])
//     *
//     */
//
//    public function getListingStats($advertiser_id = false,$listing_ids = false,$start_date = false,$end_date = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('listing_ids',$listing_ids,'ARRAYOFINT');
//	$this->push('start_date',$start_date,'STRING');
//	$this->push('end_date',$end_date,'STRING');
//    	$connection = $this->connect('Listing');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[listing_ids],$this->flexObject[start_date],$this->flexObject[end_date]} ';
//    	$node = 'Listing';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetListingStats($this->flexObject[advertiser_id],$this->flexObject[listing_ids],$this->flexObject[start_date],$this->flexObject[end_date]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Listing
//     * getListingsByAdGroup($advertiser_id [int],$ad_group_ids [ArrayOfInt])
//     *
//     */
//
//    public function getListingsByAdGroup($advertiser_id = false,$ad_group_ids = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('ad_group_ids',$ad_group_ids,'ARRAYOFINT');
//    	$connection = $this->connect('Listing');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[ad_group_ids]} ';
//    	$node = 'Listing';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetListingsByAdGroup($this->flexObject[advertiser_id],$this->flexObject[ad_group_ids]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Listing
//     * getListingsByCampaign($advertiser_id [int],$campaign_ids [ArrayOfInt])
//     *
//     */
//
//    public function getListingsByCampaign($advertiser_id = false,$campaign_ids = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('campaign_ids',$campaign_ids,'ARRAYOFINT');
//    	$connection = $this->connect('Listing');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[campaign_ids]} ';
//    	$node = 'Listing';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetListingsByCampaign($this->flexObject[advertiser_id],$this->flexObject[campaign_ids]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Listing
//     * updateListing($advertiser_id [int],$listing [Listing])
//     *
//     */
//
//    public function updateListing($advertiser_id = false,$listing = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('listing',$listing,'LISTING');
//    	$connection = $this->connect('Listing');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[listing]} ';
//    	$node = 'Listing';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->UpdateListing($this->flexObject[advertiser_id],$this->flexObject[listing]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Listing
//     * updateListingList($advertiser_id [int],$listings [ArrayOfListing])
//     *
//     */
//
//    public function updateListingList($advertiser_id = false,$listings = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('listings',$listings,'ARRAYOFLISTING');
//    	$connection = $this->connect('Listing');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[listings]} ';
//    	$node = 'Listing';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->UpdateListingList($this->flexObject[advertiser_id],$this->flexObject[listings]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//######################################################################################################
//######################################################################################################
//##      Network
//######################################################################################################
//######################################################################################################
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Network
//     * getNetworkById($network_id [int])
//     *
//     */
//
//    public function getNetworkById($network_id = false){
// 	$this->push('network_id',$network_id,'INT');
//    	$connection = $this->connect('Network');
//    	$error = 'Error Selecting Campaign {$this->flexObject[network_id]} ';
//    	$node = 'Network';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetNetworkById($this->flexObject[network_id]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Network
//     * getNetworkByName($network_name [string])
//     *
//     */
//
//    public function getNetworkByName($network_name = false){
// 	$this->push('network_name',$network_name,'STRING');
//    	$connection = $this->connect('Network');
//    	$error = 'Error Selecting Campaign {$this->flexObject[network_name]} ';
//    	$node = 'Network';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetNetworkByName($this->flexObject[network_name]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Network
//     * getNetworkCategoryMinBid($network_id [int],$category_id [int])
//     *
//     */
//
//    public function getNetworkCategoryMinBid($network_id = false,$category_id = false){
// 	$this->push('network_id',$network_id,'INT');
//	$this->push('category_id',$category_id,'INT');
//    	$connection = $this->connect('Network');
//    	$error = 'Error Selecting Campaign {$this->flexObject[network_id],$this->flexObject[category_id]} ';
//    	$node = 'Network';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetNetworkCategoryMinBid($this->flexObject[network_id],$this->flexObject[category_id]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Network
//     * getNetworkList()
//     *
//     */
//
//    public function getNetworkList(){
//
//    	$connection = $this->connect('Network');
//    	$error = 'Error Selecting Campaign {} ';
//    	$node = 'Network';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetNetworkList();
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//######################################################################################################
//######################################################################################################
//##      Transaction
//######################################################################################################
//######################################################################################################
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Transaction
//     * getLastTransaction($advertiser_id [int])
//     *
//     */
//
//    public function getLastTransaction($advertiser_id = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//    	$connection = $this->connect('Transaction');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id]} ';
//    	$node = 'Transaction';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetLastTransaction($this->flexObject[advertiser_id]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }
//
//
//
//    /*
//     * Auto-Generated Code: USE AT YOUR OWN RISK!
//     * Service: Transaction
//     * getTransactionHistory($advertiser_id [int],$start_date [string],$end_date [string])
//     *
//     */
//
//    public function getTransactionHistory($advertiser_id = false,$start_date = false,$end_date = false){
// 	$this->push('advertiser_id',$advertiser_id,'INT');
//	$this->push('start_date',$start_date,'STRING');
//	$this->push('end_date',$end_date,'STRING');
//    	$connection = $this->connect('Transaction');
//    	$error = 'Error Selecting Campaign {$this->flexObject[advertiser_id],$this->flexObject[start_date],$this->flexObject[end_date]} ';
//    	$node = 'Transaction';
//    	$shiftNode = false;
//        try {
//                $returnVal = $this->adkBidsystemService->GetTransactionHistory($this->flexObject[advertiser_id],$this->flexObject[start_date],$this->flexObject[end_date]);
//                return $this->processReturn($returnVal, $error, __FUNCTION__, $node, $shiftNode);
//            } catch (Exception $e) {
//                return array('status' => 'error', 'line' => __LINE__, 'message' => $e->getMessage());
//            }
//
//    }


}
