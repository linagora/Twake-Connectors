<?php
namespace BuiltInConnectors\Connectors\OnlyOffice;

class ConnectorDefinition
{

  public $configuration = [
    "domain" => "https://onlyoffice.apps.twakeapp.com",
    "jwt_secret" => ""
  ];

  public function __construct($app = null) {
    $server_route = "";
    if($app){
      $server_route = rtrim($app->getContainer()->getParameter("env.server_name"), "/") . "/bundle/connectors/onlyoffice";
    }

    $this->definition = [
      'app_group_name' => 'twake',
      'categories' => [],
      'name' => 'OnlyOffice',
      'simple_name' => 'onlyoffice',
      'description' => 'OnlyOffice allows you to edit and view spreadsheets, documents and slides in Twake.',
      'icon_url' => 'onlyoffice.png',
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
              'name' => 'OnlyOffice Word Document',
            ],
            [
              'url' => $server_route . '/empty?extension=xlsx',
              'filename' => 'Untitled.xlsx',
              'name' => 'OnlyOffice Excel Document',
            ],
            [
              'url' => $server_route . '/empty?extension=pptx',
              'filename' => 'Untitled.pptx',
              'name' => 'OnlyOffice PowerPoint Document',
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
