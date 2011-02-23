<?php

class opKdtJoinCommunityTask extends opKdtBaseTask
{
  protected function configure()
  {
    parent::configure();

    $this->name      = 'join-community';

    require sfConfig::get('sf_data_dir').'/version.php';

    $this->addOptions(
      array(
        new sfCommandOption('number', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of joined communities', 10),
      )
    );
  }

  protected function executeTransaction($conn, $arguments = array(), $options = array())
  {
    $members = Doctrine::getTable('Member')->findAll(Doctrine::HYDRATE_ARRAY);
    $communities = Doctrine::getTable('Community')->findAll(Doctrine::HYDRATE_ARRAY);
    if (count($communities) < $options['number'])
    {
      throw new Exception('Too few communities. Please run "opKdt:generate-community" first.');
    }
    $communityIds = array_map(create_function('$c', 'return (int)$c[\'id\'];'), $communities);

    foreach ($members as $member)
    {
      $joinCommunities = Doctrine::getTable('Community')->retrievesByMemberId($member['id'], null);
      $joinCommunityIds = array();
      if ($joinCommunities)
      {
        foreach ($joinCommunities as $c)
        {
          $joinCommunityIds[] = $c->getId();
        }
      }

      $candidate = array_diff($communityIds, $joinCommunityIds);
      shuffle($candidate);
      $candidateSlices = array_slice($candidate, 0, $options['number']);

      foreach ($candidateSlices as $communityId)
      {
        $cm = new CommunityMember();
        $cm->setCommunityId($communityId);
        $cm->setMemberId($member['id']);
        $cm->save();
        $cm->free();
        $this->logSection('added a community member', sprintf("%s - %s", $member['id'], $communityId));
      }
    }
  }
}
