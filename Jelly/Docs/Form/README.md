
## Jelly Form Class

Jelly Form Class

<a href="https://raw.githubusercontent.com/obullo/demo_blog/2f3ed528f978240e9075b285c48e6d3bce7191e7/assets/jelly/images/diagram.png" target="_blank"><img src="https://raw.githubusercontent.com/obullo/demo_blog/2f3ed528f978240e9075b285c48e6d3bce7191e7/assets/jelly/images/diagram.png"></a>

<ul>
    <li><a href='#creatingforms'>Creating forms to database</a></li>
        <ul>
    	   <li><a href='#createanexampleinsertform'>Create an example insert form.</a></li>
        </ul>
    <li><a href='#updatingforms'>Updating forms to database</a></li>
        <ul>
           <li><a href='#createanexampleupdateform'>Create an example update form</a></li>
        </ul>
    <li><a href='#readingforms'>Reading forms to database</a></li>
</ul>

### Initializing the Class

------

First you need to define <kbd>Jelly/Form</kbd> class as services. Update your service.php

```php
<?php
/*
|--------------------------------------------------------------------------
| Jelly Form
|--------------------------------------------------------------------------
*/
$c['jelly/form'] = function () use ($c) {
    return new Obullo\Jelly\Form(
        array(
            'db.form_tablename'    => 'forms',
            'db.group_tablename'   => 'form_groups',
            'db.option_tablename'  => 'form_options',
            'db.element_tablename' => 'form_elements',
            'tpl.elementDiv' => '<div class="form-group">
            <label class="col-sm-2 control-label">%s</label>
                <div class="col-sm-8">
                    %s
                </div>
            </div>',
            'tpl.groupElementDiv' => array(
                'groupedDiv' => '<div class="form-group">
                    <label class="col-sm-2 control-label">%s</label>
                    %s
                </div>', // groupedDiv second "%s" we change to parentDiv.
                'parentDiv' => '<div class="col-sm-2">%s</div>'
            ),
            'ajax.function' => 'submitAjax(%s)'
        )
    );
};
```

```php
<?php
$c->load('service/jelly/form');
$this->jellyForm->method();
```

### Form Query Operations ( Reading Forms ) <a name='readingforms'></a>

------

#### $this->jellyForm->getForm($primaryKey, $select);

Get one form.

```php
<?php
print_r($this->jellyForm->getForm($primaryKey = 1, 'id,name,resource_id'));
// Gives
Array
(
    [id] => 1
    [name] => userEdit
    [resource_id] => admin/user/edit
)
```

#### $this->jellyForm->getAllForms($select);

Get all forms.

```php
<?php
print_r($this->jellyForm->getAllForms('id,name,resource_id,action,attribute'));
// Gives
Array
(
    [0] => Array
        (
            [id] => 1
            [name] => updateTwoElements
            [resource_id] => admin/user/edit
            [action] => /jelly/test_update_two_elements
            [attribute] => id="user"
        )
    [1] => Array
        (
            [id] => 2
            [name] => updateAllElements
            [resource_id] => admin/user/edit
            [action] => /jelly/test_update_all_elements
            [attribute] => id="user"
        )
)
```

#### $this->jellyForm->getFormElement($primaryKey, $select);

Get one form element.

```php
<?php
print_r($this->jellyForm->getFormElement(1, 'id,label,name,rules'));
// Gives
Array
(
    [id] => 1
    [label] => Username
    [name] => username
    [rules] => required|min(4)|max(25)
)
```

#### $this->jellyForm->getFormElements($formId, $select);

Get all elements of the form.

```php
<?php
print_r($this->jellyForm->getFormElements(1, 'id,label,name,rules'));
// Gives
Array
(
    [0] => Array
        (
            [id] => 1
            [label] => Username
            [name] => username
            [rules] => required|min(4)|max(25)
        )
    [1] => Array
        (
            [id] => 2
            [label] => Email
            [name] => email
            [rules] => required|email
        )
    [2] => Array
        (
            [id] => 3
            [label] => First Name
            [name] => firstname
            [rules] => required|min(3)|max(30)
        )
)
```

