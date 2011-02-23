<?php

class opKdtJoinMemberToCommunityTask extends opKdtBaseTask
{
  protected function configure()
  {
    parent::configure();

    $this->name      = 'join-member-to-community';

    require sfConfig::get('sf_data_dir').'/version.php';

    $this->addOption('community', 'c', sfCommandOption::PARAMETER_REQUIRED, "Community Id", 1);
  }

  protected function executeTransaction($conn, $arguments = array(), $options = array())
  {
    $c = (int)$options['community'];
    $community = Doctrine::getTable('Community')->find($c);
    if (!$community)
    {
      throw new Exception();
    }
    $members = Doctrine::getTable('Member')->findAll();
    foreach ($members as $m)
    {
      $o = Doctrine::getTable('CommunityMember')->retrieveByMemberIdAndCommunityId($m->id, $community->id);
      if (!$o)
      {
        Doctrine::getTable('CommunityMember')->join($m->id, $community->id);
        $this->logSection('join community+', $m->getName().' to '.$community->getName());
      }
    }
  }
}
