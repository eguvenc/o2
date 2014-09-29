
## Form Element Class

The Form Element Class file contains functions that assist in working with forms.

### Initializing the Class

-------

```php
$c->load('form/element')
$this->formElement->method();
```

The following functions are available:

#### $this->formElement->open()

Creates an opening form tag with a base URL <b>built from your config preferences</b>. It will optionally let you add form attributes and hidden input fields.

The main benefit of using this tag rather than hard coding your own HTML is that it permits your site to be more portable in the event your URLs ever change.

Here's a simple example:

```php
<?php
echo $this->formElement->open('email/send');
```

The above example would create a form that points to your base URL plus the "email/send" URI segments, like this:

```php
<form method="post" action="http:/example.com/index.php/email/send" />
```

<b>Adding Attributes</b>

Attributes can be added by passing an associative array to the second parameter, like this:

```php
<?php
$attributes = array('class' => 'email', 'id' => 'myform');
echo $this->formElement->open('email/send', $attributes);
```

The above example would create a form similar to this:

```php
<form method="post" action="http:/example.com/index.php/email/send"  class="email"  id="myform" />
```

<b>Adding Hidden Input Fields</b>

Hidden fields can be added by passing an associative array to the third parameter, like this:

```php
$hidden = array('username' => 'Joe', 'member_id' => '234');
echo $this->formElement->open('email/send', '', $hidden);
```

The above example would create a form similar to this:

```php
<form method="post" action="http:/example.com/index.php/email/send">
<input type="hidden" name="username" value="Joe" />
<input type="hidden" name="member_id" value="234" />
```

#### $this->formElement->openMultipart()

This function is absolutely identical to the $this->formElement->open() tag above except that it adds a multipart attribute, which is necessary if you would like to use the form to upload files with.

#### $this->formElement->hidden('name', 'value' , $attributes = '')

Lets you generate hidden input fields. You can either submit a name/value string to create one field:

```php
<?php
$this->formElement->hidden('username', 'johndoe',  $attr = " id='username' " );

// Would produce:
<input type="hidden" name="username" value="johndoe" id='username'  />
```

Or you can submit an associative array to create multiple fields:

```php
$data = array(
              'name'  => 'John Doe',
              'email' => 'john@example.com',
              'url'   => 'http://example.com'
        );
echo $this->formElement->hidden($data);

// Would produce:

<input type="hidden" name="name" value="John Doe" />
<input type="hidden" name="email" value="john@example.com" />
<input type="hidden" name="url" value="http://example.com" />
```

#### $this->formElement->input('name', 'value',$attributes = '')

Lets you generate a standard text input field. You can minimally pass the field name and value in the first and second parameter:

```php
echo $this->formElement->input('username', 'johndoe', $attributes = '');
```

Or you can pass an associative array containing any data you wish your form to contain:

```php
$data = array(
    'name'      => 'username',
    'id'        => 'username',
    'value'     => 'johndoe',
    'maxlength' => '100',
    'size'      => '50',
    'style'     => 'width:50%',
);

echo $this->formElement->input($data);

// Would produce:

<input type="text" name="username" id="username" value="johndoe" maxlength="100" size="50" style="width:50%" />
```

If you would like your form to contain some additional data, like Javascript, you can pass it as a string in the third parameter:

```php
$js = 'onclick="someFunction()"';
echo $this->formElement->input('username', 'johndoe', $js);
```

#### $this->formElement->password()

This function is identical in all respects to the <dfn>$this->formElement->input()</dfn> function above except that it sets a "password" type.

#### $this->formElement->upload()

This function is identical in all respects to the <dfn>$this->formElement->input()</dfn> function above except that it sets a "file" type, allowing it to be used to upload files.

#### $this->formElement->textarea()

This function is identical in all respects to the <dfn>$this->formElement->input()</dfn> function above except that it generates a "textarea" type. Note: Instead of the "maxlength" and "size" attributes in the above example, you will specify "rows" and "cols".

#### $this->formElement->dropdown()

Lets you create a standard drop-down field. The first parameter will contain the name of the field, the second parameter will contain an associative array of options, and the third parameter will contain the value you wish to be selected. You can also pass an array of multiple items through the third parameter, and Obullo will create a multiple select for you. Example:

