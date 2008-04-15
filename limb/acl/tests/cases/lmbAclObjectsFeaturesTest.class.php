<?php
/*
* Limb PHP Framework
*
* @link http://limb-project.com
* @copyright  Copyright &copy; 2004-2007 BIT(http://bit-creative.com)
* @license    LGPL http://www.gnu.org/copyleft/lesser.html
*/
lmb_require('limb/acl/src/lmbAcl.class.php');
lmb_require('limb/acl/src/lmbRoleProviderInterface.interface.php');
lmb_require('limb/acl/src/lmbResourceProviderInterface.interface.php');
lmb_require('limb/acl/src/lmbRolesResolverInterface.interface.php');

class Acl_Tests_User implements lmbRoleProviderInterface
{
  protected $is_logged_in;
  public $name;

  function __construct($is_logged_in = false)
  {
    $this->is_logged_in = $is_logged_in;
  }

  function getRole()
  {
    if($this->is_logged_in)
      return 'member';
    else
      return 'guest';
  }
}

class Acl_Tests_Member implements lmbRoleProviderInterface
{
  public $name;  

  function __construct($name)
  {
    $this->name = $name;
  }
  
  function getRole()
  {
    return 'member';
  }

}

class Acl_Tests_Article implements lmbRolesResolverInterface, lmbResourceProviderInterface
{  

  function getRoleFor($object)
  {
    if('Bob' === $object->name)
      return 'owner';
    if('Valtazar' === $object->name)
      return 'approver';
  }
  
  function getResource()
  {
    return 'article';
  }
}

class lmbAclObjectsFeatureTest extends UnitTestCase
{
  protected $acl;

  function setUp()
  {
    $this->acl = new lmbAcl();
  }

  function testGetRole()
  {
    $user = new Acl_Tests_User($is_logged_in = false);
    $this->assertEqual('guest', $user->getRole());

    $user = new Acl_Tests_User($is_logged_in = true);
    $this->assertEqual('member', $user->getRole());
  }

  function testGetRoleFromResolver()
  {
    $article = new Acl_Tests_Article();

    $user = new Acl_Tests_Member('Bob');
    $this->assertEqual('owner', $article->getRoleFor($user));

    $user = new Acl_Tests_Member('Valtazar');
    $this->assertEqual('approver', $article->getRoleFor($user));
  }

  function testAclDynamicResolving()
  {
    $article = new Acl_Tests_Article();

    $member = new Acl_Tests_Member('Vasya');
    $owner = new Acl_Tests_Member('Bob');

    $this->acl->addRole('member');
    $this->acl->addRole('owner', 'member');
    $this->acl->addResource('article');

    $this->acl->allow('owner', 'article', 'edit');

    $this->assertFalse($this->acl->isAllowed($member, $article, 'edit'));

    $this->assertTrue($this->acl->isAllowed($owner, $article, 'edit'));
  }

}