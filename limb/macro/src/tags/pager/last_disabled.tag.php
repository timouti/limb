<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com 
 * @copyright  Copyright &copy; 2004-2007 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html 
 */

/**
 * @tag pager:last:disabled
 * @parent_tag_class lmbMacroPagerTag
 * @restrict_self_nesting
 * @package macro
 * @version $Id$
 */
class lmbMacroPagerLastDisabledTag extends lmbMacroTag
{
  function generate($code)
  {
    $pager = $this->findParentByClass('lmbMacroPagerTag')->getPagerVar();  
    
    $code->writePhp("if ({$pager}->isLast()) {\n");

    parent :: generate($code);

    $code->writePhp("}\n");
  }
}


