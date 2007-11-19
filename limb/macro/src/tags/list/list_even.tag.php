<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com
 * @copyright  Copyright &copy; 2004-2007 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 */

lmb_require('limb/macro/src/lmbMacroTag.class.php');

/**
 * Renders a portion of the template if the current list row is even
 * @tag list:even
 * @parent_tag_class lmbMacroListItemTag
 * @package macro
 * @version $Id$
 */
class lmbMacroListRowEvenTag extends lmbMacroTag
{
  function generateContents($code)
  {
    $list = $this->findParentByClass('lmbMacroListTag');
    $counter_var = $list->getCounterVar();

    $code->writePHP('if(('. $counter_var . '+1) % 2 == 0) {');
    parent :: generateContents($code);
    $code->writePHP('}');
  }
}

