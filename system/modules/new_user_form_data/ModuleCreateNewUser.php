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
 * Class ModuleCreateNewUser
 */
class ModuleCreateNewUser extends Module
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'member_default';


	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{

	/**
	 * Generate the module
	 */
	protected function compile()
	{
		global $objPage;

		$GLOBALS['TL_LANGUAGE'] = $objPage->language;

		$this->loadLanguageFile('tl_member');
		$this->loadDataContainer('tl_member');

		// Call onload_callback (e.g. to check permissions)
		if (is_array($GLOBALS['TL_DCA']['tl_member']['config']['onload_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_member']['config']['onload_callback'] as $callback)
			{
				if (is_array($callback))
				{
					$this->import($callback[0]);
					$this->$callback[0]->$callback[1]();
				}
			}
		}

		if (strlen($this->memberTpl))
		{
			$this->Template = new FrontendTemplate($this->memberTpl);
			$this->Template->setData($this->arrData);
		}

		$this->Template->fields = '';
		$this->Template->tableless = $this->tableless;
		$doNotSubmit = false;

		// Captcha
		if (!$this->disableCaptcha)
		{
			$arrCaptcha = array
			(
				'id' => 'createNewUser',
				'label' => $GLOBALS['TL_LANG']['MSC']['securityQuestion'],
				'type' => 'captcha',
				'mandatory' => true,
				'required' => true,
				'tableless' => $this->tableless
			);

			$strClass = $GLOBALS['TL_FFL']['captcha'];

			// Fallback to default if the class is not defined
			if (!$this->classFileExists($strClass))
			{
				$strClass = 'FormCaptcha';
			}

			$objCaptcha = new $strClass($arrCaptcha);

			if ($this->Input->post('FORM_SUBMIT') == 'tl_createNewUser')
			{
				$objCaptcha->validate();

				if ($objCaptcha->hasErrors())
				{
					$doNotSubmit = true;
				}
			}
		}

		$arrUser   = array();
		$arrFields = array();
		$hasUpload = false;
		$unique    = true;
		$i = 0;

		// Build form
		foreach ($this->editable as $field)
		{
			$arrData = $GLOBALS['TL_DCA']['tl_member']['fields'][$field];

			// Map checkboxWizard to regular checkbox widget
			if ($arrData['inputType'] == 'checkboxWizard')
			{
				$arrData['inputType'] = 'checkbox';
			}

			$strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];

			// Continue if the class is not defined
			if (!$this->classFileExists($strClass))
			{
				continue;
			}

			$arrData['eval']['tableless'] = $this->tableless;
			$arrData['eval']['required'] = $arrData['eval']['mandatory'];

			$objWidget = new $strClass($this->prepareForWidget($arrData, $field, $arrData['default']));
			$objWidget->storeValues = true;
			$objWidget->rowClass = 'row_' . $i . (($i == 0) ? ' row_first' : '') . ((($i % 2) == 0) ? ' even' : ' odd');

			// Increase the row count if its a password field
			if ($objWidget instanceof FormPassword)
			{
				$objWidget->rowClassConfirm = 'row_' . ++$i . ((($i % 2) == 0) ? ' even' : ' odd');
			}

			// Validate input
			if ($this->Input->post('FORM_SUBMIT') == 'tl_createNewUser')
			{
				$objWidget->validate();
				$varValue = $objWidget->value;

				// Check whether the password matches the username
				if ($objWidget instanceof FormPassword && $varValue == $this->Input->post('username'))
				{
					$objWidget->addError($GLOBALS['TL_LANG']['ERR']['passwordName']);
				}

				$rgxp = $arrData['eval']['rgxp'];

				// Convert date formats into timestamps (check the eval setting first -> #3063)
				if (($rgxp == 'date' || $rgxp == 'time' || $rgxp == 'datim') && $varValue != '')
				{
					// Use the numeric back end format here!
					$objDate = new Date($varValue, $GLOBALS['TL_CONFIG'][$rgxp.'Format']);
					$varValue = $objDate->tstamp;
				}

				// Make sure that unique fields are unique (check the eval setting first -> #3063)
				if ($arrData['eval']['unique'] && $varValue != '')
				{
					$objUnique = $this->Database->prepare("SELECT * FROM tl_member WHERE " . $field . "=?")
												->limit(1)
												->execute($varValue);

					if ($objUnique->numRows)
					{
						$unique = false;
					}
				}

				if ($objWidget->hasErrors())
				{
					$doNotSubmit = true;
				}

				// Store current value
				elseif ($objWidget->submitInput())
				{
					$arrUser[$field] = $varValue;
				}
			}

			if ($objWidget instanceof uploadable)
			{
				$hasUpload = true;
			}

			$temp = $objWidget->parse();

			$this->Template->fields .= $temp;
			$arrFields[$arrData['eval']['feGroup']][$field] .= $temp;

			++$i;
		}

		// Captcha
		if (!$this->disableCaptcha)
		{
			$objCaptcha->rowClass = 'row_'.$i . (($i == 0) ? ' row_first' : '') . ((($i % 2) == 0) ? ' even' : ' odd');
			$strCaptcha = $objCaptcha->parse();

			$this->Template->fields .= $strCaptcha;
			$arrFields['captcha'] .= $strCaptcha;
		}
		
		// Store all values in the session
		foreach (array_keys($_POST) as $key)
		{
			$_SESSION['FORM_DATA'][$key] = $this->allowTags ? $this->Input->postHtml($key, true) : $this->Input->post($key, true);
		}			

		$this->Template->rowLast = 'row_' . ++$i . ((($i % 2) == 0) ? ' even' : ' odd');
		$this->Template->enctype = $hasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';
		$this->Template->hasError = $doNotSubmit;

		// Create new user if there are no errors
		if ($this->Input->post('FORM_SUBMIT') == 'tl_createNewUser' && !$doNotSubmit && $unique)
		{
			$this->createNewUser($arrUser);
		}

		$this->Template->loginDetails = $GLOBALS['TL_LANG']['tl_member']['loginDetails'];
		$this->Template->addressDetails = $GLOBALS['TL_LANG']['tl_member']['addressDetails'];
		$this->Template->contactDetails = $GLOBALS['TL_LANG']['tl_member']['contactDetails'];
		$this->Template->personalData = $GLOBALS['TL_LANG']['tl_member']['personalData'];
		$this->Template->captchaDetails = $GLOBALS['TL_LANG']['MSC']['securityQuestion'];

		// Add groups
		foreach ($arrFields as $k=>$v)
		{
			$this->Template->$k = $v;
		}

		$this->Template->captcha = $arrFields['captcha'];
		$this->Template->formId = 'tl_createNewUser';
		$this->Template->slabel = specialchars($this->cnu_submit);
		$this->Template->action = $this->getIndexFreeRequest();
		
		if (!$unique)
		{
			$this->jumpToOrReload($this->jumpTo);	
		}
	}


	/**
	 * Create a new user and redirect
	 * @param array
	 */
	protected function createNewUser($arrData)
	{

	/**
	 * Send an admin notification e-mail
	 * @param integer
	 * @param array
	 */
	protected function sendAdminNotification($intId, $arrData)
	{}

?>