#### $this->jellyForm->getFormGroup($primaryKey, $select);

Get one group.

```php
<?php
print_r($this->jellyForm->getFormGroup(1, 'id,name,label,class,func'));
// Gives
Array
(
    [id] => 1
    [name] => birthdate
    [label] => Birthdate
    [class] => birthdate
    [func] => function($data) { return implode('-', $data); }
)
```

#### $this->jellyForm->getFormGroups($formId, $select);

Get all groups of the form.

```php
<?php
print_r($this->jellyForm->getFormGroups(1, 'id,name,label,class,func'));
// Gives
Array
(
    [0] => Array
        (
            [id] => 1
		    [name] => birthdate
		    [label] => Birthdate
		    [class] => birthdate
		    [func] => function($data) { return implode('-', $data); }
        )
    [1] => Array
        (
            [id] => 2
		    [name] => testRadio
		    [label] => Radio
		    [class] => radio
		    [func] => function($data) { if (isset($data['foo'])) { return 'bar'; } }
        )
)
```

#### $this->jellyForm->getFormOption($primaryKey, $select);

Get one option.

```php
<?php
print_r($this->jellyForm->getFormOption(1, 'id,form_id,name,value'));
// Gives
Array
(
    [id] => 1
    [form_id] => 1
    [name] => ajax
    [value] => 1
)
```

#### $this->jellyForm->getFormOptions($formId, $select);

Get all options of the form.

```php
<?php
print_r($this->jellyForm->getFormOptions(1, 'id,form_id,name,value'));
// Gives
Array
(
    [0] => Array
        (
            [id] => 3
		    [form_id] => 1
		    [name] => ajax
		    [value] => 1
        )
    [1] => Array
        (
            [id] => 2
		    [form_id] => 1
		    [name] => foo
		    [value] => bar
        )
)	
```

### Insert Operations ( Creating Forms ) <a name='creatingforms'></a>

-----

#### $this->jellyForm->insertForm($data);

Add a new form attirbutes to database.

```php
<?php
$data                = array();
$data['name']        = $this->post['name'];
$data['resource_id'] = $this->post['resource_id'];
$data['action']      = $this->post['action'];
$data['attribute']   = $this->post['attribute'];

$e = $this->db->transaction(
    function () use ($data) {
        $this->jellyForm->insertForm($data);
    }
);
if (is_object($e)) {
    $this->form->setMessage($e->getMessage(), NOTICE_ERROR);
} else {
    $this->form->setMessage('Form successfully added.', NOTICE_SUCCESS);
}
```

#### $this->jellyForm->insertFormElement($data);

Add a new form element to "form_elements" table.

```php
<?php
$data = array();
$data['form_id']   = $this->post['form_id'];
$data['type']      = $this->post['type'];
$data['name']      = $this->post['name'];
$data['title']     = $this->post['title'];
$data['rules']     = $this->post['rules'];
$data['label']     = $this->post['label'];
$data['attribute'] = $this->post['attribute'];
$data['value']     = $this->post['value'];
$data['order']     = $this->post['order'];
$data['group_id']  = intval($this->post['group_id']);
$data['role']      = $this->post['role'];

$e = $this->db->transaction(
    function () use ($data) { // Database save operation
        if ($data['group_id'] > 0) {
            $groupData = array(
                'id'                 => $data['group_id'],
                'form_id'            => $data['form_id'],
                'number_of_elements' => '+1'
            );
            $this->jellyForm->updateFormElementCount($groupData);
        }
        $this->jellyForm->insertFormElement($data);
    }
);
if (is_object($e)) {
    $this->form->setMessage($e->getMessage(), NOTICE_ERROR);
} else {
    $this->form->setMessage('Form element successfully added.', NOTICE_SUCCESS);
}
```

#### $this->jellyForm->insertFormOption($data);

Add a new custom form option to "form_options" table.

```php
<?php
$data            = array();
$data['form_id'] = $this->post['form_id'];
$data['name']    = $this->post['name'];
$data['value']   = $this->post['value'];

$e = $this->db->transaction(
    function () use ($data) {
        $this->jellyForm->insertFormOption($data);
    }
);
if (is_object($e)) {
    $this->form->setMessage($e->getMessage(), NOTICE_ERROR);
} else {
    $this->form->setMessage('Option successfully added.', NOTICE_SUCCESS);
}
```

