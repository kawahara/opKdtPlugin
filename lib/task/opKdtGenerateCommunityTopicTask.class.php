<?php

class opKdtGenerateCommunityTopicTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace = 'opKdt';
    $this->name      = 'generate-community-topic';

    require sfConfig::get('sf_data_dir').'/version.php';

    $this->addOptions(
      array(
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', null),
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
        new sfCommandOption('number', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of community topics', 10),
      )
    );
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);

    $communities = Doctrine::getTable('Community')->findAll(Doctrine::HYDRATE_ARRAY);
    foreach ($communities as $community)
    {
      for ($i=0; $i < $options['number']; ++$i)
      {
        $ct = new CommunityTopic();
        $ct->setCommunityId($community['id']);
        $ct->setMemberId(self::fetchRandomMemberId($community['id']));
        $ct->setName('name');
        $ct->setBody('body');
        $ct->save();
        $ct->free();
        $this->logSection('created a community topic', sprintf("%s", $community['id']));
      }
    }
  }

  protected static function fetchRandomMemberId($communityId)
  {
    $communityMembers = Doctrine::getTable('CommunityMember')->getCommunityMembers($communityId);

    if (!$communityMembers)
    {
      return;
    }

    $communityMemberIds = array();
    foreach ($communityMembers as $m)
    {
      $communityMemberIds[] = $m->getMemberId();
    }
    shuffle($communityMemberIds);

    return array_pop($communityMemberIds);
  }
}
