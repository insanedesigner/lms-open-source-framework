<?php 
/*******************************************************************************/
/* Purpose 		: Managing the lead related request and response.
/* Created By 	: Jaiswar Vipin Kumar R.
/*******************************************************************************/
defined('BASEPATH') OR exit('No direct script access allowed');

class Lead{
	private $_databaseObject		= null;
	private $_intCompanyCode		= 0;
	private $_strTableName			= "master_leads";
	private $_strBranchCodeArr		= array();
	private $_strReportingUserArr	= array();
	
	/***************************************************************************/
	/* Purpose	: Initialization
	/* Inputs 	: pDatabaesObjectRefrence :: Database object reference,
				: $pIntCompanyCode :: company code,
				: $pStrBranchCodeArr :: Branch Code Array,
				: $pStrReportingUserArr :: reporting user list array
	/* Returns	: None.
	/* Created By 	: Jaiswar Vipin Kumar R.
	/***************************************************************************/
	public function __construct($pDatabaesObjectRefrence, $pIntCompanyCode = 0, $pStrBranchCodeArr = array(), $pStrReportingUserArr = array()){
		/* database reference */
		$this->_databaseObject	= $pDatabaesObjectRefrence;
		/* Company Code */
		$this->_intCompanyCode	= $pIntCompanyCode;
		/* Setting Branch Code */
		$this->_strBranchCodeArr= decodeKeyValueArr($pStrBranchCodeArr, true);
		/* Setting reporting user array */
		$this->_strReportingUserArr	= $pStrReportingUserArr;
	}
	
	/***************************************************************************/
	/* Purpose	: get lead attribute list.
	/* Inputs 	: None.
	/* Returns	: lead attribute array details.
	/* Created By : Jaiswar Vipin Kumar R.
	/***************************************************************************/
	public function getLeadAttributesList(){
		/* Query builder Array */
		$strFilterArr	= array(
									'table'=>'master_lead_attributes',
									'where'=>array(),
									'column'=>array('id', 'attri_slug_name')
							);
		
		/* getting record from location */
		return $this->_databaseObject->getDataFromTable($strFilterArr);
		
		/* removed used variables */
		unset($strFilterArr);
	}
	
	/***************************************************************************/
	/* Purpose	: get lead attribute list by company code.
	/* Inputs 	: None
	/* Returns	: lead attribute array details.
	/* Created By 	: Jaiswar Vipin Kumar R.
	/***************************************************************************/
	public function getLeadAttributesListByCompnayCode(){
		/* if company code is not passed then do needful */
		if($this->_intCompanyCode == 0){
			/* Return empty array */
			return array();
		}

		/* Query builder Array */
		$strFilterArr	= array(
									'table'=>'master_lead_attributes',
									'where'=>array('company_code'=>$this->_intCompanyCode),
									'column'=>array('id', 'attri_slug_key','attri_slug_name','attri_value_list','is_mandatory')
							);
		
		/* getting record from location */
		return $this->_databaseObject->getDataFromTable($strFilterArr);
		
		/* removed used variables */
		unset($strFilterArr);
	}
	
	/***************************************************************************/
	/* Purpose	: get lead attribute list by module code.
	/* Inputs 	: $intModuleCode :: Module code.
	/* Returns	: lead attribute array details.
	/* Created By 	: Jaiswar Vipin Kumar R.
	/***************************************************************************/
	public function getLeadAttributesListByModuleCode($intModuleCode  = 0){
		/* Variable initialization */
		$strReturnArr	= array();
		
		/* if module code is not passed then do needful */
		if($intModuleCode == 0){
			/* Return Empty error */
			return $strReturnArr;
		}
		
		/* Query builder Array */
		$strFilterArr	= array(
									'table'=>array('mater_module_lead_attribute','master_lead_attributes'),
									'join'=>array('','mater_module_lead_attribute.attri_code = master_lead_attributes.id'),
									'where'=>array('mater_module_lead_attribute.module_code'=>$intModuleCode,'master_lead_attributes.company_code'=>$this->_intCompanyCode),
									'column'=>array('module_code', 'attri_code','attri_slug_name')
							);
		
		/* getting record from location */
		return $this->_databaseObject->getDataFromTable($strFilterArr);
		
		/* removed used variables */
		unset($strFilterArr);
	}
	
