<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com
 * @copyright  Copyright &copy; 2004-2007 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 */
lmb_require('limb/toolkit/src/lmbAbstractTools.class.php');
lmb_require('limb/tree/src/lmbMPTree.class.php');
lmb_require('limb/cms/src/model/lmbCmsSessionUser.class.php');

/**
 * class lmbCmsTools.
 *
 * @package cms
 * @version $Id: lmbCmsTools.class.php 6472 2007-10-30 09:40:17Z serega $
 */
class lmbCmsTools extends lmbAbstractTools
{
  protected $tree;
  protected $user;

  function getCmsTree()
  {
    if(is_object($this->tree))
      return $this->tree;

    $this->tree = new lmbMPTree('node');

    return $this->tree;
  }

  function setCmsTree($tree)
  {
    $this->tree = $tree;
  }

  function getCmsUser()
  {
    if(is_object($this->user))
      return $this->user;

    $session = lmbToolkit :: instance()->getSession();
    if(!is_object($session_user = $session->get('lmbCmsSessionUser')))
    {
      $session_user = new lmbCmsSessionUser();
      $session->set('lmbCmsSessionUser', $session_user);
    }

    $this->user = $session_user->getUser();

    return $this->user;
  }

  function resetCmsUser()
  {
    $this->setCmsUser(null);
    $session = lmbToolkit :: instance()->getSession();
    $session->destroy('lmbCmsSessionUser');
  }

  function setCmsUser($user)
  {
    $this->user = $user;
  }
}