#### $this->jellyForm->insertFormGroup($data);

Groups is a wrapper for your form elements this functions adds a group to your form_groups table.

```php
<?php
$data = array();
$data['form_id'] = $this->post['form_id'];
$data['label']   = $this->post['label'];
$data['value']   = $this->post['value'];
$data['func']    = $this->post['func'];

$e = $this->db->transaction(
    function () use ($data) {
        $this->jellyForm->insertFormGroup($data);
    }
);
if (is_object($e)) {
    $this->form->setMessage($e->getMessage(), NOTICE_ERROR);
} else {
    $this->form->setMessage('Form group successfully added.', NOTICE_SUCCESS);
}
```

##### Create an example insert form. <a name='createanexampleinsertform'></a>

Create an user save form. The creation of forms that are created in the database.

```php
<?php
$c->load('service/rbac/user'); // User class

$this->user->setUserId(1);
$this->user->setRoleIds($this->user->getRoles()); // getRoles() function get all roles by user id.
$this->user->setResourceId('admin/user/add');

$this->jellyForm->setId('insert_form');          // Set form identifier (form name).
$this->jellyForm->setValues($this->post[true]);  // Set form values.

if ($this->request->isPost()) {

    if ( ! $this->jellyForm->validate('insert')) { // is valid?

        $this->form->setErrors($this->validator);
        $this->form->setMessage('There are some errors in the form.');
        
    } else {
        $e = $this->db->transaction(
            function () {
                $values = $this->jellyForm->getValues(); // Get all the form values safely.
                $this->db->insert('users', $values);
            }
        );
        if (is_object($e)) {
            $this->form->setMessage($e->getMessage(), NOTICE_ERROR);
        } else {
            $this->form->setMessage('User successfully inserted.', NOTICE_SUCCESS);
        }
    }
}
if ($this->request->isXmlHttp()) { // Form is ajax post?
    echo $this->response->json($this->form->getOutput());
    return;
}
$this->jellyForm->render('insert'); // Creating form to using "insert" operation name.
                                    // If you want just "view" operation,
                                    // You should not send any operation name.
```

### Update Operations. <a name='updatingforms'></a>

Update the form table.

-----

#### $this->jellyForm->updateForm($data);

```php
<?php
$data                = array();
$data['id']          = $primaryKey;
$data['name']        = $this->post['name'];
$data['resource_id'] = $this->post['resource_id'];
$data['action']      = $this->post['action'];
$data['attribute']   = $this->post['attribute'];
$data['method']   = $this->post['method'];

$e = $this->db->transaction(
    function () use ($data) {
        $this->jellyForm->updateForm($data);
    }
);
if (is_object($e)) {
    $this->form->setMessage($e->getMessage(), NOTICE_ERROR);
} else {
    $this->form->setMessage('Form successfully updated.', NOTICE_SUCCESS);
}
```

#### $this->jellyForm->updateFormElement($data);

Update form element table.

```php
<?php
$data = array();
$data['id']        = $primaryKey;
$data['form_id']   = $this->post['form_id'];
$data['name']      = $this->post['name'];
$data['title']     = $this->post['title'];
$data['rules']     = $this->post['rules'];
$data['label']     = $this->post['label'];
$data['attribute'] = $this->post['attribute'];
$data['type']      = $this->post['type'];
$data['value']     = $this->post['value'];
$data['order']     = $this->post['order'];
$data['group_id']  = intval($this->post['group_id']);
$data['role']      = $this->post['role'];

$e = $this->db->transaction(
    function () use ($data) {
        $oldElementsData = $this->jellyForm->getFormElement($data['id']); // Old elements data.
        if (( ! empty($oldElementsData['group_id']) OR ! empty($data['group_id']))
            AND $oldElementsData['group_id'] != $data['group_id']
        ) {
            $reduceGroup = array( // Reduce group (-1)
                'id'                 => $oldElementsData['group_id'],
                'form_id'            => $oldElementsData['form_id'],
                'number_of_elements' => '-1'
            );
            $this->jellyForm->updateFormElementCount($reduceGroup);
            $increaseGroup = array( // Increase group (+1)
                'id'                 => $data['group_id'],
                'form_id'            => $data['form_id'],
                'number_of_elements' => '+1'
            );
            $this->jellyForm->updateFormElementCount($increaseGroup);
        }
        $this->jellyForm->updateFormElement($data);
    }
);
if (is_object($e)) {
    $this->form->setMessage($e->getMessage(), NOTICE_ERROR);
} else {
    $this->form->setMessage('Form element successfully updated', NOTICE_SUCCESS);
}
```
#### $this->jellyForm->updateFormOption($data);

