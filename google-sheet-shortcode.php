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
            'cols_count' => '',
            'table_id' => '',
            'table_class' => '',
            'allow_editing' => false,
            'edit_btn_class_name' => 'google-sheets-row-edit-btn',
        ],
          $atts,
          $tag
        );
        
        $spreadsheetId = $wporg_atts['sheetid'];
        $range = $wporg_atts['content_range'];

        if($spreadsheetId === '') return 'Spreadsheet ID missing';

        $values = $googleSheets->get_spreadsheet_values($spreadsheetId, $range);
        
        if(!$values) return "Could not find the spreadsheet";

        $wporg_atts['cols_count'] = sizeof($values[0]);
        
        $html = "<table id='".$wporg_atts['table_id']."' class='".$wporg_atts['table_class']."'>
                  <thead>
                    <tr>
                      ".$googleSheets->get_single_row_html($values[0], true)."
                    </tr>
                  </thead>
                  <tbody>
                  ".$googleSheets->get_multple_row_html(array_slice($values, 1), $wporg_atts['email_row_index'], $wporg_atts['cols_count'], 0)."
                  </tbody>
                  </table>
        ";
        
        return $html;
    }
    add_shortcode('google_sheets', 'display_google_sheets_content');
}
add_action('init', 'google_sheets_shortcode');