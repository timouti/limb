<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com
 * @copyright  Copyright &copy; 2004-2007 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 */

Mock::generate('lmbMacroNode', 'MockMacroNode');

class MyTestingMacroNode extends lmbMacroNode{}

class lmbMacroNodeTest extends UnitTestCase
{
  protected $node;
  protected $tag_info;
  protected $source_location;

  function setUp()
  {
    $this->source_location = new lmbMacroSourceLocation('my_file', 10);
    $this->node = new lmbMacroNode($this->source_location);
  }
  
  protected function _createNode($id = 'node', $parent = null)
  {
    $node = new lmbMacroNode($this->source_location);
    $node->setId($id);
    
    if($parent)
      $parent->addChild($node);
    
    return $node;
  }

  function testGetId()
  {
    $this->node->setId('Test');
    $this->assertEqual($this->node->getId(), 'Test');
  }

  function testGetIdGenerated()
  {
    $id = $this->node->getId();
    $this->assertEqual($this->node->getId(), $id);
  }
  
  function testGetIdByDefault()
  {
    $this->assertNotNull($this->node->getId());
  }
  
  function testGetChildren()
  {
    $child = $this->_createNode('Test', $this->node);
    $children = $this->node->getChildren();
    $this->assertReference($child, $children[0]);
  }

  function testFindChild()
  {
    $child = $this->_createNode();
    $child->setId('Test');
    $this->node->addChild($child);
    $this->assertEqual($this->node->findChild('Test'), $child);
  }

  function testFindChildInMany()
  {
    $child1 = $this->_createNode('foo', $this->node);
    $child2 = $this->_createNode('bar', $this->node);
    $this->assertEqual($this->node->findChild('bar'), $child2);
  }

  function testFindChildNotFound()
  {
    $this->assertFalse($this->node->findChild('Test'));
  }

  function testGetChild()
  {
    $child1 = $this->_createNode('test1', $this->node);
    $child2 = $this->_createNode('test2', $this->node);
    
    $this->assertReference($this->node->getChild('test2'), $child2);
  }
  
  function testGetChildThrowExceptionIfNoSuchChild()
  {
    try
    {
      $this->node->getChild('no_such_child');
      $this->assertTrue(false);
    }
    catch(lmbMacroException $e)
    {
      $this->assertTrue(true);
    }
  }
  
  function testFindUpChild()
  {
    $parent1 = $this->_createNode('parent1', $this->node);
    $parent2 = $this->_createNode('parent2', $this->node);

    $node1 = $this->_createNode('foo', $parent1);
    $node2 = $this->_createNode('bar', $parent2);
    
    $this->assertEqual($node2->findUpChild('foo')->getId(), $node1->getId());
    $this->assertEqual($parent1->findUpChild('parent2')->getId(), $parent2->getId());
    $this->assertEqual($parent1->findUpChild('foo')->getId(), $node1->getId());
  }

  function testFindChildByClassAmongImmediateChildren()
  {
    $common_child = $this->_createNode('foo', $this->node);
    $special_child = new MyTestingMacroNode();
    $this->node->addChild($special_child);
    
    $this->assertEqual($this->node->findChildByClass('MyTestingMacroNode'), $special_child);
  }

  function testFindChildByClassInDeeperLevels()
  {
    $parent = $this->_createNode('foo', $this->node);
    $special_child = new MyTestingMacroNode();
    $parent->addChild($special_child);
    $common_child = $this->_createNode('bar', $parent);
    
    $this->assertEqual($this->node->findChildByClass('MyTestingMacroNode'), $special_child);
  }

  function testFindChildByClassNotFound()
  {
    $this->assertNull($this->node->findChildByClass('Booo'));
  }

  function testFindChildrenByClass()
  {
    $parent1 = $this->_createNode('parent1', $this->node);
    $child1 = $this->_createNode('child1', $parent1);
    $child2 = $this->_createNode('child2', $parent1);

    $parent2 = $this->_createNode('parent2', $this->node);
    $child3 = new MyTestingMacroNode();
    $child4 = new MyTestingMacroNode();
    $parent2->addChild($child3);
    $parent2->addChild($child4);
    
    $this->assertEqual($this->node->findChildrenByClass('MyTestingMacroNode'), array($child3, $child4));
  }

