<?php
namespace BuiltInConnectors\Connectors\SimplePoll;

class ConnectorDefinition
{
  public $configuration = [];

  public $definition = [
      'app_group_name' => 'twake',
      'categories' => [],
      'name' => 'SimplePoll',
      'simple_name' => 'simplepoll',
      'description' => 'SimplePoll allows you to create polls in Twake messages.',
      'icon_url' => 'simplepoll.png',
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
        'messages_module' =>
        [
          'commands' =>
          [
            [
              'command' => '"What do you want to eat?" "Pizza" "Pasta" "Tacos"',
              'description' => 'Create a new poll',
            ]
          ]
        ]
      ],
      'api_allowed_ips' => '*',
      'api_event_url' => '/event'
  ];
}
