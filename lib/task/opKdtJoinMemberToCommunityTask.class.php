<?php

class opKdtJoinMemberToCommunityTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace = 'opKdt';
    $this->name      = 'join-member-to-community';

    require sfConfig::get('sf_data_dir').'/version.php';

    $this->addOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', true);
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('community', 'c', sfCommandOption::PARAMETER_REQUIRED, "Community Id", 1);
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);

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
