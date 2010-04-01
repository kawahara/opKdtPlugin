<?php

class opKdtMakeFriendTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace = 'opKdt';
    $this->name      = 'make-friend';

    require sfConfig::get('sf_data_dir').'/version.php';

    $this->addOptions(
      array(
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', null),
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
        new sfCommandOption('number', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of added friends', 10),
      )
    );
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);

    $members = Doctrine::getTable('Member')->findAll(Doctrine::HYDRATE_ARRAY);
    if (count($members) < $options['number'])
    {
      throw new Exception('Too few members. Please run "opKdt:generate-member" first.');
    }
    $memberIds = array_map(create_function('$m', 'return (int)$m[\'id\'];'), $members);

    foreach ($memberIds as $id)
    {
      $friendIds = array_map(create_function('$id', 'return (int)$id;'), Doctrine::getTable('MemberRelationship')->getFriendMemberIds($id));
      $friendIds[] = (int)$id;

      $candidate = array_diff($memberIds, $friendIds);
      shuffle($candidate);
      $candidateSlices = array_slice($candidate, 0, $options['number']);
      foreach ($candidateSlices as $memberIdTo)
      {
        $mr1 = new MemberRelationship();
        $mr1->setMemberIdFrom($id);
        $mr1->setMemberIdTo($memberIdTo);
        $mr1->setIsFriend(true);
        $mr1->save();
        $mr1->free();
        $mr2= new MemberRelationship();
        $mr2->setMemberIdFrom($memberIdTo);
        $mr2->setMemberIdTo($id);
        $mr2->setIsFriend(true);
        $mr2->save();
        $mr2->free();
        $this->logSection('make friends', sprintf("%s - %s", $id, $memberIdTo));
      }
    }
  }
}