  function testFindParentByClass()
  {
    $grandpa = new MyTestingMacroNode();
    $this->node->addChild($grandpa);

    $parent = $this->_createNode('parent', $grandpa);
    $child = $this->_createNode('child', $parent);
    
    $this->assertEqual($child->findParentByClass('lmbMacroNode'), $parent);
    $this->assertEqual($child->findParentByClass('MyTestingMacroNode'), $grandpa);
    $this->assertEqual($parent->findParentByClass('lmbMacroNode'), $grandpa);
    $this->assertEqual($child->findParentByClass('MyTestingMacroNode'), $grandpa);
  }

  function testFindParentByClassNotFound()
  {
    $this->assertNull($this->node->findParentByClass('Test'));
  }
  
  function findImmediateChildByClass()
  {
    $parent = $this->_createNode('foo', $this->node);
    $special_child = new MyTestingMacroNode();
    $parent->addChild($special_child);
    $common_child = $this->_createNode('bar', $parent);

    $special_child2 = new MyTestingMacroNode();
    $this->node->addChild($special_child2);
    
    $this->assertEqual($child->findImmediateChildByClass('MyTestingMacroNode'), $special_child2);
    // just to show the differences
    $this->assertEqual($child->findChildByClass('MyTestingMacroNode'), $special_child);    
  }

  function findImmediateChildrenByClass()
  {
    $common_child1 = $this->_createNode('child1', $this->node);
    $common_child2 = $this->_createNode('child2', $this->node);
    $special_child1 = new MyTestingMacroNode();
    $special_child2 = new MyTestingMacroNode();
    $this->node->addChild($special_child1);
    $this->node->addChild($special_child2);

    $this->assertEqual($child->findImmediateChildrenByClass('MyTestingMacroNode'), array($special_child1, $special_child2));
  }
  
  function testRemoveChild()
  {
    $child = $this->_createNode('Test', $this->node);
    $this->assertEqual($this->node->removeChild('Test'), $child);
    $this->assertNull($this->node->findChild('Test'));
  }

  function testCheckIdsOk()
  {
    $child1 = $this->_createNode('id1', $this->node);
    $child2 = $this->_createNode('id2', $this->node);

    $this->node->checkChildrenIds();
  }

  function testDuplicateIdsError()
  {
    $root = new lmbMacroNode();
    $child1 = new lmbMacroNode(new lmbMacroSourceLocation('my_file', 10));
    $child1->setId('my_tag');
    $root->addChild($child1);

    $child2 = new lmbMacroNode(new lmbMacroSourceLocation('my_file2', 15));
    $child2->setId('my_tag');
    $root->addChild($child2);

    try
    {
      $root->checkChildrenIds();
      $this->assertTrue(false);
    }
    catch(lmbMacroException $e)
    {
      $this->assertWantedPattern('/Duplicate "id" attribute/', $e->getMessage());
      $params = $e->getParams();
      $this->assertEqual($params['file'], 'my_file2');
      $this->assertEqual($params['line'], 15);
      $this->assertEqual($params['duplicate_node_file'], 'my_file');
      $this->assertEqual($params['duplicate_node_line'], 10);
    }
  }

  function testDuplicateIdIsLegalInDifferentBranches()
  {
    $branch = $this->_createNode('brand', $this->node);
    $child1 = $this->_createNode('my_tag', $branch);
    $child2 = $this->_createNode('my_tag', $this->node);

    $this->node->checkChildrenIds();
  }

  function testGenerate()
  {
    $code_writer = new lmbMacroCodeWriter('template');
    $child = new MockMacroNode();
    $child->expectCallCount('generate', 1);
    $child->expectArguments('generate', array($code_writer));
    $this->node->addChild($child);
    $this->node->generate($code_writer);
  }
}
