<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Kirsten Roschanski (C) 2012 
 * @author     Kirsten Roschanski 
 * @package    CreateNewUser 
 * @license    LGPL 
 * @filesource
 */


/**
 * Add selectors to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'cnu_assignDir';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'cnu_activate';


/**
 * Add palettes to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['CreateNewUser'] = '{title_legend},name,headline,type;{config_legend},editable,newsletters,disableCaptcha,cnu_submit;{account_legend},cnu_groups,cnu_allowLogin,cnu_assignDir;{redirect_legend},jumpTo;{email_legend:hide},cnu_activate;{template_legend:hide},memberTpl,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';


/**
 * Add subpalettes to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['cnu_assignDir'] = 'cnu_homeDir';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['cnu_activate']  = 'cnu_jumpTo,cnu_text';


/**
 * Add fields to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['disableCaptcha'] = array
(
	'label'         => &$GLOBALS['TL_LANG']['tl_module']['disableCaptcha'],
	'exclude'       => true,
	'inputType'     => 'checkbox'
);

$GLOBALS['TL_DCA']['tl_module']['fields']['cnu_submit'] = array
(
	'label'         => &$GLOBALS['TL_LANG']['tl_module']['cnu_submit'],
	'exclude'       => true,
	'inputType'     => 'text',
	'eval'          => array('mandatory'=>true, 'decodeEntities'=>true, 'maxlength'=>32, 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['cnu_groups'] = array
(
	'label'         => &$GLOBALS['TL_LANG']['tl_module']['cnu_groups'],
	'exclude'       => true,
	'inputType'     => 'checkbox',
	'foreignKey'    => 'tl_member_group.name',
	'eval'          => array('multiple'=>true)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['cnu_allowLogin'] = array
(
	'label'         => &$GLOBALS['TL_LANG']['tl_module']['cnu_allowLogin'],
	'exclude'       => true,
	'inputType'     => 'checkbox'
);

$GLOBALS['TL_DCA']['tl_module']['fields']['cnu_skipName'] = array
(
	'label'         => &$GLOBALS['TL_LANG']['tl_module']['cnu_skipName'],
	'exclude'       => true,
	'inputType'     => 'checkbox'
);

$GLOBALS['TL_DCA']['tl_module']['fields']['cnu_close'] = array
(
	'label'         => &$GLOBALS['TL_LANG']['tl_module']['cnu_close'],
	'exclude'       => true,
	'inputType'     => 'select',
	'options'       => array('close_deactivate', 'close_delete'),
	'reference'     => &$GLOBALS['TL_LANG']['tl_module']
);

$GLOBALS['TL_DCA']['tl_module']['fields']['cnu_assignDir'] = array
(
	'label'         => &$GLOBALS['TL_LANG']['tl_module']['cnu_assignDir'],
	'exclude'       => true,
	'inputType'     => 'checkbox',
	'eval'          => array('submitOnChange'=>true)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['cnu_homeDir'] = array
(
	'label'         => &$GLOBALS['TL_LANG']['tl_module']['cnu_homeDir'],
	'exclude'       => true,
	'inputType'     => 'fileTree',
	'eval'          => array('fieldType'=>'radio', 'tl_class'=>'clr')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['cnu_activate'] = array
(
	'label'         => &$GLOBALS['TL_LANG']['tl_module']['cnu_activate'],
	'exclude'       => true,
	'inputType'     => 'checkbox',
	'eval'          => array('submitOnChange'=>true)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['cnu_jumpTo'] = array
(
	'label'         => &$GLOBALS['TL_LANG']['tl_module']['cnu_jumpTo'],
	'exclude'       => true,
	'inputType'     => 'pageTree',
	'eval'          => array('fieldType'=>'radio')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['cnu_text'] = array
(
	'label'         => &$GLOBALS['TL_LANG']['tl_module']['cnu_text'],
	'exclude'       => true,
	'inputType'     => 'textarea',
	'eval'          => array('style'=>'height:120px;', 'decodeEntities'=>true, 'alwaysSave'=>true),
	'load_callback' => array
	(
		array('tl_module_CreateNewUser', 'getActivationDefault')
	)
);


/**
 * Class tl_module_CreateNewUser
 */
class tl_module_CreateNewUser extends Backend
{

	public function getActivationDefault($varValue)
	{
		if (!trim($varValue))
		{
			$varValue = (is_array($GLOBALS['TL_LANG']['tl_module']['emailText']) ? $GLOBALS['TL_LANG']['tl_module']['emailText'][1] : $GLOBALS['TL_LANG']['tl_module']['emailText']);
		}

		return $varValue;
	}
	
}

