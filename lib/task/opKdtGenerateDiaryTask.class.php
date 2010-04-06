<?php

class opKdtGenerateDiaryTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace = 'opKdt';
    $this->name      = 'generate-diary';

    require sfConfig::get('sf_data_dir').'/version.php';

    $this->addOptions(
      array(
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', null),
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
        new sfCommandOption('number', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of diaries', 5),
      )
    );
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);

    $members = Doctrine::getTable('Member')->findAll(Doctrine::HYDRATE_ARRAY);

    foreach ($members as $member)
    {
      for ($i=0; $i<$options['number']; ++$i)
      {
        $diary = new Diary();
        $diary->setMemberId($member['id']);
        $diary->setTitle('title');
        $diary->setBody('body');
        $diary->setPublicFlag(1);
        $diary->save();
        $diary->free();
        $this->logSection('posted a diary', sprintf('%s', $member['id']));
      }
    }
  }
}
