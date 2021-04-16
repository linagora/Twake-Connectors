<?php
namespace BuiltInConnectors\Connectors\Giphy;

class ConnectorDefinition
{
  public $configuration = [
    'domain' => 'https://api.giphy.com/v1/',
    'apikey' => '1234ABCD33L5lCRyYWn0RR4HdS'
  ];

  public $definition = [
      'app_group_name' => 'twake',
      'categories' => [],
      'name' => 'Giphy',
      'simple_name' => 'giphy',
      'description' => 'Giphy allows you to add gifs from the Giphy website to Twake messages.',
      'icon_url' => 'giphy.png',
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
          'right_icon' => true,
          'commands' =>
          [
            [
              'command' => '[gif_name]',
              'description' => 'Send a gif from Giphy',
            ]
          ]
        ]
      ],
      'api_allowed_ips' => '*',
      'api_event_url' => '/event'
  ];
}
