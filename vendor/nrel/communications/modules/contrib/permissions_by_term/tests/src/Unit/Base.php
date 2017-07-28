<?php

namespace Drupal\Tests\permissions_by_term\Unit;

use \Symfony\Component\DependencyInjection\ContainerInterface;

trait Base
{

  /**
   * @param string $namespace
   * @param array $methodsReturnMap
   *
   * @return mixed
   */
  public function createMock($namespace, array $methodsReturnMap = [])
  {
    $methodNames = array_keys($methodsReturnMap);
    $mock = $this
      ->getMockBuilder($namespace);

    $mock->disableOriginalConstructor();

    $mockedClass = $mock
      ->setMethods($methodNames)
      ->getMock();

    foreach ($methodsReturnMap as $methodName => $methodReturn) {
      $mockedClass
        ->method($methodName)
        ->will(
          $this->returnValue($methodReturn)
        );
    }

    return $mockedClass;
  }

  /**
   * @param string $class
   * @param string $propertyName
   * @param mixed $value
   */
  public function modifyPropertyByReflection($class, $propertyName, $value)
  {
    $reflection = $this->makePropertyAccessible($class, $propertyName);
    $reflection->setValue($class, $value);
  }

  /**
   * @param string $class
   * @param string $propertyName
   * @return \ReflectionProperty
   */
  public function makePropertyAccessible($class, $propertyName)
  {
    $reflection = new \ReflectionProperty(get_class($class), $propertyName);
    $reflection->setAccessible(true);

    return $reflection;
  }

  /**
   * @param null|array $methodReturnValues
   * @param null|array $methodReturnValuesMap
   *
   * @return mixed
   */
  public function getContainerMock($methodReturnValues = null, $methodReturnValuesMap = null)
  {
    if (!is_array($methodReturnValues)) {
      $methodReturnValues = array();
    }

    if (!is_array($methodReturnValuesMap)) {
      $methodReturnValuesMap = array();
    }

    $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->setMethods(array_merge(array_keys($methodReturnValues), array_keys($methodReturnValuesMap)))->getMock();

    foreach ($methodReturnValues as $methodName => $returnValue) {
      $container->method($methodName)->will($this->returnValue($returnValue));
    }

    foreach ($methodReturnValuesMap as $methodName => $returnValueMap) {
      $container->method($methodName)->will($this->returnValueMap($returnValueMap));
    }

    return $container;
  }

  /**
   * @param string $string
   * @return bool
   */
  public function containsHtml($string)
  {
    if ($string != strip_tags($string)) {
      return true;
    }

    return false;
  }

  protected function collectServices($defaultServiceContainers, $testSpecificServices)
  {
    $serviceContainers = $defaultServiceContainers;

    if (!empty($testSpecificServices)) {
      $testSpecificServiceKeys = $this->extractTestSpecificContainerKeys($testSpecificServices);
      $uniqueDefaultServiceContainers = $this->collectUndefinedServicesByDefaultStack($defaultServiceContainers, $testSpecificServiceKeys);
      $serviceContainers = array_merge($uniqueDefaultServiceContainers, $testSpecificServices);
    }

    return $serviceContainers;
  }

  protected function extractTestSpecificContainerKeys($testSpecificServices)
  {
    foreach ($testSpecificServices as $testSpecificService) {
      $testSpecificServiceKeys[] = $testSpecificService['0'];
    }

    return $testSpecificServiceKeys;
  }

  protected function collectUndefinedServicesByDefaultStack($defaultServiceContainers, $testSpecificServiceKeys)
  {
    foreach ($defaultServiceContainers as $serviceContainer) {
      if (!in_array($serviceContainer['0'], $testSpecificServiceKeys)) {
        $uniqueServiceContainers[] = $serviceContainer;
      }
    }

    return $uniqueServiceContainers;
  }

}