<?php

//just include all objects
foreach (glob(__DIR__ . "/objects/*.php") as $objectFilename) {
    require_once $objectFilename;
}


class OMNIRestAPI
{

    /*--------------------------------
        Error Handling
    *--------------------------------*
    Because of the possible variations in error handling messages that could be returned if the required
    formats are not passed to the OMNI Accounts REST API, and the options available for configuring a
    company within Omni Accounts friendly error messages have been adopted in place of error codes.

    If a friendly error message has not been handled for whatever reason within the OMNI Accounts REST
    API a detailed more technical error message will be returned indicating any underlying failures that may
    have happened while executing the requested action.

    It is therefore strongly advised that the REST API Test Client application be used to test any calls that
    may be required to be made to the OMNI Accounts system from a 3rd  party application via the Omni REST API.
    *--------------------------------*/

    protected $hostPort;
    protected $userName;
    protected $password;
    protected $companyName;

    protected $additionalParameters = [];

    public function __construct($HostPort, $UserName, $Password, $CompanyName)
    {
        $this->hostPort = trim(trim($HostPort), '/');
        $this->userName = trim($UserName);
        $this->password = trim($Password);
        $this->companyName = trim($CompanyName);
    }

    private function request($method, $action, $data = [], $additionalParameters = [], $timeout = 300): OmniHTTPResponseObj
    {
        $params = [
                'UserName' => $this->userName,
                'Password' => $this->password,
                'CompanyName' => $this->companyName,
            ] + $additionalParameters;

        //URI Format: http://<Host>:<Port>/Customer/<Account Code>/<Branch Code>?UserName=<User Name>&Password=<Password>&CompanyName=<Company Name>
        $url = $this->hostPort . '/' . $action . '?' . http_build_query($params);
/*echo "<br>";
 echo "Start of URL<br>";
 echo $url;
 echo "<br>";
 echo "End of URL<br>";
*/ 
        $requestRaw = null;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($method != "GET") {
            $requestRaw = json_encode($data, JSON_PRETTY_PRINT);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $requestRaw);
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $rawResponse = curl_exec($ch);
        $curlDebug = curl_getinfo($ch);

        return new OmniHTTPResponseObj($url, $method, $requestRaw, $rawResponse, $curlDebug);
    }

    /*
    Purpose: Returns a customer account details.
    URI Format: http://<Host>:<Port>/Customer/<Account Code>/<Branch Code> ?UserName=<User Name>&Password=<Password>&CompanyName=<Company Name>
    Notes:
    Customer Accounts are keyed on Account Code (alpha-numeric 8) and  Branch Code (alpha-
    numeric 4). Customer Branches are an optional extra. If the branch code is emitted, “HO” will be used.

    When creating a customer, you always need a main HO branch.

    There are no individual balances on branches, only on the main HO account. Invoices are for
    specific branches, receipts are always posted to the customer overall, so additional branches
    should only be used where head office is responsible for payment.
    For more details on the customer field definitions, please refer to the Omni help.
     */
    private function GetCustomer($accountCode, $branchCode = "HO")
    {
        $rep = $this->request("GET", "Customer/{$accountCode}/{$branchCode}");
    }

    /*
     * Purpose:  Returns a Stock Items details.
     * URI Format:  http://<Host>:<Port>/Stock/<Stock Code>?UserName=<User Name>&Password=<Password>&CompanyName=<Company Name>
     */

    public function GetStock($parameters = []): OmniStockObj
    {
        $response = $this->request("GET", "Report/AutoStockLevels", [], $parameters);
        return (new OmniStockObj($response->getBody()))->setResponse($response);
    }

    //Purpose: Updates a Stock Item .
    //URI Format:  http://<Host>:<Port>/Stock/<Stock Code>?UserName=<User Name>&Password=<Password>&CompanyName=<Company Name>
    //
    //Note:
    //Any fields not passed into the JSON object will not be updated . Ideally you should only
    //be passing in fields that you would like to change .
    //The stock code is passed in the URI, and has to be specified .
    public function UpdateStock($stockCode)
    {

        $method = "PUT";
        
    }

    //Purpose:  Creates a Stock Item .
    //URI Format:  http://<Host>:<Port>/Sales Order/<Stock Code>?UserName=<User Name>&Password =<Password >&CompanyName =<Company Name >
    /*
    This is the bare minimum, so see the full list of available field, please see the PUT section
    above . Fields left out will be picked up the Omni defaults, so only pass in the fields that your system
    needs to control .
    Note:

    If no stock code is passed in the URI then an error message will be returned indicating
    that you need to provide a stock code .*/
    public function CreateStock($stockCode)
    {

        $method = "POST";

//
//                      {
//                          "stockitem" : {
//                          "stock_description" : "Stock Item Description 01"} }


    }

