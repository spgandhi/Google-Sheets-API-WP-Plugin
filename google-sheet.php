<?php

/*
Plugin Name: Google Sheets API
*/

require_once('google-api-php-client-2.2.1/vendor/autoload.php');
require_once(plugin_dir_path(__FILE__) . '/google-sheet-shortcode.php');

$GoogleSheetsAPIConfig = include('config.php');


class GoogleSheets
{
    public $service;

    public function __construct()
    {
        global $GoogleSheetsAPIConfig;
        putenv("GOOGLE_APPLICATION_CREDENTIALS={$GoogleSheetsAPIConfig['CREDENTIALS_FILE_PATH']}");
        define('APPLICATION_NAME', 'Google Sheets API');
        define('CREDENTIALS_PATH', '~/IEEE Sensors-13a1de052093.json.json');
        define('CLIENT_SECRET_PATH', __DIR__ . '/IEEE Sensors-13a1de052093.json');
        define('SCOPES', implode(' ', array(Google_Service_Sheets::SPREADSHEETS)));
        $client = new Google_Client();
        $client->setApplicationName(APPLICATION_NAME);
        $client->setScopes(SCOPES);
        $client->useApplicationDefaultCredentials();
        $client->setAccessType('offline');
        $this->service = $service = new Google_Service_Sheets($client);
    }

    public function get_spreadsheet_values($spreadsheetId, $range)
    { 
      try{
        $response = $this->service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();
        return $values;
      } catch (Exception $e){
        return false;
      }
    }

    public function addRowToSpreadsheet($spreadsheetId, $sheetId, $newValues = [])
    {
        // Build the CellData array
        $values = [];
        foreach ($newValues as $d) {
            $cellData = new Google_Service_Sheets_CellData();
            $value = new Google_Service_Sheets_ExtendedValue();
            $value->setStringValue($d);
            $cellData->setUserEnteredValue($value);
            $values[] = $cellData;
        }
        // Build the RowData
        $rowData = new Google_Service_Sheets_RowData();
        $rowData->setValues($values);
        // Prepare the request
        $append_request = new Google_Service_Sheets_AppendCellsRequest();
        $append_request->setSheetId($sheetId);
        $append_request->setRows($rowData);
        $append_request->setFields('userEnteredValue');
        // Set the request
        $request = new Google_Service_Sheets_Request();
        $request->setAppendCells($append_request);
        // Add the request to the requests array
        $requests = array();
        $requests[] = $request;
        // Prepare the update
        $batchUpdateRequest = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(array(
              'requests' => $requests
          ));
      
        try {
            // Execute the request
            $response = $this->service->spreadsheets->batchUpdate($spreadsheetId, $batchUpdateRequest);
            if ($response->valid()) {
                return true;// Success, the row has been added
            }
        } catch (Exception $e) {
            error_log($e->getMessage());// Something went wrong
            return $e->getMessage();
        }
            
        return false;
    }

    public function get_single_row_html($row, $is_header)
    {
        $rowHtml = '';
        foreach ($row as $key => $value) {
            $tag = $is_header ? 'th' : 'td';
            $rowHtml .= "<{$tag}>{$value}</{$tag}>";
        }
        return $rowHtml;
    }

    public function get_multple_row_html($rows, $emailRowIndex, $cols_count, $allowEditing = false, $editingBtnClassName='', $index=0)
    {
        $rowsHtml = '';
        foreach ($rows as $key => $person) {
            $rowsHtml .= "<tr data-index='".$index++."'>";
            foreach ($person as $key => $value) {
                if ($key === $emailRowIndex) {
                    $rowsHtml .= '<td>'.do_shortcode('[uffc_email email="'.$value.'"]').'</td>';
                } else {
                    $rowsHtml .= '<td>'.$value.'</td>';
                }
            }
        
            // If the trailing cells for a row in the googlesheets are empty,
            // then add empty table cells
            if ($key === sizeof($person) - 1 && $cols_count && $cols_count > sizeof($person)) {
                for ($i = $key; $i<$cols_count-1; $i++) {
                    $rowsHtml .= '<td></td>';
                }
            }
            if ($allowEditing) {
                $rowsHtml .= "<td><a class='{$editingBtnClassName}'>Edit</a></td>";
            }
            $rowsHtml .= '</tr>';
        }
        
        return $rowsHtml;
    }

    public function get_all_sheets($sheetId)
    {
        $response = $this->service->spreadsheets->get($sheetId);
        $sheets = $response->getSheets();
        return $sheets;
    }

    public function updateRowToSpreadSheet($spreadsheetId, $range, $data)
    {
        $values = array($data);
        $body = new Google_Service_Sheets_ValueRange(array(
          'values' => $values
        ));
        $params = array(
          'valueInputOption' => 'USER_ENTERED'
        );

        $result = $this->service->spreadsheets_values->update($spreadsheetId, $range, $body, $params);
        return $result;
    }
}
