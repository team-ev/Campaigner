<?php

namespace TeamEV\Campaigner;

class CampaignerClient
{
	/**
	 * Credentials for Campaigner
	 * @var array
	 */
	protected $credentials;

	/**
	 * SoapClient instance.
	 * @var SoapClient
	 */
	protected $soapClient;

	/**
	 * Create instance of the CampaignerClient
	 * 
	 * @param array $credentials
	 */
	public function __construct($username, $password)
	{
		$this->credentials = ["Username" => $username, "Password" => $password];  
	}

	protected function initSoap($url)
	{
		// $url = 'https://ws.campaigner.com/2013/01/contactmanagement.asmx?WSDL';   	   
		$this->soapClient = new \SoapClient($url, [
			'exceptions' => false,
			'compression'=> SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
			'soap_version'=> 'SOAP_1_1',
			'trace' => true,					   
			'connection_timeout' => 3000
		]);
	} 

	public function getGroupData($group_id)
	{
		$this->initSoap("https://ws.campaigner.com/2013/01/listmanagement.asmx?WSDL");

		$group = null;

		$response = $this->soapClient->ListContactGroups([
			'authentication' => $this->credentials
		]);

		if (is_soap_fault($response)) {
			echo "wrong";
		}
		else
		{
			foreach ($response->ListContactGroupsResult->ContactGroupDescription as $g) {
				if ($g->Id == $group_id) {
					$group = $g;
					break;
				}
			}
		}

		return $group;
	}

	/**
	 * Create or Update user.
	 * @param  array $users
	 * @return array
	 */
	public function createOrUpdateUsers(array $users, $triggerWorkflow = false)
	{
		$this->initSoap("https://ws.campaigner.com/2013/01/contactmanagement.asmx?WSDL");
		
		$request = [
			'authentication' => $this->credentials,
			'UpdateExistingContacts' => true, //Update the contact information 
		    'TriggerWorkflow' => $triggerWorkflow,
		    'contacts' => [
		    	'ContactData' => $users
		    ]
		];

		$response = $this->soapClient->ImmediateUpload($request);

		return $response->ImmediateUploadResult->UploadResultData;
	} 


}