    //Purpose: Returns a Sales Order header and line details .
    //URI Format: http://<Host>:<Port>/Sales Order/<Sales Order Number>?UserName=<User Name>&Password =<Password >&CompanyName =<Company Name >
    public function GetSalesOrder(string $orderNumber): OmniSalesOrderObj
    {
        $response = $this->request("GET", "Sales Order/{$orderNumber}");
        return (new OmniSalesOrderObj($response->getBody()))->setResponse($response);
    }

    //Inserts a new Sales Order with the items specified in the JSON object .
    // URI Format: http://<Host>:<Port>/Sales Order/<Sales Order Number>?UserName=<User Name>& Password =<Password >&CompanyName =<Company Name >*/
    public function CreateSalesOrder(OmniSalesOrderObj $order, $orderNumber = ""): OmniHTTPResponseObj
    {
        return $this->request("POST", "Sales Order/{$orderNumber}", $order->getArray());
    }
    public function CreateSalesOrderNew($orderArray , $orderNumber = ""): OmniHTTPResponseObj
    {
        return $this->request("POST", "Sales Order/{$orderNumber}", $orderArray);
    } 
    

    //    PUT Creates or updates an open sales order with the items specified in the JSON object .
    // URI Format: http://<Host>:<Port>/Sales Order/<Sales Order Number>?UserName=<User Name>& Password =<Password >&CompanyName =<Company Name >*/
    /*NOTES:
        - Quantity can be up to 3 decimal places(depending on the Unit of Measure settings .
        - Selling Price can be up to 2 decimal places . If more accuracy is required, make use of the Selling Price Per field .
        - Selling Price Per follows the same rules as the quantity
        - The above applies to ALL sales documents
    */
    public function UpdateSalesOrder(OmniSalesOrderObj $order, $orderNumber): OmniHTTPResponseObj
    {
        return $this->request("PUT", "Sales Order/{$orderNumber}", $order->getArray());
    }

    //Purpose: Returns an Invoice header and line details .
    //URI Format: http://<Host>:<Port>/Invoice/<Invoice Number>?UserName=<User Name>&Password =<Password >&CompanyName =<Company Name >
    public function GetInvoice()
    {
        $method = "GET";
        $rep = $this->request("GET", "Sales Order/AutoStockLevels");
        return $rep;

    }

    /*
     * Purpose:  Inserts a new Invoice with the items specified in the JSON object .
                Only POST and GET are supported to insert and retrieve invoices . Invoice cannot be edited nor deleted .
    URI Format: http://<Host>:<Port>/Invoice/<Invoice Number>?UserName=<UserName >& Password =<Password >&CompanyName =<Company Name >

    <Invoice Number > is optional . If left blank, then the next system defined invoice reference number will be used and returned to you in the response

    See “GET Invoice” above for an example of full JSON, it is colour coded as to what fields are read -
    only, optional, etc . Fields left out, will pick up the system default.
    See PUT / POST Sales Order for explanation of limitations on decimal places on quantity and price .
    */
    public function CreateInvoice(OmniInvoiceObj $order, $orderNumber = ""): OmniHTTPResponseObj
    {
        if($orderNumber == "") {
             return $this->request("POST", "invoice/", $order->getArray());	
        } else {
             return $this->request("POST", "invoice/{$orderNumber}", $order->getArray());	
        }

    }
    /*
    Purpose:  Retrieve or insert a sales credit note
    URI Format:  http://<Host>:<Port>/Credit Note/<ReferenceNumber >?UserName =<UserName>&Password =<Password >&CompanyName =<CompanyName>
    */
    public function GetCreditNote()
    {

        $method = "GET";
    }

    /*
    Purpose:  Retrieve or insert a sales credit note
    URI Format:  http://<Host>:<Port>/Credit Note/<ReferenceNumber >?UserName =<UserName>&Password =<Password >&CompanyName =<CompanyName>
    When POSTing, <Reference Number > is optional . If left blank, then the next system defined
    credit note reference number will be used, and returned to you in the response

    See “GET Invoice” above for an example of full JSON, it is colour coded as to what fields are read -
    only, optional, etc . Fields left out, will pick up the system default.
    */
    public function CreateCreditNote(OmniCreditNoteObj $order, $orderNumber = ""): OmniHTTPResponseObj
    {
        return $this->request("POST", "Credit Note/{$orderNumber}", $order->getArray());

    }