	/***************************************************************************/
	/* Purpose	: get lead attribute list by module url.
	/* Inputs 	: $strModuleURL :: Module url.
	/* Returns	: lead attribute array details.
	/* Created By 	: Jaiswar Vipin Kumar R.
	/***************************************************************************/
	public function getLeadAttributesListByModuleUrl($strModuleURL  = ''){
		/* Variable initialization */
		$strReturnArr	= array();
		
		/* if module url is not passed then do needful */
		if($strModuleURL == ''){
			/* Filter array */
			$strFilterArr	= array(
										'table'=>'master_lead_attributes',
										'where'=>array('master_lead_attributes.company_code'=>$this->_intCompanyCode),
										'column'=>array('attri_slug_name','attri_slug_key','attri_value_list','attri_data_type','is_mandatory','attri_validation')
								);
		}else{
			/* Filter array */
			$strFilterArr	= array(
										'table'=>array('mater_module_lead_attribute','master_lead_attributes','master_modues'),
										'join'=>array('','mater_module_lead_attribute.attri_code = master_lead_attributes.id','master_modues.id = mater_module_lead_attribute.module_code'),
										'where'=>array('master_modues.module_url'=>$strModuleURL, 'master_lead_attributes.company_code'=>$this->_intCompanyCode),
										'column'=>array('module_code', 'attri_code','attri_slug_name','attri_slug_key','attri_value_list','attri_data_type','is_mandatory','attri_validation')
								);
		}
		
		/* getting record from modules lead attribute */
		return $this->_databaseObject->getDataFromTable($strFilterArr);
		
		/* removed used variables */
		unset($strFilterArr, $strWhereArr);
	}
	
	/***************************************************************************/
	/* Purpose	: get lead details by lead code.
	/* Inputs 	: $pIsCountNeed :: Counter needed,
				: $pStrFilterArr :: Lead filter.
	/* Returns	: lead details array details.
	/* Created By 	: Jaiswar Vipin Kumar R.
	/***************************************************************************/
	public function getLeadDetialsByLogger($pIsCountNeed = false, $pStrFilterArr = array()){
		/* Variable initialization */
		$strReturnArr	= $strLimitFilter	= array();
		$strWhereArr	= array($this->_strTableName.'.company_code'=>$this->_intCompanyCode,'trans_leads_'.$this->_intCompanyCode.'.branch_code'=>$this->_strBranchCodeArr, $this->_strTableName.'.lead_owner_code'=>$this->_strReportingUserArr);
		$strColumnArr	= array($this->_strTableName.'.*', 'trans_leads_'.$this->_intCompanyCode.'.*','master_lead_source.description as souce_name','master_status.description as status_name, master_status.parent_id as parent_code', $this->_strTableName.'.record_date as lead_created_date',$this->_strTableName.'.lead_owner_code as lead_owner_name');
		
		/* if reporting user is not set then do needful */
		if(empty($this->_strReportingUserArr)){
			/* Removed lead owner filter clause */ 
			unset($strWhereArr[$this->_strTableName.'.lead_owner_code']);
		}

		/* if lead code array is empty then do needful */
		if(!empty($intLeadCodeArr)){
			/* Adding lead filter */
			$strWhereArr	= array_merge($strWhereArr, array($this->_strTableName.'.id'=>$intLeadCodeArr));
		}
		
		/* if lead filter is not empty then do needful */
		if(!empty($pStrFilterArr)){
			if(isset($pStrFilterArr['offset']) && isset($pStrFilterArr['limit'])){
				$strLimitFilter['offset']	= $pStrFilterArr['offset'];
				$strLimitFilter['limit']	= $pStrFilterArr['limit'];
			}
			/* Removed page limit */
			unset($pStrFilterArr['offset'] , $pStrFilterArr['limit']);
			
			/* Adding lead filter */
			$strWhereArr	= array_merge($strWhereArr, $pStrFilterArr);
		}
		
		/* if needed count */
		if($pIsCountNeed){
			$strColumnArr	= array('COUNT('.$this->_strTableName.'.id) as recordCount ');
		}
		
		/* Query builder Array */
		$strFilterArr	= array(
									'table'=>array($this->_strTableName,'trans_leads_'.$this->_intCompanyCode,'master_lead_source','master_status'),
									'join'=>array('',$this->_strTableName.'.id = trans_leads_'.$this->_intCompanyCode.'.lead_code','master_lead_source.id = '.$this->_strTableName.'.lead_source_code','master_status.id = '.$this->_strTableName.'.status_code'),
									'where'=>$strWhereArr,
									'column'=>$strColumnArr,
									'order'=>array($this->_strTableName.'.id'=>'desc')
							);
		
		/* Pagination request */
		if(!empty($strLimitFilter)){
			/* Setting pagination */
			$strFilterArr	= array_merge($strFilterArr, $strLimitFilter);
		}
		
		/* getting record from modules lead attribute */
		return $this->_databaseObject->getDataFromTable($strFilterArr);
		
		/* removed used variables */
		unset($strFilterArr);
	}
	
