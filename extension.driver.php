<?php
	
	class Extension_CheckListField extends Extension {
	/*-------------------------------------------------------------------------
		Definition:
	-------------------------------------------------------------------------*/
		
		public function about() {
			return array(
				'name'			=> 'Field: Check List',
				'version'		=> '1.0.1',
				'release-date'	=> '2009-03-18',
				'author'		=> array(
					'name'			=> 'Rowan Lewis',
					'website'		=> 'http://pixelcarnage.com/',
					'email'			=> 'rowan@pixelcarnage.com'
				),
				'description' => 'A simple field that represents multiple check boxes.'
			);
		}
		
		public function uninstall() {
			$this->_Parent->Database->query("DROP TABLE `tbl_fields_checklist`");
		}
		
		public function install() {
			return $this->_Parent->Database->query("
				CREATE TABLE IF NOT EXISTS `tbl_fields_checklist` (
					`id` INT(11) UNSIGNED NOT NULL auto_increment,
					`field_id` INT(11) UNSIGNED NOT NULL,
					`options` TEXT DEFAULT NULL,
					PRIMARY KEY (`id`),
					KEY `field_id` (`field_id`)
				)
			");
		}
		
	/*-------------------------------------------------------------------------
		Utilites:
	-------------------------------------------------------------------------*/
		
		protected $addedHeaders = false;
		
		public function addHeaders($page) {
			if (!$this->addedHeaders) {
				$page->addStylesheetToHead(URL . '/extensions/checklistfield/assets/publish.css', 'screen', 11256344);
				
				$this->addedHeaders = true;
			}
		}
	}
	
?>
