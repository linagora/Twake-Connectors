<?php
namespace BuiltInConnectors\Connectors\R7;

class ConnectorDefinition
{
  public $configuration = [
    //TODO add configuration needed for this connector
  ];

  public $definition = [
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
            'url' => 'https://connectors.api.twake.app/only_office/load',
            'preview_url' => 'https://connectors.api.twake.app/only_office/load?preview=1',
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
              'url' => 'https://connectors.api.twake.app/public/onlyoffice/empty.docx',
              'filename' => 'Untitled.docx',
              'name' => 'ONLYOFFICE Word Document',
            ],
            [
              'url' => 'https://connectors.api.twake.app/public/onlyoffice/empty.xlsx',
              'filename' => 'Untitled.xlsx',
              'name' => 'ONLYOFFICE Excel Document',
            ],
            [
              'url' => 'https://connectors.api.twake.app/public/onlyoffice/empty.pptx',
              'filename' => 'Untitled.pptx',
              'name' => 'ONLYOFFICE PowerPoint Document',
            ],
          ],
        ],
      ],
      'api_allowed_ips' => '*',
      'api_event_url' => '/event'
  ];
}
