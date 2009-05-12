<?php
	/***************************************************************
	*  Copyright notice
	*
	*  (c) 2009 Georg Ringer <www.ringer.it>
	*  All rights reserved
	*
	*  This script is part of the TYPO3 project. The TYPO3 project is
	*  free software; you can redistribute it and/or modify
	*  it under the terms of the GNU General Public License as published by
	*  the Free Software Foundation; either version 2 of the License, or
	*  (at your option) any later version.
	*
	*  The GNU General Public License can be found at
	*  http://www.gnu.org/copyleft/gpl.html.
	*
	*  This script is distributed in the hope that it will be useful,
	*  but WITHOUT ANY WARRANTY; without even the implied warranty of
	*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	*  GNU General Public License for more details.
	*
	*  This copyright notice MUST APPEAR in all copies of the script!
	***************************************************************/


/**
 * Class for updating the db
 *
 * @author	 Georg Ringer <www.ringer.it>
 */
class ext_update  {

	/**
	 * Main function, returning the HTML content of the module
	 *
	 * @return	string		HTML
	 */
	function main()	{
		global $LANG;
		$LANG->includeLLFile(t3lib_div::getFileAbsFileName('EXT:rggooglemap/locallang.xml'));

		$content = '<form name="rggooglemap_form" action="'.htmlspecialchars(t3lib_div::linkThisScript()).'" method="post">';
		$content .= '<p>'.$this->LL('text.intro').'</p>';
		$content .= '<p><i>'.$this->LL('text.intro2').'</i></p><br />';
		$content .= $this->getCheckbox('copymarkers');
		$content .= $this->getCheckbox('copycategories', false);
						
		$content .= '<br /><input type="submit" name="update" value="'.$this->LL('button.update').'" />';
		$content .= '</form>';

		
		if (t3lib_div::_GP('update')) {
			$content .= '<h3>'.$this->LL('text.updated').'</h3>';
			$content .= '<p>'.$this->processUpdate().'</p>';
		}

		return $content;
	}

	/**
	 * Performe all the needed updates
	 *
	 * @return	string success msgs
	*/
	function processUpdate() {
		$vars = t3lib_div::_POST();
		$serverPath = t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT').'/';
		$extPath = $serverPath.t3lib_extMgm::siteRelPath('rggooglemap');
		$filemount = '';
		
		$notify = $success = $error = array();

		
		/***************
		 * #1 Copy files
		 *************'*/ 		 
		if ($vars['copymarkers']==1) {
			$files = t3lib_div::getFilesInDir($extPath.'res/icons/search/', 'png,gif,jpg', 1);
			$target = 'uploads/tx_rggooglemap/';
			
			// copy files
			foreach ($files as $file) {
				$newFileName = $serverPath.$target . basename($file);

				if (!is_file($newFileName)) {
					copy($file, $newFileName );
					$success[] = sprintf($this->LL('msg.copy.file'), basename($file));
					$tsFiles[] = basename($file);
				} else {
					$error[]	= sprintf($this->LL('msg.copy.file.error'), basename($file));
				}
			}
		}

		/***************
		 * #1 Copy files
		 *************'*/ 		 
		if ($vars['copycategories']==1) {
			$sql = 'UPDATE tt_address 
							SET tx_rggooglemap_cat = tx_rggooglemap_cat2;';
			
			$GLOBALS['TYPO3_DB']->sql_query($sql);

			$success[] = $this->LL('msg.copy.category');			
		}


		// output the warnings/error messages
		$content.= $this->getErrorWarning($error, $success, $notify);

		return $content;
	}


	/**
	 * Render a checkbox including a label
	 *
	 * @param	string		$name: Name of the checkbox which is also used for the label
	 * @param	boolean		$checked: If checkbox is checked by default  	 
	 * @return	string final checkbox
	*/	 
	function getCheckbox($name, $checked=true) {
		$vars = t3lib_div::_POST();
		$checkedText = ($checked || $vars[$name]==1) ? ' checked="checked" ' : '';

		$content = '<label for="'.$name.'">
									<input type="checkbox" name="'.$name.'" id="'.$name.'" value="1" '.$checkedText.' />
									'.$this->LL($name).'
								</label>
								<br />
							';
		return $content;
	}
	
	
	/**
	 * Render warning, success and notify messages
	 *
	 * @param	array		$error: Array holding the error msgs
	 * @param	array		$success: Array holding the success msgs
	 * @param	array		$notify: Array holding the notify msgs  	 
	 * @return	all msgs
	*/
	function getErrorWarning($error, $success, $notify=array()) {
		$content = '';

		if (count($success)>0) {
			$content.= '<div style="padding:5px;margin:5px;width:400px;color:#79C91B;background-color: #F1F9E8;border: 1px solid #79C91B;">
										<strong>'.$this->LL('msg.success').'</strong><br />'.implode('<br />', $success).'</div>';
		}

		if (count($notify)>0) {
			$content.= '<div style="padding:5px;margin:5px;width:400px;color:#FF9900;background-color: #FFF5E5;border: 1px solid #FF9900;">
										<strong>'.$this->LL('msg.warning').'</strong><br />'.implode('<br />', $notify).'</div>';
		}
				
		if (count($error)>0) {
			$content.= '<div style="padding:5px;margin:5px;width:400px;color:#CB0912;background-color: #FAE6E7;border: 1px solid #CB0912;">
										<strong>'.$this->LL('msg.error').'</strong><br />'.implode('<br />', $error).'</div>';
		}

		return $content;
	}

	/**
	* Get the localized messages from locallang and prepend 'update.'
	*
	* @param	string		$key: Key of the text
	* @return Text from the locallang.xml
	*/	
	function LL($key) {
		return $GLOBALS['LANG']->getLL('update.'.$key);
	}


	/**
	 * access is always allowed
	 *
	 * @return	boolean		Always returns true
	 */
	function access() {
		return true;
	}
	
}

// Include extension?
	if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/class.ext_update.php'])	{
		include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/class.ext_update.php']);
	}
?>