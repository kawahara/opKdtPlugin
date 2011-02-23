<?php

class opKdtGenerateCommunityTopicTask extends opKdtBaseTask
{
  protected function configure()
  {
    parent::configure();

    $this->name      = 'generate-community-topic';

    require sfConfig::get('sf_data_dir').'/version.php';

    $this->addOptions(
      array(
        new sfCommandOption('number', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of community topics', 10),
      )
    );
  }

  protected function executeTransaction($conn, $arguments = array(), $options = array())
  {
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