```php
$options = array(
    'small'  => 'Small Shirt',
    'med'    => 'Medium Shirt',
    'large'  => 'Large Shirt',
    'xlarge' => 'Extra Large Shirt',
);

$shirtsOnSale = array('small', 'large');
echo $this->formElement->dropdown('shirts', $options, 'large');

// Would produce:

<select name="shirts">
<option value="small">Small Shirt</option>
<option value="med">Medium Shirt</option>
<option value="large" selected="selected">Large Shirt</option>
<option value="xlarge">Extra Large Shirt</option>
</select>

echo $this->formElement->dropdown('shirts', $options, shirtsOnSale);

// Would produce:

<select name="shirts" multiple="multiple">
<option value="small" selected="selected">Small Shirt</option>
<option value="med">Medium Shirt</option>
<option value="large" selected="selected">Large Shirt</option>
<option value="xlarge">Extra Large Shirt</option>
</select>
```

If you would like the opening <b>select</b> to contain additional data, like an id attribute or JavaScript, you can pass it as a string in the fourth parameter:

```php
$js = 'id="shirts" onChange="someFunction();"';
echo $this->formElement->dropdown('shirts', $options, 'large', $js);
```

If the array passed as $options is a multidimensional array, $this->formElement->dropdown() will return the array keys as the label.

#### Using Your Schema for Dropwdowns

```php
<?php 
$users = array(
  '*' => array(),

    'id' => array(
        'types' => '_not_null|_primary_key|_int(11)|_auto_increment',
    ),
    'email' => array(
        'types' => '_varchar(60)|_not_null',
    ),
    'business_size' => array(
        '_enum' => array(
            'small',
            'medium',
            'large',
            'xlarge',
            'xxlarge',
        ),
        'types' => '_null|_enum',
    ),
);
 
/* End of file users.php */
/* Location: .app/schemas/users.php */
```

Let's write a schema function for enum type.

```php
<?php 
$users = array(
  '*' => array(),
  
    'id' => array(
        'types' => '_not_null|_primary_key|_int(11)|_auto_increment',
    ),
    'email' => array(
        'types' => '_varchar(60)|_not_null',
    ),
    'business_size' => array(
        '_enum' => array(
            'small',
            'medium',
            'large',
            'xlarge',
            'xxlarge',
        ),
        'types' => '_null|_enum',
    ),
   'func' => function() {
        $options = array(
            'small'   => '1 employee',
            'medium'  => '2 - 10 employees',
            'large'   => '11 - 25 employees',
            'xlarge'  => '26 - 75 employees',
            'xxlarge' => 'More than 75 employees',
        );
        $business_sizes = getSchema('users')['business_size']['_enum'];
        $sizes = array();
        foreach ($business_sizes as $val) {
            $sizes[$val] = $options[$val];
        }
        return $sizes;
    },
);
```

```php
<?php
echo $this->formElement->dropdown('business_size', '@getSchema.users.business_size.func', 'xlarge');
```

Adding custom options.

```php
<?php
echo $this->formElement->dropdown('business_size', array(array('' => 'Please specify a field .. '), '@getSchema.users.business_size.func'));
```


### Array Functions

Also you can build your array closure functions.

```php
    'business_size' => array(
        '_enum' => array(
            'small',
            'medium',
            'large',
            'xlarge',
            'xxlarge',
        ),
        'types' => '_null|_enum',
    ),
    'func' => array(
        'all' => function() {
            $options = array(
                'small'   => '1 employee',
                'medium'  => '2 - 10 employees',
                'large'   => '11 - 25 employees',
                'xlarge'  => '26 - 75 employees',
                'xxlarge' => 'More than 75 employees',
            );
            $business_sizes = getSchema('users')['business_size']['_enum'];
            $sizes = array();
            foreach ($business_sizes as $val) {
                $sizes[$val] = $options[$val];
            }
            return $sizes;
        },
        'list' => function() {
            $business_sizes = getSchema('users')['business_size']['_enum']['high'];            
            $sizes = $business_sizes();

            unset($sizes['xlarge']);
            unset($sizes['xxlarge']);

            return $sizes;
        },
    )
```

```php
<?php
echo $this->formElement->dropdown('business_size', '@getSchema.users.business_size.func.list', 'medium');
```

#### $this->formElement->fieldset()

Lets you generate fieldset/legend fields.

```php
<?php
echo $this->formElement->fieldset('Address Information');
echo "<p>fieldset content here</p>\n";
echo $this->formElement->fieldsetClose();

// Produces
<fieldset>
<legend>Address Information</legend>
<p>form content here</p>
</fieldset>
```

#### $this->formElement->fieldsetClose()

Produces a closing <b>fieldset</b> tag. The only advantage of using this function is it permits you to pass data to it which will be added below the tag. For example:

```php
<?php
$string = "</div></div>";

echo $this->formElement->fieldsetClose($string);

// Would produce:
</fieldset>
</div></div>
```