	/***************************************************************************/
	/* Purpose	: Set new / update lead.
	/* Inputs 	: $pStrLeadArr :: lead array.
	/* Returns	: lead code array details.
	/* Created By 	: Jaiswar Vipin Kumar R.
	/***************************************************************************/
		/* variable initialization */
	public function setLeadDetails($pStrLeadArr = array()){
		$intLeadCode 		= 0;
		$blnIsDirect		= false;
		$intLeadOwnerCode	= (isset($pStrLeadArr['lead_owner_code']) && ((int)$pStrLeadArr['lead_owner_code'] >0 ))?$pStrLeadArr['lead_owner_code']:0;
		$strLocationArr		= array();
		
		/* if Debugging is set the do needful */
		if((isset($pStrLeadArr['is_debug'])) && ($pStrLeadArr['is_debug'])){
			debugVar('----------------To Enroll, passed array ----------------');
			debugVar($pStrLeadArr);
		}
		
		/* if lead details is empty then do needful */
		if(empty($pStrLeadArr)){
			/* Return empty array */
			return 'Lead details is empty.';
		}
		
		/* if added by user then do needful */
		if(isset($pStrLeadArr['is_direct'])){
			/* value over ridding */
			$blnIsDirect	= true;
		}
		
		/* Creating Led adding information */
		$strLedAddingArr	= array(
										'company_code'=>$this->_intCompanyCode,
										'lead_source_code'=>$pStrLeadArr['lead_source_code'],
								);
		
		/* removed used variables */
		unset($pStrLeadArr['is_direct'], $pStrLeadArr['lead_source_code'], $pStrLeadArr['lead_owner_code']);
		
		/* Creating status object */
		$statusObj 		= new status($this->_databaseObject, $this->_intCompanyCode);
		/* Getting default status details */
		$strStatusArr	= $statusObj->getDefaultLeadStatusDetilsByCompanyCode();
		/* Checking for default data set */
		if(empty($strStatusArr)){
			/* return error */
			return 'Default lead status is not set';
			
		}
		/* setting default status code */
		$strLedAddingArr	= array_merge($strLedAddingArr, array('status_code'=>$strStatusArr[0]['id']));
		/* Removed used variables */
		unset($statusObj, $strStatusArr);
		
		/* if Debugging is set the do needful */
		if((isset($pStrLeadArr['is_debug'])) && ($pStrLeadArr['is_debug'])){
			debugVar('----------------Master Lead Entry Array ----------------');
			debugVar($strLedAddingArr);
		}
		
		/* Setting data in master for generating the lead code */
		$intLeadCode	= $this->_databaseObject->setDataInTable(
																	array(
																			'table'=>$this->_strTableName,
																			'data'=>$strLedAddingArr
																	)
																);
		/* If lead code value is less 0 then */
		if($intLeadCode <= 0){
			return "Error occurred while added lead";
		}
		
		/* Creating location Object */
		$locationObj	= new Location($this->_databaseObject, $this->_intCompanyCode);
		/* If Lead owner code passed then do needful */
		if($intLeadOwnerCode > 0){
			/* Getting Branch and Region */
			$strLocationArr	= $locationObj->getLocationsByUserCode($intLeadOwnerCode);
		}
		
		/* if branch and location is not found the get default location details */
		if(empty($strLocationArr)){
			/* Getting Branch and Region */
			$strReultArr	= $locationObj->getLocationsByUserCode(-2);
			
			/* if location array is empty then do needful */
			if(empty($strReultArr)){
				/* Getting Branch and Region */
				$strReultArr	= $locationObj->getLocationsByUserCode(-1);
			}else{
				/* Variable initialization */
				$strReultContinerArr	= array();
				/* iterating the loop */
				foreach($strReultArr as $strReultArrKey => $strReultArrValue){
					/* Setting value */
					$strReultContinerArr[0][getCustomDefination($strReultArrValue['key_description'])]	= $strReultArrValue['value_description'];
				}
				/* value setting */
				$strReultArr	= $strReultContinerArr;
				/* removed used variables */
				unset($strReultContinerArr);
			}
			/* value initialization */
			$strLocationArr	= $strReultArr;
		}
		
		/* setting lead code */
		$pStrLeadArr['lead_code']			= $intLeadCode;
		$pStrLeadArr['branch_code']			= $strLocationArr[0]['branch_code'];
		$pStrLeadArr['region_code']			= $strLocationArr[0]['region_code'];
		
		/* if Debugging is set the do needful */
		if((isset($pStrLeadArr['is_debug'])) && ($pStrLeadArr['is_debug'])){
			debugVar('----------------Trans Lead Attributes ----------------');
			debugVar($pStrLeadArr);
		}
		/* Request from web service */
		if((isset($pStrLeadArr['is_debug'])) && ($pStrLeadArr['is_debug'])){
			$blnIsDirect	= true;
		}
		
		/* Removed used variables */
		unset($locationObj, $strLocationArr, $pStrLeadArr['is_debug']);
		
		/* Setting details in lead details table */
		$this->_databaseObject->setDataInTable(
												array(
													'table'=>'trans_leads_'.$this->_intCompanyCode,
													'data'=>$pStrLeadArr
												)
											);
		
		/* if lead add request came from application then do needful */
		if($blnIsDirect){
			/* updating the array */
			$strLedAddingArr['updated_by']	= $intLeadOwnerCode;
			/* Setting the lead owner */
			$this->setLeadOwner($intLeadCode, $intLeadOwnerCode, array_merge($pStrLeadArr, $strLedAddingArr));
		}
		
		/* Creating task object */
		$taskObj	= new Task($this->_databaseObject, $this->_intCompanyCode);
		/* Setting the task */
		$taskObj->setTask(array('leadCode'=>$intLeadCode, 'leadOwnerCode'=>$intLeadOwnerCode, 'updatedBy'=>$intLeadOwnerCode,'statusCode'=>$strLedAddingArr['status_code'],'action_type'=>LEAD_ASSIGMENT_EMAIL));
		/* Removed used variables */
		unset($taskObj);
		
		/* return lead code */
		return $intLeadCode;
	}
	
