<?php

use Drupal\Tests\permissions_by_term\Unit\Base;
use Drupal\permissions_by_term\NodeAccess;
use \Drupal\permissions_by_term\Factory\NodeAccessRecordFactory;

/**
 * Class NodeAccess
 */
class NodeAccessTest extends PHPUnit_Framework_TestCase {

  use Base;

  public function testCreateRealms() {
    $accessStorage = $this->createMock('Drupal\permissions_by_term\AccessStorage',
      [
        'fetchUidsByRid' => [999, 87, 44],
        'getNidsByTid' => [64, 826, 91, 21],
        'getAllNids' => [12, 55, 88, 3, 5],
        'getAllUids' => [6, 84, 2, 99, 2],
        'getNodeType' => 'article',
        'getLangCode' => 'en'
      ]
    );
    $nodeAccessStorageFactory = new NodeAccessRecordFactory();

    $entityManager = $this->createMock('Drupal\Core\Entity\EntityManager',
      [
        'getStorage' => $this->createMock('Storage', [
          'load' => $this->createMock('Entity', [
            'hasPermission' => true
        ]),
      ])]
    );

    $accessCheck = $this->createMock('Drupal\permissions_by_term\AccessCheck',
      [
        'canUserAccessByNodeId' => TRUE
      ]
    );

    $database = $this->createMock('Drupal\Core\Database\Driver\mysql\Connection');

    $nodeAccess = new NodeAccess($accessStorage, $nodeAccessStorageFactory, $entityManager, $accessCheck, $database);

    $this->assertTrue($this->propertiesHaveValues($nodeAccess->createGrants(1)));
    $this->assertTrue($this->realmContainsNumber($nodeAccess->createGrants(1)));
  }

  private function realmContainsNumber($objectStack) {
    foreach ($objectStack as $object) {
      foreach ($object as $propertyName => $propertyValue) {
        if ($propertyName == 'realm') {
          if ($this->stringContainsOneNumbers($propertyValue) === FALSE) {
            throw new \Exception('The realm does not contain two numbers. It must contain the UID and TID.');
          }
        }
      }
    }

    return TRUE;
  }

  private function stringContainsOneNumbers($string) {
    $numOfNumbers = 0;
    $elements = explode('_', $string);
    foreach ($elements as $element) {
      if (is_numeric($element)) {
        $numOfNumbers++;
      }
    }

    if ($numOfNumbers == 1) {
      return TRUE;
    }
    
    return FALSE;
  }

  private function propertiesHaveValues($objectStack) {
    foreach ($objectStack as $object) {
      foreach ($object as $propertyName => $propertyValue) {
        if ($propertyValue == '' && $propertyValue != 0) {
          throw new \Exception('Property with name ' . $propertyName . ' does not contain any value.');
          return FALSE;
        }
      }
    }

    return TRUE;
  }

}
