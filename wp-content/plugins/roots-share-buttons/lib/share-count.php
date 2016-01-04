<?php

namespace Roots\ShareButtons\ShareCount;

/**
 * Get share counts from social networks
 */
class shareCount {
  private $url, $timeout;

  private static $services = array(
    'twitter'   => 'http://urls.api.twitter.com/1/urls/count.json?url=',
    'linkedin'  => 'http://www.linkedin.com/countserv/count/share?format=json&url=',
    'pinterest' => 'http://api.pinterest.com/v1/urls/count.json?url=',
    'facebook'  => 'http://graph.facebook.com/'
  );

  function __construct($url, $timeout = 15) {
    $this->url = rawurlencode($url);
    $this->timeout = $timeout * 60; // Multiply to minutes
    $this->ID = url_to_postid($url);
  }

  private function service_request($service) {
    $response = get_transient(sprintf('rsb_%s_%d', $service, $this->ID));

    if ($response) {
      return $response;
    } elseif ($service === 'plusones') {
      $response = $this->request_plusones();
    } else {
      $response = wp_remote_get(self::$services[$service] . $this->url);
    }

    if (is_wp_error($response)) {
      return 0;
    } else {
      if ($service === 'pinterest') {
        $start  = strpos($response['body'], '{');
        $length = strrpos($response['body'], '}') - $start + 1;
        $json   = substr($response['body'], $start, $length);
        $json   = json_decode($json);
        set_transient(sprintf('rsb_%s_%d', $service, $this->ID), $json, $this->timeout);
        if (isset($json->count)) {
          return (int)$json->count;
        } else {
          return 0;
        }
      }
      $json = json_decode($response['body'], true);
      set_transient(sprintf('rsb_%s_%d', $service, $this->ID), $json, $this->timeout);
      return $json;
    }
  }

  function get_tweets() {
    $json = $this->service_request('twitter');
    return isset($json['count']) ? intval($json['count']) : 0;
  }

  function get_linkedin() {
    $json = $this->service_request('linkedin');
    return isset($json['count']) ? intval($json['count']) : 0;
  }

  function get_fb() {
    $json = $this->service_request('facebook');
    return isset($json['shares']) ? intval($json['shares']) : 0;
  }

  function get_pinterest() {
    $json = $this->service_request('pinterest');
    return isset($json->count) ? intval($json->count) : 0;
  }

  function get_plusones() {
    $json = $this->service_request('plusones');
    return isset($json['result']['metadata']['globalCounts']['count']) ? intval($json['result']['metadata']['globalCounts']['count']) : 0;
  }

  function request_plusones() {
    $args = array(
      'method'    => 'POST',
      'headers'   => array(
        'Content-Type' => 'application/json'
      ),
      'body'      => json_encode(array(
        'method'     => 'pos.plusones.get',
        'id'         => 'p',
        'jsonrpc'    => '2.0',
        'key'        => 'p',
        'apiVersion' => 'v1',
        'params' => array(
          'nolog'   => true,
          'id'      => rawurldecode($this->url),
          'source'  => 'widget',
          'userId'  => '@viewer',
          'groupId' => '@self'
        )
      )),
      'sslverify' => false
    );

    return wp_remote_post('https://clients6.google.com/rpc', $args);
  }
}
