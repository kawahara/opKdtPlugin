<?php

class opKdtGenerateMemberTask extends opKdtBaseTask
{
  protected function configure()
  {
    parent::configure();

    $this->name      = 'generate-member';

    $this->addOption('link', 'l', sfCommandOption::PARAMETER_REQUIRED, 'Who links?', null);
    $this->addOption('name-format', null, sfCommandOption::PARAMETER_REQUIRED, "Member's Name format", 'dummy%d');
    $this->addOption('number', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of added members', 10);
    $this->addOption('mail-address-format', null, sfCommandOption::PARAMETER_REQUIRED, 'Mail-Address format', 'sns%d@example.com');
    $this->addOption('password-format', null, sfCommandOption::PARAMETER_REQUIRED, 'Password format', 'password');
  }

  protected function executeTransaction($conn, $arguments = array(), $options = array())
  {
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

      $address = sprintf($options['mail-address-format'], $member->getId());
      $member->setConfig('pc_address', $address);
      $member->setConfig('mobile_address', $address);

      $password = preg_replace("/%d/", $member->getId(), $options['password-format'], 1);
      $member->setConfig('password', md5($password));

      $this->logSection('member+', $member->getName());
      if (isset($linkMember))
      {
        $memberRelationship1 = new MemberRelationship();
        $memberRelationship1->setMember($member);
        $memberRelationship1->setMemberRelatedByMemberIdFrom($linkMember);
        $memberRelationship1->setIsFriend(true);
        $memberRelationship1->save();

        $memberRelationship2 = new MemberRelationship();
        $memberRelationship2->setMember($linkMember);
        $memberRelationship2->setMemberRelatedByMemberIdFrom($member);
        $memberRelationship2->setIsFriend(true);
        $memberRelationship2->save();
        $this->logSection('friend link', sprintf("%s - %s", $linkMember->getId(), $member->getId()));
      }
    }
  }
}
