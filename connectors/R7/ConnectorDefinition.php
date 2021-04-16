<?php
namespace BuiltInConnectors\Connectors\R7;

class ConnectorDefinition
{

  public $configuration = [
    "domain" => "https://onlyoffice.apps.twakeapp.com",
    "apipubkey_slide" => "",
    "apipubkey_spreadsheet" => "",
    "apipubkey_text" => "",
  ];

  public function __construct($app = null) {
    $server_route = "";
    if($app){
      $server_route = rtrim($app->getContainer()->getParameter("env.server_name"), "/") . "/r7_office";
    }

    $this->definition = [
      'app_group_name' => 'twake',
      'categories' => [],
      'name' => 'R7',
      'simple_name' => 'r7_office',
      'description' => 'R7 allows you to edit and view spreadsheets, documents and slides in Twake.',
      'icon_url' => 'R7.png',
      'website' => 'https://twake.app',
      'privileges' => [
        'workspace_drive'
      ],
      'capabilities' =>
      [
        'drive_save',
        'display_modal'
      ],
      'hooks' => [],
      'display' => [
        'drive_module' => [
          'can_open_files' => [
            'url' => $server_route . '/load',
            'preview_url' => $server_route . '/load?preview=1',
            'main_ext' => [
              'xlsx',
              'pptx',
              'docx',
              'xls',
              'ppt',
              'doc',
              'odt',
              'ods',
              'odp',
            ],
            'other_ext' => [
              'txt',
              'html',
              'csv',
            ],
          ],
          'can_create_files' => [
            [
              'url' => $server_route . '/empty?extension=docx',
              'filename' => 'Untitled.docx',
              'name' => 'R7 Word Document',
            ],
            [
              'url' => $server_route . '/empty?extension=xlsx',
              'filename' => 'Untitled.xlsx',
              'name' => 'R7 Excel Document',
            ],
            [
              'url' => $server_route . '/empty?extension=pptx',
              'filename' => 'Untitled.pptx',
              'name' => 'R7 PowerPoint Document',
            ],
          ],
        ],
      ],
      'api_allowed_ips' => '*',
      'api_event_url' => '/event'
    ];
  }

  public $definition = [];
}
