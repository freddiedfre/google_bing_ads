<?php
date_default_timezone_set('America/Chicago');
/**
 * This scripts gets the fields available in a campaign report and 
 * packags them into a JSON object. The core libraries used belong to Google Adwords And Bing
 * @author     Fredrick Anyera M
 */

// Include the initialization file for google adwords api
require_once dirname(dirname(__FILE__)) . '/init.php';

//initialization files for bing ads 
include 'bingads\ReportingClasses.php';
include 'bingads\ClientProxy.php';
 
// Specify the BingAds\Reporting objects that will be used.
use BingAds\Reporting\SubmitGenerateReportRequest;
use BingAds\Reporting\KeywordPerformanceReportRequest;
use BingAds\Reporting\ReportFormat;
use BingAds\Reporting\ReportAggregation;
use BingAds\Reporting\AccountThroughAdGroupReportScope;
use BingAds\Reporting\CampaignReportScope;
use BingAds\Reporting\ReportTime;
use BingAds\Reporting\ReportTimePeriod;
use BingAds\Reporting\KeywordPerformanceReportFilter;
use BingAds\Reporting\DeviceTypeReportFilter;
use BingAds\Reporting\KeywordPerformanceReportColumn;
use BingAds\Reporting\PollGenerateReportRequest;
use BingAds\Reporting\ReportRequestStatusType;
use BingAds\Reporting\KeywordPerformanceReportSort;
use BingAds\Reporting\SortOrder;
// Specify the BingAds\Proxy object that will be used.
use BingAds\Proxy\ClientProxy;

/*
 * 
 * Fetch data from google adwords API
 * */
 function get_data_from_adwords_api($clientCustomers_id){
 	$user = new AdWordsUser();
 	$i=0;
	
	//loop through the clientCustomer Array, and get campaign details for each client
	foreach ($clientCustomers_id as $clientCustomer_id) {
    	$user->SetClientCustomerId($clientCustomer_id);
		
   		 // Log SOAP XML request and response.
    	$user->LogDefaults();

    	// Download the report to a file in the same directory as the example.
  $filePath = dirname(__FILE__) . '/report.csv';
		
// Load the service, so that the required classes are available.
  $user->LoadService('ReportDefinitionService', ADWORDS_VERSION);

  // Create selector.
  $selector = new Selector();
  $selector->fields = array('CampaignName', 'Impressions', 'Clicks', 'Cost', 'Ctr', 'ConvertedClicks', 'ConversionValue', 'ConversionsManyPerClick');

  // Optional: use predicate to filter out paused criteria.
  //$selector->predicates[] = new Predicate('Status', 'NOT_IN', array('PAUSED'));

  // Create report definition.
  $reportDefinition = new ReportDefinition();
  $reportDefinition->selector = $selector;
  $reportDefinition->reportName = 'Campaign Performance Report #' . uniqid();
  $reportDefinition->dateRangeType = 'YESTERDAY';
  $reportDefinition->reportType = 'CAMPAIGN_PERFORMANCE_REPORT';
  $reportDefinition->downloadFormat = 'XML';

  // Exclude criteria that haven't recieved any impressions over the date range.
  $reportDefinition->includeZeroImpressions = FALSE;

  // Set additional options.
  $options = array('version' => ADWORDS_VERSION);

  // Optional: Set skipReportHeader or skipReportSummary to suppress header or
  // summary rows in the report output.
  // $options['skipReportHeader'] = true;
  // $options['skipReportSummary'] = true;

  // Download report.
  ReportUtils::DownloadReport($reportDefinition, $filePath, $user, $options);

  // printf("Report with name '%s' was downloaded to '%s'.\n",
  //     $reportDefinition->reportName, $filePath);

  $doc = new DOMDocument();
    $doc->loadXML(file_get_contents($filePath));
    $xp = new DOMXPath($doc);

   $Details['ClientCustomerId'] = $clientCustomer_id;

    $CampaignName = $xp->query("/report/table/row/@campaign");
    foreach ($CampaignName as $item) {
   			 $Details['CampaignName'] = $item->nodeValue;
		}

    $Clicks = $xp->query("/report/table/row/@clicks");
    foreach ($Clicks as $item) {
   			 $Details['Clicks'] = $item->nodeValue;
		}

    $Impressions = $xp->query("/report/table/row/@impressions");
    foreach ($Impressions as $item) {
   			 $Details['Impressions'] = $item->nodeValue;
		}


	$ClickConversionRate = $xp->query("/report/table/row/@ctr");
	foreach ($ClickConversionRate as $item) {
   			 $Details['ClickConversionRate'] = $item->nodeValue;
		}

  $Conversions = $xp->query("/report/table/row/@conversions");
  foreach ($Conversions as $item) {
          $Details['Conversions'] = $item->nodeValue;
    }

  $ConvertedClicks = $xp->query("/report/table/row/@convertedClicks");
  foreach ($ConvertedClicks as $item) {
          $Details['ConvertedClicks'] = $item->nodeValue;
    }

  $ConversionValue = $xp->query("/report/table/row/@totalConvValue");
  foreach ($ConversionValue as $item) {
         $Details['ConversionValue'] = $item->nodeValue;
    }

	$AmountSpent = $xp->query("/report/table/row/@cost");
	foreach ($AmountSpent as $item) {
   			$Details['AmountSpent'] = $item->nodeValue/1000000;
		}  
	
	$Details['Date']=date('Y-m-d', time() - 60 * 60 * 24);
	
	//Post The Details Array as a JSON object
	return json_encode($Details);	
	}
}

/*
 *This function fecthes data from the bing Api 
 */
function get_data_from_bing_api()
{
	
}

 

 try {
 	
//TODO check if Date range has been set in post, and then proceed to process the request
if ((isset($_POST['startDate'])) && (isset($_POST['startDate']))){
	var_dump($_POST);
}
 
 //You will need to replace this client customers ID with your own

 get_data_from_adwords_api($clientCustomers_id);
 } catch (Exception $e) {
  printf("An error has occurred: %s\n", $e->getMessage());
  
}
 
 /*
  *End of Script 
  * 
  */
?>