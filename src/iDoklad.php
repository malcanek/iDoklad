<?php
/**
 * Description of iDoklad
 * Class for iDoklad api
 * @author Jan Malcánek
 */

namespace iDoklad;
use Exception;

class iDoklad {
    private static $token;
    private static $url = 'https://app.idoklad.cz/developer/api/';
    private static $XApp;
    private static $XAppVersion;
    
    /**
     * 
     * @param string $XApp
     * @param string $XAppVersion
     * @param string $token
     */
    public static function setEnviroment($XApp, $XAppVersion, $token = null){
        self::$XApp = $XApp;
        self::$XAppVersion = $XAppVersion;
        if(!empty($token)){
            self::$token = $token;
        }
    }

    /**
     * 
     * @param string $email
     * @param string $password
     * @throws Exception
     */
    public static function getToken($email, $password){
        if(empty(self::$XApp) || empty(self::$XAppVersion)){
            throw new Exception('Zadejte nazev aplikace a jeji verzi');
        }
        
        $token = self::curlData('Agendas/GetSecureToken', array('username' => $email, 'password' => $password));
        $ret = json_decode($token, true);
        if(!empty($ret['Message'])){
            throw new Exception($ret['Message']);
        } else {
            self::$token = $token;
            return $token;
        }
    }
    
