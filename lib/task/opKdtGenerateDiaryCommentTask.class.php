<?php

class opKdtGenerateDiaryCommentTask extends sfBaseTask
{
  protected $memberIds = array();

  protected function configure()
  {
    $this->namespace = 'opKdt';
    $this->name      = 'generate-diary-comment';

    require sfConfig::get('sf_data_dir').'/version.php';

    $this->addOptions(
      array(
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', null),
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
        new sfCommandOption('number', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of diary comments', 5),
      )
    );
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $this->memberIds = array_map(create_function('$m', 'return $m[\'id\'];'), Doctrine::getTable('Member')->findAll(Doctrine::HYDRATE_ARRAY));

    $diaries = Doctrine::getTable('Diary')->findAll(Doctrine::HYDRATE_ARRAY);
    foreach ($diaries as $diary)
    {
      for ($i=0; $i<$options['number']; ++$i)
      {
        shuffle($this->memberIds);
        $comment = new DiaryComment();
        $comment->setDiaryId($diary['id']);
        $comment->setMemberId($this->memberIds[0]);
        $comment->setBody('body');
        $comment->save();
        $comment->free();
        $this->logSection('added a diary comment', sprintf('%s - %s', $diary['id'], $this->memberIds[0]));
      }
      unset($diary);
    }
  }
}