	/***************************************************************************/
	/* Purpose	: Setting lead updated details.
	/* Inputs 	: $pIntLeadCode :: lead code,
				: $pStrLeadDetailsUpdate :: Lead information,
				: $pIntUpdateBy :: Updated By
	/* Returns	: Return transaction status.
	/* Created By : Jaiswar Vipin Kumar R.
	/***************************************************************************/
	public function setLeadUpdatedDetails($pIntLeadCode = 0, $pStrLeadDetailsUpdate = array(), $pIntUpdateBy = 0 ){
		/* variable initialization */
		$intOperationStatus	= 0;
		
		/* Requested parameters are not passed then do needful */
		if(($pIntLeadCode == 0) || (empty($pStrLeadDetailsUpdate))){
			/* Return operation status */
			return $intOperationStatus;
		}
		
		/* Lead information */
		$strLeadInformationArr	= $this->getLeadDetialsByLogger(false,array('master_leads.id'=>$pIntLeadCode));
		$strUpdateRequest		= json_encode($pStrLeadDetailsUpdate);
		
		/* Setting lead updated details  */
		$this->_databaseObject->setUpdateData(
												array(
													'table'=>$this->_strTableName,
													'data'=>array('lead_source_code'=>$pStrLeadDetailsUpdate['lead_source_code']),
													'where'=>array('id'=>$pIntLeadCode)
												)
											);
											
		/* Removed lead source details */
		unset($pStrLeadDetailsUpdate['lead_source_code']);
		
		/* Setting lead attributes details  */
		$intOperationStatus 	=	$this->_databaseObject->setUpdateData(
																	array(
																		'table'=>'trans_leads_'.$this->_intCompanyCode,
																		'data'=>$pStrLeadDetailsUpdate,
																		'where'=>array('lead_code'=>$pIntLeadCode)
																	)
																);
		
		/* Setting communication history */
		$communicationHistoryObj	= new communicationhistory($this->_databaseObject, $this->_intCompanyCode);
		/* Setting communication history */
		$communicationHistoryObj->setCommuncationHistory(
															array(
																	'lead_code'=>$pIntLeadCode,
																	'lead_owner_code'=>$strLeadInformationArr[0]['lead_owner_code'],
																	'follow_up_date'=>$strLeadInformationArr[0]['next_followup_date'],
																	'status_code'=>$strLeadInformationArr[0]['status_code'],
																	'comments'=>'Update requested executed. Requested details was : <br />'.$strUpdateRequest,
																	'comm_text'=>(isset($strEmailArr['message'])?$strEmailArr['message']:''),
																	'is_system'=>0,
																	'updated_by'=>$pIntUpdateBy
															)
													);
		/* Removed used variables */
		unset($communicationHistoryObj, $strLeadInformationArr);
		
		/* return the transaction status */
		return $intOperationStatus;
	}
	
