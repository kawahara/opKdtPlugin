<?php

class opKdtGenerateCommunityTask extends opKdtBaseTask
{
  protected function configure()
  {
    parent::configure();

    $this->name      = 'generate-community';

    require sfConfig::get('sf_data_dir').'/version.php';

    $this->addOption('name-format', null, sfCommandOption::PARAMETER_REQUIRED, "Member's Name format", 'dummy%d');
    $this->addOption('admin-member', 'a', sfCommandOption::PARAMETER_REQUIRED, "Admin member Id", 1);
    $this->addOption('category', 'c', sfCommandOption::PARAMETER_REQUIRED, "Category Id", 2);
    $this->addOption('number', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of added members', 10);
  }

  protected function executeTransaction($conn, $arguments = array(), $options = array())
  {
    $n = (int)$options['number'];

    $adminMember = Doctrine::getTable('Member')->find($options['admin-member']);
    if (!$adminMember)
    {
      throw new Exception("not found member: ".$options['admin-member']);
    }

    $communityCategory = Doctrine::getTable('CommunityCategory')->find($options['category']);
    if (!$communityCategory)
    {
      throw new Exception("not found category: ".$options['category']);
    }

    for ($i = 0; $i < $n; $i++)
    {
      $community = new Community();
      $community->setName('dummy');
      $community->setCommunityCategory($communityCategory);
      $community->save();

      $community->setName(sprintf($options['name-format'], $community->getId()));
      $community->save();

      $configData = array(
        array('description', $community->getName()),
      );

      if (version_compare(OPENPNE_VERSION, '3.5.0-dev', '>='))
      {
        // new version
        $configData[] = array('register_policy', 'open');
      }
      else
      {
        // old version
        $configData[] = array('register_poricy', 'open');
      }

      foreach ($configData as $config)
      {
        $communityConfig = new CommunityConfig();
        $communityConfig->setCommunity($community);
        $communityConfig->setName($config[0]);
        $communityConfig->setValue($config[1]);
        $communityConfig->save();
      }

      $communityMember = new CommunityMember();
      $communityMember->setCommunity($community);
      $communityMember->setMember($adminMember);

      if (version_compare(OPENPNE_VERSION, '3.3.1-dev', '>='))
      {
        $communityMember->addPosition('admin');
      }
      else
      {
        $communityMember->setPosition('admin');
      }

      $communityMember->save();
      $this->logSection('community+', $community->getName());
    }
  }
}
