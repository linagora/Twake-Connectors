<?php
namespace BuiltInConnectors\Connectors\IncomingWebhooks;

class ConnectorDefinition
{
  public $configuration = [
  ];

  public $definition = [
      'app_group_name' => 'twake',
      'categories' => [ ],
      'name' => 'IncomingWebhooks',
      'simple_name' => 'incoming_webhooks',
      'description' => 'Incoming Webhooks allows you to easily send a message on a Twake channel from a third party service via a single link per channel.',
      'icon_url' => 'webhooks.svg',
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
