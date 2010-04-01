<?php

class opKdtGenerateAllTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace = 'opKdt';
    $this->name      = 'generate-all';

    require sfConfig::get('sf_data_dir').'/version.php';

    $this->addOptions(
      array(
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', null),
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      )
    );

    $this->briefDescription = 'Generate Data for Test';
    $this->detailedDescription = <<<EOF
The [opKdt:generate-all|INFO] task generates useless data for testing.
Call it with:

  [./symfony opKdt:generate-all|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $tasks = array(
      'GenerateMember',
      'GenerateCommunity',
      'GenerateCommunityTopic',
      'GenerateDiary',
      'GenerateDiaryComment',
      'JoinCommunity',
      'MakeFriend',
      'PutFootprint',
      'SendMessage',
    );

    foreach ($tasks as $task)
    {
      $taskName = sprintf('opKdt%sTask', $task);
      $t = new $taskName($this->dispatcher, $this->formatter);
      $t->run();
    }
  }
}
