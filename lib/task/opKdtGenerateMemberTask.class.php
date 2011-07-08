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
      self::setMemberConfig($member->id, 'pc_address', $address);
      self::setMemberConfig($member->id, 'mobile_address', $address);

      $password = preg_replace("/%d/", $member->getId(), $options['password-format'], 1);
      self::setMemberConfig($member->id, 'password', md5($password));

      $this->logSection('member+', $member->getName());
      if (isset($linkMember))
      {
        $memberRelationship1 = new MemberRelationship();
        $memberRelationship1->setMemberIdTo($member->id);
        $memberRelationship1->setMemberIdFrom($linkMember->id);
        $memberRelationship1->setIsFriend(true);
        $memberRelationship1->save();
        $memberRelationship1->free(true);

        $memberRelationship2 = new MemberRelationship();
        $memberRelationship2->setMemberIdTo($linkMember->id);
        $memberRelationship2->setMemberIdFrom($member->id);
        $memberRelationship2->setIsFriend(true);
        $memberRelationship2->save();
        $memberRelationship2->free(true);
        $this->logSection('friend link', sprintf("%s - %s", $linkMember->getId(), $member->getId()));
      }

      $member->free(true);
    }
  }

  // MemberConfigTableメモリリーク対策
  // 重複チェックをしていないため新規項目以外に使用してはならない
  static protected function setMemberConfig($memberId, $name, $value)
  {
    $config = new MemberConfig();
    $config->member_id = $memberId;
    $config->name = $name;
    $config->value = $value;
    $config->save();
    $config->free(true);
  }
}
