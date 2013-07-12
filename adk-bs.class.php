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

           foreach($magicFn['parameters'] as $offset=>$param){
              $this->push(str_replace("$","",$param['parameterName']),$params[$offset],$param['parameterType']);
           }
            $connection = $this->connect($magicFn['service']);
            $error="There was an error attempting to return data for magicClass $func()";
            $node=$magicFn['returnType'];
            /*
             * The API says the most number of parameters for a function is 5. that is
             * manageable for a switch statement...
             */
            try {
                switch(count($magicFn['parameters'])){
                    case 1:
                       $returnVal = $this->adkBidsystemService->$magicFn['remoteName'](
                               $this->flexOBJECT[$magicFn['parameters'][0]['parameterName']]
                               );
                        break;
                    case 2:
                        $returnVal = $this->adkBidsystemService->$magicFn['remoteName'](
                                $this->flexOBJECT[$magicFn['parameters'][0]['parameterName']]
                                ,$this->flexOBJECT[$magicFn['parameters'][1]['parameterName']]
                                );
                        break;
                    case 3:
                          $returnVal = $this->adkBidsystemService->$magicFn['remoteName'](
                                $this->flexOBJECT[$magicFn['parameters'][0]['parameterName']]
                                ,$this->flexOBJECT[$magicFn['parameters'][1]['parameterName']]
                                ,$this->flexOBJECT[$magicFn['parameters'][2]['parameterName']]
                                );
                        break;
                    case 4:
                          $returnVal = $this->adkBidsystemService->$magicFn['remoteName'](
                                $this->flexOBJECT[$magicFn['parameters'][0]['parameterName']]
                                ,$this->flexOBJECT[$magicFn['parameters'][1]['parameterName']]
                                ,$this->flexOBJECT[$magicFn['parameters'][2]['parameterName']]
                                ,$this->flexOBJECT[$magicFn['parameters'][3]['parameterName']]
                                );
                        break;
                    case 5:
                          $returnVal = $this->adkBidsystemService->$magicFn['remoteName'](
                                $this->flexOBJECT[$magicFn['parameters'][0]['parameterName']]
                                ,$this->flexOBJECT[$magicFn['parameters'][1]['parameterName']]
                                ,$this->flexOBJECT[$magicFn['parameters'][2]['parameterName']]
                                ,$this->flexOBJECT[$magicFn['parameters'][3]['parameterName']]
                                ,$this->flexOBJECT[$magicFn['parameters'][4]['parameterName']]
                                );
                        break;
                    default:
                         return array('status' => 'error', 'line' => __LINE__, 'message' =>"We attempted to call a magic function {$magicFn['remoteName']} with ".count($params)." parameters and the function thinks there shold be ".count($magicFn['parameters']));
                        break;
                }
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

}
