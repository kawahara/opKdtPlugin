<?php

abstract class opKdtBaseTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace = 'opKdt';

    $this->addOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', true);
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);

    $conn = Doctrine_Manager::getInstance()->getCurrentConnection();
    $conn->beginTransaction();
    try
    {
      $this->executeTransaction($conn, $arguments, $options);
      $conn->commit();
    }
    catch (Exception $e)
    {
      $conn->rollBack();
      throw $e;
    }
  }

  abstract protected function executeTransaction($conn, $arguments = array(), $options = array());
}
