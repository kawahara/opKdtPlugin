<?php

class opKdtPutFootprintTask extends opKdtBaseTask
{
  protected function configure()
  {
    parent::configure();

    $this->name      = 'put-footprint';

    require sfConfig::get('sf_data_dir').'/version.php';

    $this->addOptions(
      array(
        new sfCommandOption('number', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of footprints', 10),
      )
    );
  }

  protected function executeTransaction($conn, $arguments = array(), $options = array())
  {
    $members = Doctrine::getTable('Member')->findAll(Doctrine::HYDRATE_ARRAY);
    if (count($members) < 2)
    {
      throw new Exception('Too few members. Please run "opKdt:generate-member" first.');
    }
    $memberIds = array_map(create_function('$m', 'return (int)$m[\'id\'];'), $members);

    foreach ($memberIds as $memberId)
    {
      $candidate = array_diff($memberIds, array($memberId));
      shuffle($candidate);
      $footprintMembers = array_slice($candidate, 0, $options['number']);
      foreach ($footprintMembers as $memberIdTo)
      {
        $ashi = new Ashiato();
        $ashi->setMemberIdFrom($memberId);
        $ashi->setMemberIdTo($memberIdTo);
        $ashi->save();
        $ashi->free();
        $this->logSection('put a footprint', sprintf("%s - %s", $memberId, $memberIdTo));
      }
    }
  }
}
