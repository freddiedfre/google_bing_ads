<?php
/**
 * This scripts gets the fields available in a campaign report and 
 * stores them in a mysql database. The core libraries used belong to Google Adwords
 * @author     Fredrick Anyera
 */

// Include the initialization file
require_once dirname(dirname(__FILE__)) . '/init.php';

// Include the functions file
require 'functions.php';

/**
 * This function takes in an array containing client customer ID for each individual
 * account under the Ergodesoft MCC umbrella, and pulls the campaign details available at that moment.
 * 
 */

 function get_data_from_adwords_api($clientCustomers_id){
 	$user = new AdWordsUser();

	
	//loop through the clientCustomer Array, and get campaign details for each client
	foreach ($clientCustomers_id as $clientCustomer_id) {
    	$user->SetClientCustomerId($clientCustomer_id);
		
   		 // Log SOAP XML request and response.
    	$user->LogDefaults();
		
   		$campaignService = $user->GetCampaignService('v201502');
   		//$campaignService = $user->LoadService('CampaignService', ADWORDS_VERSION);

	    // Create selector.
		$selector = new Selector();
		// Fields to retrieve
		//$selector->fields = array('CampaignId', 'AdGroupId', 'Id', 'Criteria', 'CriteriaType', 'Impressions', 'Clicks', 'Cost');
		// Date rage for stats
		//$selector->dateRange->min = "20150414";
		//$selector->dateRange->max = "20150415";
		$selector->fields = array('Id', 'Name', 'Status', 'StartDate', 'Amount' );
	
		// Get all campaigns.
		$page = $campaignService->get($selector);
	
	if(isset($page->entries)){
	    foreach ($page->entries as $campaign) {
	    	//save data from api into local variables
	    	if(isset($campaign)) {
				$id = $campaign->id;
				$name = $campaign->name;
				$status = $campaign->status;
				$startDate = $campaign->startDate;
				$endDate = $campaign->endDate;
				$budgetId = $campaign->budget->budgetId;
				$budgetName =  $campaign->budget->name;
				$budgetPeriod =  $campaign->budget->period;
				$cost =  $campaign->budget->amount->microAmount/1000000;
	            
	    	//add the records to a database, check if table exist, if not create then populate it.
	    	$table_name = "adword_campaign_records";
			//check if table already exists in database. function defined in functions.php
			if (does_table_exist("parcels")) {$table_name = "adword_campaign_records";}
			
			//create table if it doesnt exist
			$rows = array('`auto_id`' => 'int NOT NULL AUTO_INCREMENT', '`id`' => 'varchar(255) NOT NULL', '`name`' => 'varchar(255) NOT NULL', '`status`' => 'varchar(255) NOT NULL', '`startDate`' => 'varchar(255) NOT NULL',
			 '`endDate`' => 'varchar(255) NOT NULL', '`budgetId`' => 'varchar(255) NOT NULL', '`budgetPeriod`' => 'varchar(255) NOT NULL', '`budgetName`' => 'varchar(255) NOT NULL','`Cost`' => 'varchar(255) NOT NULL', 'PRIMARY KEY' => '(`auto_id`)');

			$db = create_new_table($table_name, $rows);

			//add data pulled from API into the database
			$rows_of_data =  array('`id`' => $id, '`name`' => $name, '`status`' => $status, '`startDate`' => $startDate, '`endDate`' => $endDate, '`budgetId`' => $budgetId, '`budgetPeriod`' => $budgetPeriod, '`budgetName`' => $budgetName,'`Cost`' => $cost);
	
			
			if (populate_database_table($table_name, $rows_of_data)) {
					echo "Client " . $name. " has been successfully added to local adword database. <br/>" ;
					
			} else {
					echo "Client " . $name. " failed to be added in local adword database. <br/>" ;
					
				}	
	        
	    }
	}
	}
 }
}
 

 try {
 
 //now call the function defined above, and pass an array of clientCustomer Ids as the parameters.
 $clientCustomers_id = array('429-492-7124', '186-486-4569');
 get_data_from_adwords_api($clientCustomers_id);
 } catch (Exception $e) {
  printf("An error has occurred: %s\n", $e->getMessage());
}
 
 /*
  *End of Script 
  * 
  */
?>