<?php
	
	if (!defined('__IN_SYMPHONY__')) die('<h2>Symphony Error</h2><p>You cannot directly access this file</p>');
	
	class FieldCheckList extends Field {
	/*-------------------------------------------------------------------------
		Definition:
	-------------------------------------------------------------------------*/
		
		public function __construct(&$parent) {
			parent::__construct($parent);
			
			$this->_name = 'Check List';
			$this->_required = true;
			$this->_driver = $this->_engine->ExtensionManager->create('checklistfield');
			
			// Set defaults:
			$this->set('show_column', 'yes');
			$this->set('required', 'yes');
		}
		
		public function createTable() {
			$field_id = $this->get('id');
			
			return $this->_engine->Database->query("
				CREATE TABLE IF NOT EXISTS `tbl_entries_data_{$field_id}` (
					`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					`entry_id` INT(11) UNSIGNED NOT NULL,
					`handle` VARCHAR(255) DEFAULT NULL,
					`value` TEXT DEFAULT NULL,
					PRIMARY KEY (`id`),
					KEY `entry_id` (`entry_id`),
					FULLTEXT KEY `value` (`value`)
				)
			");
		}
		
		public function canFilter() {
			return true;
		}
		
	/*-------------------------------------------------------------------------
		Settings:
	-------------------------------------------------------------------------*/
		
		public function displaySettingsPanel(&$wrapper, $errors = null) {
			parent::displaySettingsPanel($wrapper, $errors);
			
			$order = $this->get('sortorder');
			$label = Widget::Label(__('Options'));
			$label->appendChild(Widget::Input(
				"fields[{$order}][options]",
				General::sanitize($this->get('options'))
			));
			
			$wrapper->appendChild($label);
			
			$this->appendShowColumnCheckbox($wrapper);
		}
		
		public function commit($propogate = null) {
			if (!parent::commit()) return false;
			
			$id = $this->get('id');
			$handle = $this->handle();
			$options = $this->get('options');
			
			if ($id === false) return false;
			
			$options = preg_split('/\s*,\s*/', $options, -1, PREG_SPLIT_NO_EMPTY);
			$options = implode(', ', $options);
			
			$fields = array(
				'field_id'			=> $id,
				'options'			=> $options
			);
			
			$this->Database->query("
				DELETE FROM
					`tbl_fields_{$handle}`
				WHERE
					`field_id` = '{$id}'
				LIMIT 1
			");
			
			return $this->Database->insert($fields, "tbl_fields_{$handle}");
		}
		
	/*-------------------------------------------------------------------------
		Publish:
	-------------------------------------------------------------------------*/
		
		public function displayPublishPanel(&$wrapper, $data = null, $error = null, $prefix = null, $postfix = null) {
			if (!is_array($data['handle'])) {
				$data['handle'] = array($data['handle']);
				$data['value'] = array($data['value']);
			}
						
			$this->_driver->addHeaders($this->_engine->Page);
			$name = $this->get('element_name');
			$options = preg_split('/\s*,\s*/', $this->get('options'), -1, PREG_SPLIT_NO_EMPTY);
			$wrapper->appendChild(Widget::Label($this->get('label')));
			$settings = new XMLElement('div');
			
			foreach ($options as $option) {
				$attributes = array();
				
				if (in_array(Lang::createHandle($option), $data['handle'])) {
					$attributes = array('checked' => 'checked');
				}
				
				$settings->appendChild(Widget::Label(__(
					"%s {$option}", array(
					Widget::Input(
						"fields{$prefix}[$name][]{$postfix}", $option, 'checkbox', $attributes
					)->generate()
				))));
			}
			
			$wrapper->appendChild($settings);
		}
		
	/*-------------------------------------------------------------------------
		Input:
	-------------------------------------------------------------------------*/
		
		public function processRawFieldData($data, &$status, $simulate = false, $entry_id = null) {
			$status = self::__OK__;
			$result = array(
				'handle'	=> array(),
				'value'		=> array()
			);
			
			foreach ($data as $option) {
				$result['handle'][] = Lang::createHandle($option);
				$result['value'][] = $option;
			}
			
			return $result;
		}
		
	/*-------------------------------------------------------------------------
		Output:
	-------------------------------------------------------------------------*/
		
		public function appendFormattedElement(&$wrapper, $data, $encode = false) {
			if (!is_array($data['handle'])) {
				$data['handle'] = array($data['handle']);
				$data['value'] = array($data['value']);
			}
			
			$options = preg_split('/\s*,\s*/', $this->get('options'), -1, PREG_SPLIT_NO_EMPTY);
			$element = new XMLElement($this->get('element_name'));
			
			foreach ($options as $option) {
				$handle = Lang::createHandle($option);
				$value = 'no';
				
				if (in_array($handle, $data['handle'])) {
					$value = 'yes';
				}
				
				$element->appendChild(new XMLElement(
					$handle, $value, array(
						'value'		=> $option
					)
				));
			}
			
			$wrapper->appendChild($element);
		}
		
		public function prepareTableValue($data, XMLElement $link = null) {
			if (empty($data)) return;
			
			$value = implode(', ', $data['value']);
			
			return parent::prepareTableValue(
				array(
					'value'		=> General::sanitize(strip_tags($value))
				), $link
			);
		}
	}
	
?>