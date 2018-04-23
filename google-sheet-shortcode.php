<?php 
// require_once('../google-api-php-client-2.2.1/vendor/autoload.php');
function google_sheets_shortcode()
{
    function display_google_sheets_content($atts = [], $content = null, $tag = '')
    {
      
        $googleSheets = new GoogleSheets();

        // normalize attribute keys, lowercase
        $atts = array_change_key_case((array)$atts, CASE_LOWER);
        wp_reset_query();
        // override default attributes with user attributes
        $wporg_atts = shortcode_atts(
          [
            'sheetid' => '',
            'content_range' => 'Sheet1',
            'email_row_index' => 'null',
            'table_id' => '',
            'table_class' => '',
            'offset' => 0,
            'value-render-option' => 'FORMATTED_VALUE',
            'date-time-render-option' => 'FORMATTED_STRING',
        ],
          $atts,
          $tag
        );
        
        $spreadsheetId = $wporg_atts['sheetid'];
        $range = $wporg_atts['content_range'];

        if($spreadsheetId === '') return 'Spreadsheet ID missing';

        $optParams = [];
        $optParams['valueRenderOption'] = $wporg_atts['value-render-option'];
        $optParams['dateTimeRenderOption'] = $wporg_atts['date-time-render-option'];

        $values = $googleSheets->get_spreadsheet_values($spreadsheetId, $range, $optParams);
        $values = array_slice($values, $wporg_atts['offset']);
        
        $columnsCount = sizeof($values[0]);
        
        if(!$values) return "Could not find the spreadsheet";

        $wporg_atts['cols_count'] = sizeof($values[0]);
        
        $html = "<table id='".$wporg_atts['table_id']."' class='".$wporg_atts['table_class']."'>
                  <thead>
                    <tr>
                      ".$googleSheets->get_single_row_html($values[0], true)."
                    </tr>
                  </thead>
                  <tbody>
                  ".$googleSheets->get_multple_row_html(array_slice($values, 1), $wporg_atts['email_row_index'], $columnsCount, 0)."
                  </tbody>
                  </table>
        ";
        
        return $html;
    }

    function display_google_sheets_complete($atts = [], $content = null, $tag = ''){

       // normalize attribute keys, lowercase
       $atts = array_change_key_case((array)$atts, CASE_LOWER);
       wp_reset_query();
       // override default attributes with user attributes
       $wporg_atts = shortcode_atts(
         [
           'sheetid' => '',
           'content_range' => 'Sheet1',
           'email_row_index' => 'null',
           'table_id' => '',
           'table_class' => '',
           'offset' => 0,
           'value-render-option' => 'FORMATTED_VALUE',
           'date-time-render-option' => 'FORMATTED_STRING',
       ],
         $atts,
         $tag
       );
      
      if($spreadsheetId === '') return 'Spreadsheet ID missing';

      $googleSheets = new GoogleSheets();
      // return 
      $html = '';
      $sheets = $googleSheets->get_all_sheets($wporg_atts['sheetid']);

      $html .= '<ul>';
      foreach ($sheets as $key => $sheet) {
          $title = $sheet->properties['title'];
          $sheetid = $sheet->properties['sheetId'];
          $html .= '<li>
            <a href="?sheet_name='.$title.'">'.$title.'
            </a>
            </li>';
      }
      $html .= '</ul>';

      $html .= "<h3>{$_GET['sheet_name']}</h3>";

      $sheetname = isset($_GET['sheet_name']) ? $_GET['sheet_name'] : $sheets[0]->properties['title'];
      $wporg_atts['content_range'] = $sheetname;
      $html .= display_google_sheets_content($wporg_atts);
      
      return $html;
    }

    add_shortcode('google_sheets', 'display_google_sheets_content');
    add_shortcode('google_sheets_complete', 'display_google_sheets_complete');
}
add_action('init', 'google_sheets_shortcode');

