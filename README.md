gc_with_view
============

Grocerycrud with option to add view in the list

Here is an complete example of how to get grocery crud with a View button in the list. 

This currently is implemented in the flexgrid theme. This surely can be extended to the othr themes for sure.

Main changes / additions in here are

1 - assets/grocery_crud/themes/flexigrid/views/list_with_view.php

2 - assets/grocery_crud/themes/flexigrid/views/view.php	

3 - assets/grocery_crud/themes/flexigrid/css/flexigrid.css

4 - (Added new) assets/grocery_crud/themes/flexigrid/css/images/view.png

5 - application/libraries/grocery_crud_with_view.php


You can either just copy this set of files that you can include in your existing code or you can start the whole 
new project with this codebase. The whole purpose of putting up all the files together is to show the code in action.
Many developers find it difficult to patch with existing code. This will help them glue it perfectly.


In any controller where we need to have a view button along side with edit / delete, 
instead of 

$this->load->library('grocery_CRUD');	

will have to make following call - $this->load->library('Grocery_crud_with_view');

It dose not matter even if you call - $this->load->library('Grocery_crud_with_view');  globaly in constructor, 
you still will be able to make regular call  

$crud = new grocery_CRUD(); 		//This will resume the old existing functionality without having a view button in list

$crud = new Grocery_crud_with_view();		//This will avail the facility of view button in the list.


This version of the code is exclusively moulded to work with the current stable release of grocery crud.

This surely can also work with the developmental release for the same. Ofcourse, to take much better advantage of 
the latest release like ajax view and all, you will need to mould the code accordingly. I know you will are good 
enough to do it :)

