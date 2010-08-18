<?php
class PluginTokenTable extends Doctrine_Table
{
  public function findOneByNameAndUser($name, $user)
  {
    $q = $this->createQuery('t')
              ->where('t.name = ?', $name)
              ->addWhere('t.user_id = ?', $user->getId())
              ->limit(1);

    return $q->fetchOne();
  }
}