Update form option table.

```php
<?php
$data = array();
$data['id']      = $primaryKey;
$data['form_id'] = $this->post['form_id'];
$data['name']    = $this->post['name'];
$data['value']   = $this->post['value'];

$e = $this->db->transaction(
    function () use ($data) {
        $this->jellyForm->updateFormOption($data);
    }
);
if (is_object($e)) {
    $this->form->setMessage($e->getMessage(), NOTICE_ERROR);
} else {
    $this->form->setMessage('Option successfully updated.', NOTICE_SUCCESS);
}
```
#### $this->jellyForm->updateFormGroup($data);

Update form group table.

```php
<?php
$data = array();
$data['id']      = $primaryKey;
$data['form_id'] = $this->post['form_id'];
$data['label']   = $this->post['label'];
$data['value']   = $this->post['value'];
$data['func']    = $this->post['func'];

$e = $this->db->transaction(
    function () use ($data) {
        $this->jellyForm->updateFormGroup($data);
    }
);
if (is_object($e)) {
    $this->form->setMessage($e->getMessage(), NOTICE_ERROR);
} else {
    $this->form->setMessage('Form group successfully updated.', NOTICE_SUCCESS);
}
```

##### Create an example update form. <a name='createanexampleupdateform'></a>

Create update form using the "user form".

```php
<?php
$c->load('service/rbac/user');

$this->user->setUserId($userId = 1);
$this->user->setRoleIds($this->user->getRoles()); // getRoles() function get all roles by user id.
$this->user->setResourceId('admin/user/edit');

$this->jellyForm->setId('update_form'); // Set form identifier (form name).

$this->db->where('user_id', 2);
$this->db->get('users');
$row = $this->db->rowArray();

// $birthdate = explode('-', $row['birthdate']);
// $row['day']   = $birthdate[2];
// $row['month'] = $birthdate[1];
// $row['year']  = $birthdate[0];

$this->jellyForm->setValues($row);

if ($this->request->isPost()) {

    if ( ! $this->jellyForm->validate('update')) {

        $this->form->setErrors($this->validator);
        $this->form->setMessage('There are some errors in the form.');

    } else {
        $e = $this->db->transaction(
            function () {
                $values = $this->jellyForm->getValues(); // Get all the form values safely.
                $this->db->where('user_id', $this->post['user_id']);
                $this->db->update('users', $values); 
            }
        );
        if (is_object($e)) {
            $this->form->setMessage($e->getMessage(), NOTICE_ERROR);
        } else {
            $this->form->setMessage('User successfully updated.', NOTICE_SUCCESS);
        }
    }
}
if ($this->request->isXmlHttp()) { // Form is ajax post?
    echo $this->response->json($this->form->getOutput());
    return;
}
$this->jellyForm->render('update'); // Creating form to using "update" operation name.
                                    // If you want just "view" operation,
                                    // You should not send any operation name.
$this->view->load('test_form');
```

### Delete Operations ( Delete Form )

#### $this->jellyForm->deleteForm($primaryKey);

Delete form table.

```php
<?php
$e = $this->db->transaction(
    function () use ($primaryKey) {
        $this->jellyForm->deleteForm($primaryKey);
    }
);
if ($e === true) {
    $this->sess->setFlash(array('notice' => 'Form successfully deleted.', 'status' => NOTICE_SUCCESS));
} else {
    $this->sess->setFlash(array('notice' => $e->getMessage(), 'status' => NOTICE_ERROR));
}
```

