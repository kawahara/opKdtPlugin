<?php

class opKdtGenerateMemberTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace = 'opKdt';
    $this->name      = 'generate-member';

    $this->addOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', true);
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('link', 'l', sfCommandOption::PARAMETER_REQUIRED, 'Who links?', null);
    $this->addOption('name-format', null, sfCommandOption::PARAMETER_REQUIRED, "Member's Name format", 'dummy%d');
    $this->addOption('number', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of added members', 10);
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);

    $n = (int)$options['number'];
    $link = $options['link'];
    if (null !== $link)
    {
      $linkMember = Doctrine::getTable('Member')->find($link);
      if (!$linkMember)
      {
        throw new Exception("not found member: ".$link);
      }
    }

    for ($i = 0; $i < $n; $i++)
    {
      $member = new Member();
      $member->setName('dummy');
      $member->setIsActive(true);
      $member->save();

      $member->setName(sprintf($options['name-format'], $member->getId()));
      $member->save();
      $member->setConfig('pc_address', 'sns'.$member->getId().'@example.com');
      $member->setConfig('mobile_address', 'sns'.$member->getId().'@example.com');
      $member->setConfig('password', md5('password'));
      $this->logSection('member+', $member->getName());
      if (isset($linkMember))
      {
        $memberRelationship1 = new MemberRelationship();
        $memberRelationship1->setMember($member);
        $memberRelationship1->setMemberRelatedByMemberIdFrom($linkMember);
        $memberRelationship1->setIsFriend(true);

        $memberRelationship2 = new MemberRelationship();
        $memberRelationship2->setMember($linkMember);
        $memberRelationship2->setMemberRelatedByMemberIdFrom($member);
        $memberRelationship2->setIsFriend(true);
        $this->logSection('friend link', sprintf("%s - %s", $linkMember->getId(), $member->getId()));
      }
    }
  }
}
