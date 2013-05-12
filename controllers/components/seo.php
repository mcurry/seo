<?php
if (!Configure::read('Seo.separator')) {
	Configure::write('Seo.separator', '-');
}

class SeoComponent extends Object {
	var $Controller = null;
	static $fields = array('title' => array('name', 'title'),
												 'description' => array('description', 'desc', 'name', 'title'));
        /**
         * @return void
         * /
	function initialize(&$controller) {
		$this->Controller = $controller;
	}

	function beforeRender() {
		$data = array();

		if (!empty($this->Controller->data[$this->Controller->modelClass])) {
			$data = $this->Controller->data[$this->Controller->modelClass];
		} else if (!empty($this->Controller->viewVars[strtolower(Inflector::underscore($this->Controller->modelClass))])) {
			$data = $this->Controller->viewVars[strtolower(Inflector::underscore($this->Controller->modelClass))];
		}

		$this->__setPageTitle($data);
		$this->__setMetaDescription($data);
	}

	function verifySlug($data) {
		if(!empty($data[$this->Controller->modelClass])) {
			$data = $data[$this->Controller->modelClass];
		}
		
		$slug = Seo::slug($data, array('id' => false));
		if ($this->Controller->params['pass'][1] != $slug) {
			$this->Controller->redirect(array('action' => $this->Controller->action, Seo::slug($data)), 301);
		}
	}

	function __setPageTitle($data) {
		if ($this->Controller->pageTitle !== false) {
			return;
		}

		$title = $this->__findData($data, 'title');

		if ($title) {
			$this->Controller->pageTitle = $title;
		}
	}

	function __setMetaDescription($data) {
		if (!empty($this->Controller->viewVars['meta_description_for_layout'])) {
			return;
		}

		$description = $this->__findData($data, 'title');

		if (!$description) {
			$description = '';
		}

		$this->Controller->set('meta_description_for_layout', $description);
	}

	function __findData($data, $field) {
		if (empty($data)) {
			return false;
		}

		foreach(self::$fields[$field] as $field) {
			if (!empty($data[$field])) {
				return $data[$field];
			}

			if (!empty($data[$this->Controller->modelClass][$field])) {
				return $data[$this->Controller->modelClass][$field];
			}
		}

		return false;
	}
}

class Seo extends SeoComponent {
	static function slug($data, $options=array()) {
		$options = array_merge(array('title' => null, 'id' => true), $options);
		
		if (is_array($data)) {
			$id = $data['id'];

			if(!$options['title']) {
				foreach(parent::$fields['title'] as $field) {
					if (isset($data[$field])) {
						$options['title'] = $data[$field];
						break;
					}
				}
			}
		} else {
			$id = $data;
		}

		$pretty = '';
		if ($options['title']) {
			$pretty = sprintf('%s', strtolower(Inflector::slug($options['title'], Configure::read('Seo.separator'))));

			if (strlen($pretty) > 100) {
				$pretty = substr($pretty, 0, 100);
			}
		}

		if($options['id']) {
			return $id . '/' . $pretty;
		}
		
		return $pretty;
	}
}
?>