#### $this->jellyForm->deleteFormElement($primaryKey);

Delete form element table.

```php
<?php
$e = $this->db->transaction(
    function () use ($primaryKey) {
        $this->jellyForm->deleteFormElement($primaryKey);
    }
);
if ($e === true) {
    $this->sess->setFlash(array('notice' => 'Form element successfully deleted.', 'status' => NOTICE_SUCCESS));
} else {
    $this->sess->setFlash(array('notice' => $e->getMessage(), 'status' => NOTICE_ERROR));
}
```

#### $this->jellyForm->deleteFormOption($primaryKey);

Delete form option table.

```php
<?php
$e = $this->db->transaction(
    function () use ($primaryKey) {
        $this->jellyForm->deleteFormOption($primaryKey);
    }
);
if ($e === true) {
    $this->sess->setFlash(array('notice' => 'Form option successfully deleted.', 'status' => NOTICE_SUCCESS));
} else {
    $this->sess->setFlash(array('notice' =>  $e->getMessage(), 'status' => NOTICE_ERROR));
}
```

#### $this->jellyForm->deleteFormGroup($primaryKey);

Delete form group table.

```php
<?php
$e = $this->db->transaction(
    function () use ($primaryKey) {
        $this->jellyForm->deleteFormGroup($primaryKey);
    }
);
if ($e === true) {
    $this->sess->setFlash(array('notice' => 'Form group successfully deleted.', 'status' => NOTICE_SUCCESS));
} else {
    $this->sess->setFlash(array('notice' => $e->getMessage(), 'status' => NOTICE_ERROR));
}
```

### Function Reference

-----

#### $this->jellyForm->setRules(string $field, string $label, string $rules);

Set rules.

#### $this->jellyForm->getForm(int $primaryKey, mixed $select);

Get one form.

#### $this->jellyForm->getFormElement(int $primaryKey, mixed $select);

Get one form element.

#### $this->jellyForm->getAllForms(mixed $select);

Get all forms.

#### $this->jellyForm->getFormElements(int $formId,mixed $select);

Get all elements of the form.

#### $this->jellyForm->getFormGroup(int $primaryKey,mixed $select);

Get one group.

#### $this->jellyForm->getFormGroups(int $formId,mixed $select);

Get all groups of the form.

#### $this->jellyForm->getFormOption(int $primaryKey,mixed $select);

Get one option.

#### $this->jellyForm->getFormOptions(int $formId,mixed $select);

Get all options of the form.

#### $this->jellyForm->insertForm(array $data);

Add a new form to database.

#### $this->jellyForm->insertFormElement(array $data);

Add a new form element to "form_elements" table.

#### $this->jellyForm->insertFormOption(array $data);

Add a new custom form option to "form_options" table.

#### $this->jellyForm->insertFormGroup(array $data);

Groups is a wrapper for your form elements this functions adds a group to your form_groups table.

#### $this->jellyForm->updateForm(array $data);

Update form table.

#### $this->jellyForm->updateFormElement(array $data);

Update form element table.

#### $this->jellyForm->updateFormOption(array $data);

Update form option table.

#### $this->jellyForm->updateFormGroup(array $data);

Update form group table.

#### $this->jellyForm->deleteForm(int $primaryKey);

Delete form table.

#### $this->jellyForm->deleteFormElement(int $primaryKey);

Delete form element table.

#### $this->jellyForm->deleteFormOption(int $primaryKey);

Delete form option table.

#### $this->jellyForm->deleteFormGroup(int $primaryKey);

Delete form group table.

#### $this->jellyForm->render(int $primaryKey, mixed $select);

Create of forms that are created in the database.

#### $this->jellyForm->setValues(array $values = array());

Set form values.

#### $this->jellyForm->setId(string $id);

Set form identifier.

#### $this->jellyForm->toArray($data = null);

If type of data is array return it as json type, otherwise return the same data type

#### $this->jellyForm->toJson($data = null, $jsonType = true);

Return json.