    //Purpose: Returns a Proforma Invoice header and line details .
    //URI Format: http://<Host>:<Port>/Proforma  Invoice/<Invoice Number>?UserName=<User Name>&Password =<Password >&CompanyName =<Company Name >
    public function GetProformaInvoice()
    {
        $method = "GET";


    }

    /*Purpose:  Creates or updates a Proforma Invoice with the items specified in the JSON object .
    URI Format: http://<Host>:<Port>/Sales Order/<Proforma Invoice Number>?UserName=<User Name>&xPassword =<Password >&CompanyName =<Company Name >*/

    public function UpsertProformaInvoice()
    {
        $method = "PUT";

    }


    //Purpose:  Inserts a new Proforma Invoice with the items specified in the JSON object .
    //URI Format: http://<Host>:<Port>/Sales Order/<Proforma Invoice Number>?UserName=<User Name>&Password =<Password >&CompanyName =<Company Name >
    public function CreateProformaInvoice()
    {
        $method = "POST";
    }


    /*
    Purpose:  Returns a banking transaction, and all its target split lines .
    URI Format: http://<Host>:<Port>/BankingTransaction/<Tran  No>?UserName=<User Name>&Password =<Password >&CompanyName =<Company Name >
    */
    public function GetBankingTransaction()
    {
        $method = "GET";
    }

    /*
    Purpose: Inserts a new banking transaction, using the information specified in the JSON object .
    Only POST and GET are supported to insert and retrieve banking transactions .

    URI Format: http://<Host>:<Port>/BankingTransaction?UserName=<User Name>&Password =<Password >&CompanyName =<Company Name >

    When posting, no transaction number must be specified . Upon successful processing, the
    transaction number will be returned .

    NOTES:
    Set Transaction Category to Receipt for receiving money, and Payment for paying money .
    Pre - allocations are optional, and will determine what the banking transaction gets allocated to
    when processed .

    VAT Code is only applicable when the Target Ledger is Nominal Ledger .
    See “GET Banking Transaction” above for an example of full JSON, it is colour coded as to what
    fields are mandatory, read - only, optional, etc . Fields left out, will pick up the system default.*/
    public function CreateBankingTransaction()
    {
        $method = "POST";
    }

    //Purpose: Returns a stock item / s details .
    //URI Format: http://<Host>:<Port>/Stock Item/<Stock Code>?UserName=<User Name>&Password =<Password >&CompanyName =<Company Name >
    public function GetStockItem()
    {
        $method = "GET";
    }

//Purpose: Returns a quote header and details .
//URI Format: http://<Host>:<Port>/Quote/<Quote  Number>?UserName=<User Name>&Password =<Password >&CompanyName =<Company Name >

    public function GetQuote()
    {
        $method = "GET";
    }

    /*
    Purpose: Creates or updates an open quote with the items specified in the JSON object .
    URI Format : http://<Host>:<Port>/Quote/<Quote Number>?UserName=<User Name>&Password =<Password >&CompanyName =<Company Name >
    */
    public function UpsertQuote()
    {
        $method = "PUT";
    }

//Purpose: Creates a new quote with the items specified in the JSON object
//URI Format : http://<Host>:<Port>/Quote/<Quote Number>?UserName=<User Name>&Password =<Password >&CompanyName =<Company Name >

    public function CreateQuote()
    {
        $method = "POST";
    }


//    Purpose: Returns details of the specified Nominal Journal .
//    URI Format: GET  http://<Host>:<Port>/NLJournal/<Journal  Number>?UserName=<User Name>&Password =<Password >&CompanyName =<Company Name >
    public function GetNLJournal()
    {
        $method = "GET";
    }


    /**
     *   Purpose  Returns details of a specified communication on a customer’s account .
     *   URI Format: http://<Host>:<Port>/Comm/<CommNumber>?UserName=<User Name>&Password =<Password >&CompanyName =<Company Name >
     */
    public function PostCommunication()
    {
        $method = "POST";
    }


    /*
     * Purpose:  Extracts a given report’s data as a JSON array(ie . No formatting, or layout in PDF, HTML, etc)
      URI Format:  http://<Host>:<Port>/Report/<Report  Name>?UserName=<User Name>& Password=<Password>&CompanyName=<Company Name>

        To see a list of valid report names, in Omni go to File | Print Report
        You may also set up your own new report via File | New Report .
        Reports commonly used are Customer Export and Stock Export .
    */
    public function GetReport($reportName = "", $parameters = [])
    {
        return $this->request("GET", "Report/{$reportName}", [], $parameters);
    }

}