As with other functions, if you would like the tag to contain additional data, like JavaScript, you can pass it as a string in the fourth parameter:

```php
<?php
$js = 'onClick="someFunction()"';

echo $this->formElement->checkbox('newsletter', 'accept', true, $js)
```

#### $this->formElement->radio()

This function is identical in all respects to the <dfn>$this->formElement->checkbox()</dfn> function above except that is sets it as a "radio" type.

#### $this->formElement->submit()

Lets you generate a standard submit button. Simple example:

```php
<?php
echo $this->formElement->submit('mysubmit', 'Submit Post!');

// Would produce:

<input type="submit" name="mysubmit" value="Submit Post!" />
```

#### $this->formElement->reset()

Lets you generate a standard reset button. Use is identical to <dfn>$this->formElement->submit()</dfn>.

#### $this->formElement->button()

Lets you generate a standard button element. You can minimally pass the button name and content in the first and second parameter:

```php
<?php
echo $this->formElement->button('name','content');

// Would produce
<button name="name" type="button">Content</button> 
```

Or you can pass an associative array containing any data you wish your form to contain: 

```php
<?php
$data = array(
    'name'    => 'button',
    'id'      => 'button',
    'value'   => 'true',
    'type'    => 'reset',
    'content' => 'Reset'
);

echo $this->formElement->button($data);

// Would produce:
<button name="button" id="button" value="true" type="reset">Reset</button>  
```

If you would like your form to contain some additional data, like JavaScript, you can pass it as a string in the third parameter: 

```php
<?php
$js = 'onClick="someFunction()"';
echo $this->formElement->button('mybutton', 'Click Me', $js);
```

#### $this->formElement->close()

Produces a closing tag. The only advantage of using this function is it permits you to pass data to it which will be added below the tag. For example:

```php
<?php
$string = "</div></div>";

echo $this->formElement->close($string);

// Would produce:

</form>
</div></div>
```

#### $this->formElement->prep()

Allows you to safely use HTML and characters such as quotes within form elements without breaking out of the form. Consider this example:

```php
$string = 'Here is a string containing <strong>"quoted"</strong> text.';
<input type="text" name="myform" value="$string" />
```

Since the above string contains a set of quotes it will cause the form to break. The form\prep function converts HTML so that it can be used safely:

```php
<input type="text" name="myform" value="<?php echo $this->formElement->prep($string); ?>" />
```

**Note:** If you use any of the form helper functions listed in this page the form values will be prepped automatically, so there is no need to call this function. Use it only if you are creating your own form elements.

### Function Reference

-----

#### $this->formElement->button($data = '', $content = '', $extra = '');

Form button

#### $this->formElement->checkbox($data = '', $value = '', $checked = false, $extra = '');

Checkbox field

#### $this->formElement->close($extra = '');

Form close tag

#### $this->formElement->dropdown($name = '', $options = '', $selected = array(), $extra = '');

Drop-down Menu

#### $this->formElement->fieldset($legend_text = '', $attributes = array());

Used to produce ```<fieldset><legend>text</legend>```. To close fieldset use form_fieldset_close()

#### $this->formElement->fieldsetClose($extra = '');

Fieldset Close

#### $this->formElement->hidden($name, $value = '', $extra = '', $recursing = false);

Generates hidden fields.  You can pass a simple key/value string or an associative array with multiple values.

#### $this->formElement->input($data = '', $value = '', $extra = '');

Text input field

#### $this->formElement->label($label_text = '', $id = '', $attributes = "");

Form label

#### $this->formElement->open($action = '', $attributes = '', $hidden = array());

Form Declaration Creates the opening portion of the form.

#### $this->formElement->openMultipart($action, $attributes = array(), $hidden = array());

Form Declaration - Multipart type Creates the opening portion of the form, but with "multipart/form-data".

#### $this->formElement->password($data = '', $value = '', $extra = '');

Identical to the input function but adds the "password" type

#### $this->formElement->prep($str = '', $field_name = '');

Formats text so that it can be safely placed in a form field in the event it has HTML tags.

#### $this->formElement->radio($data = '', $value = '', $checked = false, $extra = '')

Radio button

#### $this->formElement->reset($data = '', $value = '', $extra = '')

Reset button

#### $this->formElement->submit($data = '', $value = '', $extra = '')

Submit button

#### $this->formElement->textarea($data = '', $value = '', $extra = '')

Textarea filed

#### $this->formElement->upload($data = '', $value = '', $extra = '')

Identical to the input function but adds the "file" type