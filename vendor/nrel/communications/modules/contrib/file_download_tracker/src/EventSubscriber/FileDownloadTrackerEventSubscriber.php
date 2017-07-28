<?php
/**
 * @file
 * Contains \Drupal\file_download_tracker\FileDownloadTrackerEventSubscriber.
 */
namespace Drupal\file_download_tracker\EventSubscriber;

use Drupal\file_download_tracker\FileDownloadTracker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\file_download_tracker\Entity\FileDownloadEntity;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class FileDownloadTrackerEventSubscriber.
 *
 * @package Drupal\file_download_tracker
 */
class FileDownloadTrackerEventSubscriber implements EventSubscriberInterface {
  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[FileDownloadTracker::SUBMIT][] = array('tracking', 800);
    return $events;
  }

  /**
   * Subscriber Callback for the event.
   * @param FileDownloadTracker $event
   */
  public function tracking(FileDownloadTracker $event) {
    $FileID = $event->getFileID();
    // To get the node id from last URL.
    $ref = $_SERVER['HTTP_REFERER'];
    // Get the URL without domain name.
    $req = Request::create($ref)->getRequestUri();
    // Get the node id from path alias.
    $path = \Drupal::service('path.alias_manager')->getPathByAlias($req);
    if(preg_match('/node\/(\d+)/', $path, $matches)) {
      $eid = $matches[1];
    }
    // To get the ip address of the system.
    $ip_address = \Drupal::request()->getClientIp();
    // Get the current user id
    $user_id = \Drupal::currentUser()->id();
    // To save entity for File.
    foreach ($FileID as $fid) {
      $file_download_entity_file = FileDownloadEntity::create([
        'entity_type' => 'file',
        'entity_id' => $fid,
        'ip_address' => $ip_address,
        'user_id' => $user_id,
      ]);
      $file_download_entity_file->save();
    }
    // To save entity for Page.
    $file_download_entity_page = FileDownloadEntity::create([
      'entity_type' => 'page',
      'entity_id' => $eid,
      'ip_address' => $ip_address,
      'user_id' => $user_id,
    ]);
    $file_download_entity_page->save();
  }
}

