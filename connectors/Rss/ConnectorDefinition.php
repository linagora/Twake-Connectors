<?php
namespace BuiltInConnectors\Connectors\Rss;

class ConnectorDefinition
{

  public $configuration = [
  ];

  public function __construct($app = null) {

    $this->definition = [
      'app_group_name' => 'twake',
      'categories' => [],
      'name' => 'RSS',
      'simple_name' => 'rss',
      'description' => 'RSS allow your channel to receive RSS feed.',
      'icon_url' => 'rss.png',
      'website' => 'https://twake.app',
      'privileges' => [],
      'capabilities' =>
      [
        'messages_send',
        'display_modal',
        'messages_save',
      ],
      'hooks' => [],
      'display' =>
      [
        "channel" => [
          "can_connect_to_channel" => true
        ],
        "configuration" => [
          "can_configure_in_channel" => true
        ]
      ],
      'api_allowed_ips' => '*',
      'api_event_url' => '/event'
    ];
  }

  public $definition = [];
}