    /**
     * 
     * @param string $action
     * @param array $params
     * @param string $header
     * @return mixed
     */
    private static function curlData($action, $params = array(), $header = 'GET', $params_post = array()){
        $curl = curl_init();
        $curl_opt = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => self::$url.$action.'?'.  http_build_query($params),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'X-App: '.self::$XApp,
                'X-App-Version: '.self::$XAppVersion,
                (empty(self::$token) ? '' : 'SecureToken: '.self::$token)
            ),
            CURLOPT_SSL_VERIFYPEER => false
        );
        switch($header){
            case 'PUT':
                $curl_opt[CURLOPT_CUSTOMREQUEST] = "PUT";
                if(!empty($params_post)){
                    $curl_opt[CURLOPT_POSTFIELDS] = json_encode($params_post);
                } else {
                    $curl_opt[CURLOPT_POSTFIELDS] = http_build_query($params_post);
                }
                break;
            case 'POST':
                $curl_opt[CURLOPT_POSTFIELDS] = json_encode($params_post);
                break;
            case 'DELETE':
                $curl_opt[CURLOPT_CUSTOMREQUEST] = "DELETE";
                $curl_opt[CURLOPT_POSTFIELDS] = http_build_query($params_post);
                break;
        }
        curl_setopt_array($curl, $curl_opt);
        $data = curl_exec($curl);
        curl_close($curl);
        return $data;
    }
    
    /**
     * 
     * @throws Exception
     */
    private static function checkToken(){
        if(empty(self::$token)){
            throw new Exception('Zadejte token');
        }
    }
    
    /**
     * 
     * @param string $base64
     * @param string $path
     * @return string
     */
    private static function base64toPDF($base64, $path){
        $pdf_string = base64_decode($base64);
        $path = rtrim($path,'/');
        $name = 'iDoklad_'.time().'.pdf';
        $pdf = fopen($path.'/'.$name, 'w');
        fwrite($pdf, $pdf_string);
        fclose($pdf);
        return $path.'/'.$name;
    }


    /* Invoices */

    /**
     * 
     * @param array $filter
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-IssuedInvoices_ConstantSymbolId_IsPaid_CurrencyId_DateLastChange_DateOfIssue_DateOfPayment_Exported_Filter_FilterExported_OrderBy_Query_Page_PageSize
     */
    public static function getInvoices($filter = array()){
        self::checkToken();
        $invoices = self::curlData('IssuedInvoices', $filter);
        return json_decode($invoices, true);
    }
    
    /**
     * 
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-IssuedInvoices-Default
     */
    public static function getInvoiceTemplate(){
        self::checkToken();
        $template = self::curlData('IssuedInvoices/Default');
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param array $filter
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-IssuedInvoices-Expand_ConstantSymbolId_IsPaid_CurrencyId_DateLastChange_DateOfIssue_DateOfPayment_Exported_Filter_FilterExported_OrderBy_Query_Page_PageSize
     */
    public static function getInvoicesFull($filter = array()){
        self::checkToken();
        $invoices = self::curlData('IssuedInvoices/Expand', $filter);
        return json_decode($invoices, true);
    }
    
    /**
     * 
     * @param int $id
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-IssuedInvoices-id
     */
    public static function getInvoice($id){
        self::checkToken();
        $invoice = self::curlData('IssuedInvoices/'.$id);
        return json_decode($invoice, true);
    }
    
    /**
     * 
     * @param int $id
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-IssuedInvoices-id-Expand
     */
    public static function getInvoiceFull($id){
        self::checkToken();
        $invoice = self::curlData('IssuedInvoices/'.$id.'/Expand');
        return json_decode($invoice, true);
    }
    
    /**
     * 
     * @param int $id
     * @param string $path
     * @return string
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-IssuedInvoices-id-GetPdf
     */
    public static function getInvoicePDF($id,$path){
        self::checkToken();
        $pdf_string = self::curlData('IssuedInvoices/'.$id.'/GetPdf');
        $path = self::base64toPDF($pdf_string, $path);
        return $path;
    }
    
    /**
     * 
     * @param int $id
     * @param string $path
     * @return string
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-IssuedInvoices-id-GetCashVoucherPdf
     */
    public static function getCashVoucherPDF($id, $path){
        self::checkToken();
        $pdf_string = self::curlData('IssuedInvoices/'.$id.'/GetCashVoucherPdf');
        if($pdf_string == "Invoice don't have cash voucher."){
            return 'No cash voucher';
        } else {
            $path = self::base64toPDF($pdf_string, $path);
            return $path;
        }
    }
    
    /**
     * 
     * @param int $customer_id
     * @param array $filter
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-IssuedInvoices-contactId-IssuedInvoices_ConstantSymbolId_IsPaid_CurrencyId_DateLastChange_DateOfIssue_DateOfPayment_Exported_Filter_FilterExported_OrderBy_Query_Page_PageSize
     */
    public static function getInvoicesByCustomer($customer_id, $filter = array()){
        self::checkToken();
        $invoices = self::curlData('IssuedInvoices/'.$customer_id.'/IssuedInvoices', $filter);
        return json_decode($invoices, true);
    }
    
    /**
     * 
     * @param int $id
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-IssuedInvoices-id-MyDocumentAddress
     */
    public static function invoiceMyDocumentAddress($id){
        self::checkToken();
        $invoices = self::curlData('IssuedInvoices/'.$id.'/MyDocumentAddress');
        return json_decode($invoices, true);
    }
    
    /**
     * 
     * @param int $id
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-IssuedInvoices-id-PurchaserDocumentAddress
     */
    public static function invoicePurchaserDocumentAddress($id){
        self::checkToken();
        $invoices = self::curlData('IssuedInvoices/'.$id.'/PurchaserDocumentAddress');
        return json_decode($invoices, true);
    }
    
    /**
     * 
     * @param int $id
     * @return boolean
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/PUT-api-IssuedInvoices-id-FullyPay_dateOfPayment
     */
    public static function invoiceFullyPay($id, $filter = array()){
        self::checkToken();
        $invoices = self::curlData('IssuedInvoices/'.$id.'/FullyPay', $filter, 'PUT');
        return $invoices;
    }
    
    /**
     * 
     * @param int $id
     * @return boolean
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/PUT-api-IssuedInvoices-id-FullyUnpay
     */
    public static function invoiceFullyUnpay($id){
        self::checkToken();
        $invoices = self::curlData('IssuedInvoices/'.$id.'/FullyUnpay', array(), 'PUT');
        return $invoices;
    }
    
    /**
     * 
     * @param int $id
     * @return boolean
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/PUT-api-IssuedInvoices-id-SendMailToPurchaser
     */
    public static function invoiceSendMailToPurchaser($id){
        self::checkToken();
        $invoices = self::curlData('IssuedInvoices/'.$id.'/SendMailToPurchaser', array(), 'PUT');
        return $invoices;
    }
    
    /**
     * 
     * @param array $params
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/POST-api-IssuedInvoices
     */
    public static function newInvoice($params){
        self::checkToken();
        $template = self::curlData('IssuedInvoices', array(), 'POST', $params);
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param int $id
     * @param array $params
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/PUT-api-IssuedInvoices-id
     */
    public static function updateInvoice($id,$params){
        self::checkToken();
        $template = self::curlData('IssuedInvoices/'.$id, array(), 'PUT', $params);
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param int $id
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/DELETE-api-IssuedInvoices-id
     */
    public static function deleteInvoice($id){
        self::checkToken();
        self::curlData('IssuedInvoices/'.$id, array(), 'DELETE', array());
    }
    
    /**
     * 
     * @param int $id
     * @param int $exported
     * @return boolean
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/PUT-api-IssuedInvoices-id-Exported-value
     */
    public static function updateInvoiceExported($id, $exported){
        self::checkToken();
        $template = self::curlData('IssuedInvoices/'.$id.'/Exported/'.$exported, array(), 'PUT', array());
        return $template;
    }


    /* Agenda */
    
    /**
     * 
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-Agendas-GetAgendaBankAccounts
     */
    public static function getBankAccounts(){
        self::checkToken();
        $template = self::curlData('Agendas/GetAgendaBankAccounts');
        return json_decode($template, true);
    }
    
    /**
     * 
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-Agendas-GetAgendaContact
     */
    public static function getAgendaContact(){
        self::checkToken();
        $template = self::curlData('Agendas/GetAgendaContact');
        return json_decode($template, true);
    }
    
    /**
     * 
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-Agendas-GetAgendaContactExpand
     */
    public static function getAgendaContactExpand(){
        self::checkToken();
        $template = self::curlData('Agendas/GetAgendaContactExpand');
        return json_decode($template, true);
    }
    
    /**
     * 
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-Agendas-GetAgendaSummary
     */
    public static function getSummary(){
        self::checkToken();
        $template = self::curlData('Agendas/GetAgendaSummary');
        return json_decode($template, true);
    }
    
    /**
     * 
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-Agendas-GetSummaryQuarters
     */
    public static function getSummaryQuarters(){
        self::checkToken();
        $template = self::curlData('Agendas/GetSummaryQuarters');
        return json_decode($template, true);
    }
    
    /**
     * 
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-Agendas-GetTopPartners
     */
    public static function getTopPartners(){
        self::checkToken();
        $template = self::curlData('Agendas/GetTopPartners');
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param array $filter
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-Agendas_Page_PageSize
     */
    public static function getAgendas($filter = array()){
        self::checkToken();
        $template = self::curlData('Agendas', $filter);
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param int $id
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-Agendas-id
     */
    public static function getAgendaDetail($id){
        self::checkToken();
        $template = self::curlData('Agendas/'.$id);
        return json_decode($template, true);
    }
    
    /* Contacts */
    
    /**
     * 
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-Contacts-Default
     */
    public static function getContactTemplate(){
        self::checkToken();
        $template = self::curlData('Contacts/Default');
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param array $filter
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-Contacts_DateLastChange_Query_Filter_Page_PageSize
     */
    public static function getContacts($filter = array()){
        self::checkToken();
        $template = self::curlData('Contacts', $filter);
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param type $filter
     * @return type
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-Contacts-Expand_DateLastChange_Query_Filter_Page_PageSize
     */
    public static function getContactsExpand($filter = array()){
        self::checkToken();
        $template = self::curlData('Contacts/Expand', $filter);
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param int $id
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-Contacts-id
     */
    public static function getContactDetail($id){
        self::checkToken();
        $template = self::curlData('Contacts/'.$id);
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param int $id
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-Contacts-id-Expand
     */
    public static function getContactDetailExpand($id){
        self::checkToken();
        $template = self::curlData('Contacts/'.$id.'/Expand');
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param array $params
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/POST-api-Contacts
     */
    public static function newContact($params){
        self::checkToken();
        $template = self::curlData('Contacts', array(), 'POST', $params);
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param int $id
     * @param array $params
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/PUT-api-Contacts-id
     */
    public static function updateContact($id, $params){
        self::checkToken();
        $template = self::curlData('Contacts/'.$id, array(), 'PUT', $params);
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param int $id
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/DELETE-api-Contacts-id
     */
    public static function deleteContact($id){
        self::checkToken();
        self::curlData('Contacts/'.$id, array(), 'DELETE', array());
    }


    /* Document addresses */
    
    /**
     * 
     * @param int $id
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-DocumentAddresses-id
     */
    public static function getDocumentAddresses($id){
        self::checkToken();
        $template = self::curlData('DocumentAddresses/'.$id);
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param int $id
     * @param array $params
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/PUT-api-DocumentAddresses-id
     */
    public static function updateDocumentAddresses($id, $params){
        self::checkToken();
        $template = self::curlData('DocumentAddresses/'.$id, array(), 'PUST', $params);
        return json_decode($template, true);
    }


    /* Credit notes */
    
    /**
     * 
     * @param array $filter
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-CreditNotes_ConstantSymbolId_IsPaid_CurrencyId_DateLastChange_DateOfIssue_DateOfPayment_Exported_Filter_FilterExported_OrderBy_Query_Page_PageSize
     */
    public static  function getCreditNotes($filter = array()){
        self::checkToken();
        $template = self::curlData('CreditNotes', $filter);
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param array $filter
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-CreditNotes-Expand_ConstantSymbolId_IsPaid_CurrencyId_DateLastChange_DateOfIssue_DateOfPayment_Exported_Filter_FilterExported_OrderBy_Query_Page_PageSize
     */
    public static  function getCreditNotesExpand($filter = array()){
        self::checkToken();
        $template = self::curlData('CreditNotes/Expand', $filter);
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param int $id
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-CreditNotes-id
     */
    public static function getCreditNoteDetail($id){
        self::checkToken();
        $template = self::curlData('CreditNotes/'.$id);
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param int $id
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-CreditNotes-id-Expand
     */
    public static function getCreditNoteDetailExpand($id){
        self::checkToken();
        $template = self::curlData('CreditNotes/'.$id.'/Expand');
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param int $id
     * @param array $filter
     * @return boolean
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/PUT-api-CreditNotes-id-FullyPay_dateOfPayment
     */
    public static function creditNoteFullyPay($id, $filter){
        self::checkToken();
        $template = self::curlData('CreditNotes/'.$id.'/FullyPay', $filter);
        return $template;
    }
    
    /**
     * 
     * @param int $id
     * @return boolean
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/PUT-api-CreditNotes-id-FullyUnpay
     */
    public static function creditNoteFullyUnpay($id){
        self::checkToken();
        $template = self::curlData('CreditNotes/'.$id.'/FullyUnpay');
        return $template;
    }
    
    /**
     * 
     * @param int $id
     * @param int $value
     * @return boolean
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/PUT-api-CreditNotes-id-Exported-value
     */
    public static function updateCreditNoteExported($id, $value){
        self::checkToken();
        $template = self::curlData('CreditNotes/'.$id.'/Exported/'.$value);
        return $template;
    }


    /* Received invoices */
    
    /**
     * 
     * @param array $filter
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-ReceivedInvoices_PaymentStatus_DateOfReceiving_CurrencyId_DateLastChange_DateOfIssue_DateOfPayment_Exported_Filter_FilterExported_OrderBy_Query_Page_PageSize
     */
    public static function getReceivedInvoices($filter = array()){
        self::checkToken();
        $invoices = self::curlData('Receivedinvoices', $filter);
        return json_decode($invoices, true);
    }
    
    /**
     * 
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-Receivedinvoices-Default
     */
    public static function getReceivedInvoiceTemplate(){
        self::checkToken();
        $template = self::curlData('Receivedinvoices/Default');
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param array $filter
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-Receivedinvoices-Expand_PaymentStatus_DateOfReceiving_CurrencyId_DateLastChange_DateOfIssue_DateOfPayment_Exported_Filter_FilterExported_OrderBy_Query_Page_PageSize
     */
    public static function getReceivedInvoicesExpand($filter = array()){
        self::checkToken();
        $invoices = self::curlData('Receivedinvoices/Expand', $filter);
        return json_decode($invoices, true);
    }
    
    /**
     * 
     * @param int $id
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-ReceivedInvoices-id
     */
    public static function getReceivedInvoice($id){
        self::checkToken();
        $invoice = self::curlData('Receivedinvoices/'.$id);
        return json_decode($invoice, true);
    }
    
    /**
     * 
     * @param int $id
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-Receivedinvoices-id-Expand
     */
    public static function getReceivedInvoiceExpand($id){
        self::checkToken();
        $invoice = self::curlData('Receivedinvoices/'.$id.'/Expand');
        return json_decode($invoice, true);
    }
    
    /**
     * 
     * @param int $supplier_id
     * @param array $filter
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-Receivedinvoices-supplierId-ReceivedInvoices_PaymentStatus_DateOfReceiving_CurrencyId_DateLastChange_DateOfIssue_DateOfPayment_Exported_Filter_FilterExported_OrderBy_Query_Page_PageSize
     */
    public static function getReceivedInvoicesBySupplier($supplier_id, $filter = array()){
        self::checkToken();
        $invoices = self::curlData('Receivedinvoices/'.$supplier_id.'/ReceivedInvoices', $filter);
        return json_decode($invoices, true);
    }
    
    /**
     * 
     * @param int $id
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-Receivedinvoices-id-MyDocumentAddress
     */
    public static function receivedInvoiceMyDocumentAddress($id){
        self::checkToken();
        $invoices = self::curlData('receivedinvoices/'.$id.'/MyDocumentAddress');
        return json_decode($invoices, true);
    }
    
    /**
     * 
     * @param int $id
     * @return boolean
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/PUT-api-Receivedinvoices-id-FullyPay_dateOfPayment
     */
    public static function receivedInvoiceFullyPay($id, $filter = array()){
        self::checkToken();
        $invoices = self::curlData('Receivedinvoices/'.$id.'/FullyPay', $filter, 'PUT');
        return $invoices;
    }
    
    /**
     * 
     * @param int $id
     * @return boolean
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/PUT-api-Receivedinvoices-id-FullyUnpay
     */
    public static function receivedInvoiceFullyUnpay($id){
        self::checkToken();
        $invoices = self::curlData('Receivednvoices/'.$id.'/FullyUnpay', array(), 'PUT');
        return $invoices;
    }
    
    /**
     * 
     * @param array $params
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/POST-api-ReceivedInvoices
     */
    public static function newReceivedInvoice($params){
        self::checkToken();
        $template = self::curlData('ReceivedInvoices', array(), 'POST', $params);
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param int $id
     * @param array $params
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/PUT-api-ReceivedInvoices-id
     */
    public static function updateReceivedInvoice($id,$params){
        self::checkToken();
        $template = self::curlData('ReceivedInvoices/'.$id, array(), 'PUT', $params);
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param int $id
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/DELETE-api-ReceivedInvoices-id
     */
    public static function deleteReceivedInvoice($id){
        self::checkToken();
        self::curlData('ReceivedInvoices/'.$id, array(), 'DELETE', array());
    }
    
    /**
     * 
     * @param int $id
     * @param int $exported
     * @return boolean
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/PUT-api-Receivedinvoices-id-Exported-value
     */
    public static function updateReceivedInvoiceExported($id, $exported){
        self::checkToken();
        $template = self::curlData('ReceivedInvoices/'.$id.'/Exported/'.$exported, array(), 'PUT', array());
        return $template;
    }
    
    /* Pricelist */
    
    /**
     * 
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-PriceListItems-Default
     */
    public static function getPriceListTemplate(){
        self::checkToken();
        $template = self::curlData('PriceListItems/Default');
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param array $filter
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-PriceListItems_DateLastChange_Filter_Query_CurrencyId_Page_PageSize
     */
    public static function getPriceListItems($filter){
        self::checkToken();
        $template = self::curlData('PriceListItems', $filter);
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param array $filter
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-PriceListItems-Expand_DateLastChange_Filter_Query_CurrencyId_Page_PageSize
     */
    public static function getPriceListItemsExpand($filter){
        self::checkToken();
        $template = self::curlData('PriceListItems/Expand', $filter);
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param int $id
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-PriceListItems-id
     */
    public static function getPriceListItemDetail($id){
        self::checkToken();
        $template = self::curlData('PriceListItems/'.$id);
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param int $id
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-PriceListItems-id-Expand
     */
    public static function getPriceListItemDetailExpand($id){
        self::checkToken();
        $template = self::curlData('PriceListItems/'.$id.'/Expand');
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param array $params
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/POST-api-PriceListItems
     */
    public static function newPriceListItem($params){
        self::checkToken();
        $template = self::curlData('PriceListItems', array(), 'POST', $params);
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param int $id
     * @param array $params
     * @return array
     */
    public static function updatePriceListItem($id, $params){
        self::checkToken();
        $template = self::curlData('PriceListItems/'.$id, array(), 'PUT', $params);
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param int $id
     */
    public static function deletePriceListItem($id){
        self::checkToken();
        elf::curlData('PriceListItems/'.$id, array(), 'DELETE', array());
    }


    /* Banks */
    
    /**
     * 
     * @param array $filter
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-Banks_Page_PageSize
     */
    public static function getBanks($filter = array()){
        self::checkToken();
        $template = self::curlData('Banks', $filter);
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param int $id
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-Banks-id
     */
    public static function getBankDetail($id){
        self::checkToken();
        $template = self::curlData('Banks/'.$id);
        return json_decode($template, true);
    }

        /**
     * 
     * @param array $filter
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-Banks-GetChanges_Page_PageSize_lastCheck
     */
    public static function getBankChanges($filter = array()){
        self::checkToken();
        $template = self::curlData('Banks/GetChanges', $filter);
        return json_decode($template, true);
    }


    /* Constant Symbols */
    
    /**
     * 
     * @param array $filter
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-ConstantSymbols_Page_PageSize
     */
    public static function getConstantSymbols($filter = array()){
        self::checkToken();
        $template = self::curlData('ConstantSymbols', $filter);
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param int $id
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-ConstantSymbols-id
     */
    public static function getConstantSymbolDetail($id){
        self::checkToken();
        $template = self::curlData('ConstantSymbols/'.$id);
        return json_decode($template, true);
    }

        /**
     * 
     * @param array $filter
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-ConstantSymbols-GetChanges_Page_PageSize_lastCheck
     */
    public static function getConstanSymbolChanges($filter = array()){
        self::checkToken();
        $template = self::curlData('ConstantSymbols/GetChanges', $filter);
        return json_decode($template, true);
    }
    
    /* Countries */
    
    /**
     * 
     * @param array $filter
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-Countries_Page_PageSize
     */
    public static function getCountries($filter = array()){
        self::checkToken();
        $template = self::curlData('Countries', $filter);
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param int $id
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-Countries-id
     */
    public static function getCountryDetail($id){
        self::checkToken();
        $template = self::curlData('Countries/'.$id);
        return json_decode($template, true);
    }

        /**
     * 
     * @param array $filter
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-Countries-GetChanges_Page_PageSize_lastCheck
     */
    public static function getCountryChanges($filter = array()){
        self::checkToken();
        $template = self::curlData('Countries/GetChanges', $filter);
        return json_decode($template, true);
    }
    
    /* Currencies */
    
    /**
     * 
     * @param array $filter
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-Currencies_Page_PageSize
     */
    public static function getCurrencies($filter = array()){
        self::checkToken();
        $template = self::curlData('Currencies', $filter);
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param int $id
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-Currencies-id
     */
    public static function getCurrencyDetail($id){
        self::checkToken();
        $template = self::curlData('Currencies/'.$id);
        return json_decode($template, true);
    }

        /**
     * 
     * @param array $filter
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-Currencies-GetChanges_Page_PageSize_lastCheck
     */
    public static function getCurrencyChanges($filter = array()){
        self::checkToken();
        $template = self::curlData('Currencies/GetChanges', $filter);
        return json_decode($template, true);
    }
    
    /* Exchange rates */
    
    /**
     * 
     * @param array $filter
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-ExchangeRates_Legislation_CurrencyId_Date_Page_PageSize
     */
    public static function getExchangeRates($filter = array()){
        self::checkToken();
        $template = self::curlData('ExchangeRates', $filter);
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param int $id
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-ExchangeRates-id
     */
    public static function getExchangeRateDetail($id){
        self::checkToken();
        $template = self::curlData('ExchangeRates/'.$id);
        return json_decode($template, true);
    }

        /**
     * 
     * @param array $filter
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-ExchangeRates_Page_PageSize_lastCheck
     */
    public static function getExchangeRateChanges($filter = array()){
        self::checkToken();
        $template = self::curlData('ExchangeRates', $filter);
        return json_decode($template, true);
    }
    
    /* Payment options */
    
    /**
     * 
     * @param array $filter
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-PaymentOptions_Page_PageSize
     */
    public static function getPaymentOptions($filter = array()){
        self::checkToken();
        $template = self::curlData('PaymentOptions', $filter);
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param int $id
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-PaymentOptions-id
     */
    public static function getPaymentOptionDetail($id){
        self::checkToken();
        $template = self::curlData('PaymentOptions/'.$id);
        return json_decode($template, true);
    }

        /**
     * 
     * @param array $filter
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-PaymentOptions-GetChanges_Page_PageSize_lastCheck
     */
    public static function getPaymentOptionChanges($filter = array()){
        self::checkToken();
        $template = self::curlData('PaymentOptions/GetChanges', $filter);
        return json_decode($template, true);
    }
    
    /* Vat rates */
    
    /**
     * 
     * @param array $filter
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-VatRates_Page_PageSize
     */
    public static function getVatRates($filter = array()){
        self::checkToken();
        $template = self::curlData('VatRates', $filter);
        return json_decode($template, true);
    }
    
    /**
     * 
     * @param int $id
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-VatRates-id
     */
    public static function getVatRateDetail($id){
        self::checkToken();
        $template = self::curlData('VatRates/'.$id);
        return json_decode($template, true);
    }

    /**
     * 
     * @param array $filter
     * @return array
     * @url https://app.idoklad.cz/Developer/Help/cs/Api/GET-api-VatRates-GetChanges_Page_PageSize_lastCheck
     */
    public static function getVatRateChanges($filter = array()){
        self::checkToken();
        $template = self::curlData('VatRates/GetChanges', $filter);
        return json_decode($template, true);
    }
    
    /* Additional functions */
    
    public static function getInvoiceIdByVs($vs){
        $ret = self::getInvoices(array('query' => $vs));
        return $ret['Data'][0]['Id'];
    }
    
    public static function payInvoiceByVs($vs){
        $id = self::getInvoiceIdByVs($vs);
        return self::invoiceFullyPay($id);
    }
}