	/***************************************************************************/
	/* Purpose	: Setting lead owner.
	/* Inputs 	: $pIntLeadCode :: lead code,
				: $pIntLeadOwnerCode :: Led Owner code.
				: $pStrDetailsArr :: lead code.
	/* Returns	: Return transaction status.
	/* Created By : Jaiswar Vipin Kumar R.
	/***************************************************************************/
	public function setLeadOwner($pIntLeadCode = 0, $pIntLeadOwnerCode = 0, $pStrDetailsArr = array()){
		/* variable initialization */
		$intOperationStatus	= 0;
		
		/* Requested parameters are not passed then do needful */
		if(($pIntLeadCode == 0) || ($pIntLeadOwnerCode == 0) || (empty($pStrDetailsArr))){
			/* Return operation status */
			return $intOperationStatus;
		}
		/* Updating the lead assignment details */
		$strAssigementArr	= array(
										'lead_owner_code'=>$pIntLeadOwnerCode,
										'assigment_date'=>date('YmdHis')
								);
		
		/* Setting lead assignment assignment history  */
		$intOperationStatus	= $this->_databaseObject->setUpdateData(
																		array(
																			'table'=>$this->_strTableName,
																			'data'=>$strAssigementArr,
																			'where'=>array('id'=>$pIntLeadCode)
																		)
																	);
																	
		
		/* Updating the lead region and branch details */
		$strAssigementArr	= array(
										'branch_code'=>$pStrDetailsArr['branch_code'],
										'region_code'=>$pStrDetailsArr['region_code']
									);
		
		/* Setting lead lead region and branch assignment history  */
		$intOperationStatus	= $this->_databaseObject->setUpdateData(
																		array(
																			'table'=>'trans_leads_'.$this->_intCompanyCode,
																			'data'=>$strAssigementArr,
																			'where'=>array('lead_code'=>$pIntLeadCode)
																		)
																	);
																	
		
		/* Log array */
		$strAssigementArr	= array(
										'lead_code' => $pIntLeadCode, 
										'lead_owner_code' => $pIntLeadOwnerCode,
										'previous_lead_owber_code' => (isset($pStrDetailsArr['lead_owner_code'])?$pStrDetailsArr['lead_owner_code']:0),
										'status_code'=>$pStrDetailsArr['status_code'],
										'updated_by'=>$pStrDetailsArr['updated_by'],
										'updated_date'=>date('YmdHis')
								);
								
		/* Setting lead assignment assignment history  */
		$intOperationStatus	= $this->_databaseObject->setDataInTable(
																		array(
																			'table'=>'trans_lead_allocation_history',
																			'data'=>$strAssigementArr
																		)
																	);
		
		/* Creating task object */
		$taskObj	= new Task($this->_databaseObject, $this->_intCompanyCode);
		/* Transfer exiting all open task to new lead owner */
		$taskObj->setTransferOlderLeadOwnerTaskToNew(array('leadCode'=>$pIntLeadCode, 'leadOwnerCode'=>$pIntLeadOwnerCode, 'updatedBy'=>$pStrDetailsArr['updated_by'],'action_type'=>LEAD_ASSIGMENT_EMAIL));
		/* Removed used variables */
		unset($taskObj);
		
		/* Return Operation Status */
		return $intOperationStatus;
	}
}