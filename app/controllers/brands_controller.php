<?php
class BrandsController extends AppController {
	var $view = 'Theme';
	var $theme = 'private';
  var $name = 'Brands';
  var $components = array('Upload', 'Session');
  var $uses = array('User', 'Brand');
  
  var $permissions = array(
    'index' => '*',
    'help' => '*',
    'add' => array('authenticated'),
    'edit' => array('authenticated'),
    'switchBrand' => array('authenticated'),
  );

  // @todo: figure out why this piece of code has to be here if loginRedirect is set (?)
  //   should be in the users/login action after validation success
  function index() {
    if ($this->Auth->user()) {
      $this->Session->write('Auth.User.group', $this->User->Group->field('name', array('id' => $this->Auth->user('group_id'))));
    }
	}
	
	function help() { }

  function beforeFilter() {
	  parent::beforeFilter();
  }
		
	/**
	 * Add a brand new brand
	 */
	function add() {
		$this->set('user_id', $this->User->id);
		
		if ((!empty($this->data)) && ($this->Brand->validates())) {
			
			$this->__uploadBrandIcon();

      //@todo set the Brand inactive until payment has been received

      // Save the Brand and return to the dashboard
      if ($this->Brand->save($this->data)) {
	      $brand = $this->Brand->find("id = '" . $this->Brand->id . "'"); // urgh, extra query...
	      $this->Session->write('Brand', $brand['Brand']);
        $this->Session->setFlash('Your brand has been saved');

	      $this->redirect(array('controller' => 'brands', 'action' => 'subscriptions', $this->Brand->id));
      }

      // @todo redirect to the actual page they are on instead of defaulting to the dashboard
			$this->redirect(array('controller' => 'brands', 'action' => 'index'));
    }
	}
	
	/**
	 * Edit a brand
	 */
	function edit($id = NULL) {
		$this->Brand->id = $id;
    if (empty($this->data)) {
	    $this->data = $this->Brand->read();
    } else {

    	$this->__uploadBrandIcon();

	    if ($this->Brand->save($this->data, FALSE)) {
		    $this->Session->write('Brand', $this->Brand->data['Brand']);
	      $this->Session->setFlash('Your Brand has been updated');
	      $this->redirect(array('controller' => 'brands', 'action' => 'index'));
	    }
    }
	}
	
	/**
	 * Switch to a different active Brand
	 */
	function switchBrand($id = NULL) {
		if ($id) {			
		  $this->Brand->id = $id;
		  $data = $this->Brand->read();
      if ($this->Session->read('Brand')) {
	      $this->Session->delete('Brand');
      }
      $this->Session->write('Brand', $data['Brand']);
	  }
		
    $this->redirect(array('controller' => 'brands', 'action' => 'index'));
	}

	function __uploadBrandIcon() {
		// Image handling
		$destination = realpath('../../app/webroot/img/icons/') . '/';
		$file = $this->data['Brand']['image'];

		$ext = $this->Upload->ext($file['name']);
		$uniquename = md5($this->data['Brand']['image']['name'] . time()) . '.' . $ext; // ensure uniqueness

		$result = $this->Upload->upload($file, $destination, $uniquename, array('type' => 'resizecrop', 'size' => array('50', '50'), 'output' => 'jpg'));

		if (!$result){
			$this->data['Brand']['icon'] =  $this->Upload->result;
		} else {
			// display error
			$errors = $this->Upload->errors;

			// piece together errors
			if (is_array($errors)) {
	      $errors = implode("<br />",$errors); 
	    }

			$this->Session->setFlash($errors);
			$this->redirect(array('controller' => 'brands', 'action' => 'index'));
			exit();
 		}		
	}
}