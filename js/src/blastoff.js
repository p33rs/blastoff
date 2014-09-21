/** @TODO
 * - Restyle the page for mobile
 */

$(function() {

  /**
   *  Init some jQuery objects
   **/
   
  $('#jsWarn').hide();
  
  // set up ajax defaults
  // note: ajax returns the keys data, success, message.
  $.ajaxSetup({
    url: 'ajax.php',
    type: 'POST',
    dataType: 'json'
  });
  // init dialogs
  $('#d-notify, #d-editlist, #d-addlist, #d-saveboard, #d-createboard').dialog({
    autoOpen: false,
    resizable: false,
  });
  $('#d-editlist').dialog('option', {
    open: function() {
      confirmDeleteList(false);
      $(this).dialog('option', 'width', (windowWidth() * .8));
      $(this).dialog('option', 'position', 'center');
    }, 
    resizable: true
  });
  $('#d-notify').dialog('option', {
    title: 'Notice',
    buttons: {
      Close: function(e) {
        $('#d-notify').dialog('close');
      }
    }
  });
  $('#d-addlist').dialog('option', {
    title: 'Add List',
    buttons: {
      Cancel: function(e) {
        e.preventDefault();
        $('#d-addlist').dialog('close');
      },
      Update: function(e) {
        $('#addlist-form').submit();
      }
    }
  });
  $('#d-createboard').dialog('option', {
    title: 'Save Board',
    buttons: {
      Confirm: function(e) {
        $('#createboard-form').submit();
      },
      Cancel: function(e) {
        e.preventDefault();
        $('#d-createboard').dialog('close');
      }
    }
  });
  $('#d-saveboard').dialog('option', {
    title: 'Save Board',
    buttons: {
      Confirm: function(e) {
        $('#saveboard-form').submit();
      },
      Cancel: function(e) {
        e.preventDefault();
        $('#d-saveboard').dialog('close');
      }
    }
  });
  
  
  
  /**
   * On load, pull up the requested board, if any
   **/
  
  // If an ID was provided, load the board
  setSaveButton(false);
  if ($('#container').data('id')) {
    load();
  }
  else {
    $('#container').html(createBoard([
      {
        title: 'Welcome!',
        items: [ 
          {text:'This page lets you create a list of links, like this:'},
          {text:'Google', url:'https://www.google.com'},
          {text:'xkcd', url:'https://www.xkcd.com'},
          {text:'You can add links or delete this list by clicking "edit".'},
          {text:'You can also add more lists to the board by clicking "add list", up top.'},
          {text:'If you save your board, it will get its own URL, which you can then use as your browser\'s homepage.'} 
        ]
      }
    ]));
    $('#container').sortable('refresh');
  }
  $('#container').sortable({
    handle: 'h2',
    start: function(event, ui) {
      ui.item.data('oldindex', ui.item.index());
    },
    stop: function(event, ui) {
      if (ui.item.data('oldindex') != ui.item.index()) {
        setSaveButton(true);
      }
    }
  });
  
  
  
  /**
   * Save Board
   */
  
  // Open the dialog to save the board
  $('#header-saveboard').on('click', function(e) {
    $('#d-saveboard ul, #d-createboard ul').empty();
    $('#saveboard-password, #createboard-password1, #createboard-password2').val('');
    if (!$('#container div').length) {
      notify('There are no lists to save.');
    }
    else if ($('#container').data('id')) {
      $('#d-saveboard').dialog('open');
    }
    else {
      $('#d-createboard').dialog('open');
    }
  });
  
  // Confirm the new board save and send the ajax command
  $('#createboard-form').on('submit', function(e) {
    e.preventDefault();
    error = false;
    $('#d-createboard ul').empty();
    var pass1 = $('#createboard-password1').val();
    var pass2 = $('#createboard-password2').val();
    if (pass1 != pass2) {
      $('#d-createboard ul').append('<li>The passwords do not match.</li>');
      error = true;
    }
    if (pass1.length < 6) {
      $('#d-createboard ul').append('<li>The password must be at least six characters long.</li>');
      error = true;
    }
    // fire off the ajax command
    if (!error) {
      create(pass1);
      $('#d-createboard').dialog('close');
    }
  });
  
  // Confirm the board update and send the ajax command
  $('#saveboard-form').on('submit', function(e) {
    e.preventDefault();
    $('#d-saveboard ul').empty();
    save($('#saveboard-password').val());
    $('#d-saveboard').dialog('close');
  });
  
  // Cancel the board update
  $('#saveboard-cancel').on('click', function(e) {
    e.preventDefault();
    $('#d-saveboard').dialog('close');
  });
  
  
  
  /**
   * Add List 
   **/
  
  // when "add list" is clicked, open the dialog
  $('#header-addlist').on('click', function(e) {
    e.preventDefault();
    $('#addlist-name').val('');
    $('#d-addlist').dialog('open');
  });
  
  $('#addlist-form').on('submit', function(e) {
    e.preventDefault();
    var title = $('#addlist-name').val();
    if (!title) title = 'New List';
    $('#container').append(createList({
      title: title,
      items: []
    }));
    setSaveButton(true);
    $('#d-addlist').dialog('close');
    $('#container').sortable('refresh');
  });
  
  
  
  /**
   * Edit Existing List
   **/
  
  // when "edit list" is clicked, open the dialog
  $('#container').on('click', '.list-edit', function(e) {
    e.preventDefault();
    var index = $(this).parent().index();
    var listName = getList(index).children('h2:first-child').text();
    $('#d-editlist table tbody').html(createEditList(index));
    $('#d-editlist table tbody').sortable({
      handle: '.editlist-item-handle',
      stop: function() {
        reindexEditList();
      }
    });
    $('#editlist-name').val(listName);
    $('#d-editlist').dialog('option', 'title', listName);
    $('#d-editlist').data('index', index);
    reindexEditList();
    if (!$('.editlist-item').length) {
      $('#editlist-item-add').click();
    }
    $('#d-editlist').dialog('open');
  });
  
  // when "add item" is clicked in the dialog, add another field
  $('#editlist-item-add').on('click', function(e) {
    e.preventDefault();
    $('#editlist-form tbody').append(createEditItem());
    reindexEditList();
    $('#editlist-form tbody').sortable('refresh');
  });
  
  // when "remove item" is clicked in the dialog, kill the list item
  $('#d-editlist').on('click', '.editlist-item-remove', function(e) {
    e.preventDefault();
    $(this).closest('tr').remove();
    reindexEditList();
    $('#editlist-form tbody').sortable('refresh');
  });
  
  // when "apply" is clicked in the dialog,
  //   apply the changes to the list.
  $('#editlist-form').on('submit', function(e) {
    e.preventDefault();
    var index = $('#d-editlist').data('index');
    var title = $.trim($('#editlist-name').val());
    var list = [];
    $('#d-editlist tr.editlist-item').each(function(index, element) {
      var text = $.trim($(this).find('.editlist-item-text').val());
      var url = $.trim($(this).find('.editlist-item-url').val());
      if (!text) return;
      var item = { text: text };
      if (url) item.url = url;
      list.push(item);
    });
    getList(index).replaceWith(createList({
      items: list,
      title: title
    }));
    setSaveButton(true);
    $('#d-editlist').dialog('close');
  });
  
  
  
  /**
   * Change board title
   */
  $('#header-logo-form').hide();
  $('#header-logo').on('click', function(e) {
    e.preventDefault();
    $('#header-logo').hide();
    $('#header-logo-form').show();
    $('#header-logo-new').val(
      $.trim($('#header-logo').text())
    );
  });
  $('#header-logo-form').on('submit', function(e) {
    e.preventDefault();
    // get the old one
    var oldLogo = $.trim($('#header-logo').text());
    var newLogo = $.trim($('#header-logo-new').val());
    if (!newLogo) {
      notify('You must enter a title');
    }
    else if (newLogo.length > 255) {
      notify('The title you entered was too long.');
    }
    else if (newLogo != oldLogo) {
      $('#header-logo').text(newLogo);
      setSaveButton(true);
    }
    $('#header-logo').show();
    $('#header-logo-form').hide();
  });
  $('#header-logo-cancel').on('click', function(e) {
    e.preventDefault();
    $('#header-logo').show();
    $('#header-logo-form').hide();
  });
  
  /**
   * Start a new board
   */
  $('#header-newboard-confirm, #header-newboard-cancel').hide();
  $('#header-newboard').on('click', function(e) {
    e.preventDefault();
    $('#header-newboard').hide();
    $('#header-newboard-cancel').show();
    $('#header-newboard-confirm').show();
  });
  $('#header-newboard-confirm').on('click', function(e) {
    e.preventDefault();
    var url = window.location.href.split("?")[0];
    window.location.href = url;
  });
  $('#header-newboard-cancel').on('click', function(e) {
    e.preventDefault();
    $('#header-newboard-confirm, #header-newboard-cancel').hide();
    $('#header-newboard').show();
  });
  
}); // end doc.ready



/**
 * The Edit Screen
 **/

/**
 * The "delete this list" dialog has two states:
 *   Confirming ("confirm" and "cancel" are displayed) 
 *   Normal ("delete" is displayed)
 * This function toggles between the two.
 * @param bool confirm Whether or not the "confirm" options are displayed
 */
function confirmDeleteList(confirm) {
  var buttons = { };
  if (confirm) {
    buttons['Confirm Delete'] = function(e) {
      e.preventDefault();
      var index = $('#d-editlist').data('index');
      getList(index).remove();
      setSaveButton(true);
      $('#d-editlist').dialog('close');
      $('#container').sortable('refresh');
    };
    buttons['Don\'t Delete'] = function(e) { 
      e.preventDefault();
      confirmDeleteList(false);
    };
  }
  else {
    buttons['Delete List'] = function(e) {
      e.preventDefault();
      confirmDeleteList(true);
    }
  }
  buttons.Cancel = function(e) {
    e.preventDefault();
    $('#d-editlist').dialog('close');
  };
  buttons.Update = function(e) {
    $('#editlist-form').submit();
  };
  $('#d-editlist').dialog('option', 'buttons', buttons);
}

 
/**
 * Retrieve the jquery object for the given list.
 * @param int index A list from the board
 * @return object A jQ object for the <div> representing that list.
 */
function getList(index) {
  var result = false;
  $('.list').each(function() {
    if ($(this).index() == index) result = $(this);
  });
  return result;
} // getList

/**
 * Reindex the form IDs on the edit list.
 */
function reindexEditList() {
  var index = 0;
  $('.editlist-item').each(function() {
    $(this).find('input').each(function() {
      var name = $(this).attr('class');
      $(this).attr('id', (name + '-' + index));
      $(this).attr('name', (name + '-' + index));
      $(this).siblings('label').attr('for', (name + '-' + index));
    });
    index++;
  });
} // reindexEditList

/**
 * Create a table of editable list items.
 * @param object The list to iterate
 * @return string HTML for the table
 */
function createEditList(index) {

  var list = getList(index);
  var string = '';

  list.find('li').each( function(i) {
    var text = $(this).text();
    var url = $(this).children('a').attr('href') ? $(this).children('a').attr('href') : '';
    string += createEditItem(text, url);
  });
  
  return string;
  
} // end createEditList

/**
 * Render a table row for the "edit list" dialog.
 * @param string index The index, for the ID to prepopulate the field with.
 * @param string text The text to prepopulate the field with.
 * @param string url The URL to prepopulate the field with.
 * @return string HTML for the row.
 */
function createEditItem(text, url) {
  var string = '';
  string += '<tr class="editlist-item">';
  string += '<td class="editlist-item-handle"></td>';
  string += '<td>';
  string += '<input type="text" class="editlist-item-text" value="'+escape(text)+'" />';
  string += '<label class="hideLabel">Text</label>';
  string += '</td>';
  string += '<td>';
  string += '<input type="text" class="editlist-item-url" value="'+escape(url)+'" />';
  string += '<label class="hideLabel">Link (Optional)</label>';
  string += '</td>';
  string += '<td><input type="button" class="editlist-item-remove" value="Remove" /></td>';
  string += '</tr>';
  return string;
} // end createEditItem



/**
 * Ajax methods
 **/
 
function create(password) {
  $.ajax({
    data: {
      command: 'create',
      password: password,
      lists: serialize(),
      title: $('#header-logo').text()
    },
    success: function(d) {
      if (d.success) {
        var url = window.location.href.split("?")[0];
        url += '?board=' + d.data;
        window.location.href = url;
      }
      else notify('An error occurred.', d.messages);
    },
    failure: function() { 
      notify('A problem occurred while saving the board.');
    }
  });
} // end create()

function save(password) {
  $.ajax({
    data: {
      command: 'update',
      password: password,
      id: $('#container').data('id'),
      lists: serialize(),
      title: $('#header-logo').text()
    },
    success: function(d) {
      if (d.success) {
        setSaveButton(false);
      }
      else notify('An error occurred.', d.messages);
    },
    failure: function() { 
      notify('A problem occurred while saving the board.');
    }
  });
} // end save()

function load() {
  id = $('#container').data('id');
  $.ajax({
    data: {
      command: 'load',
      id: id
    },
    success: function(d) {
      if (d.success) {
        $('#container').data('id', id);
        $('#container').empty();
        $('#container').html(createBoard(d.data.lists));
        $('#header-logo').text(d.data.title);
        $('#container').sortable('refresh');
        setSaveButton(false);
      }
      else {
        $('#container').data('id', '');
        $('#container').removeAttr('data-id');
        setSaveButton(true);
        notify('Your board couldn\'t be loaded.', d.messages);
      }
    },
    failure: function() { 
      notify('A problem occurred while retrieving the board.');
    }
  });
} // end load()






/**
 * List HTML Renderers
 **/
 
/**
 * Return an HTML string for the given board object.
 * @param object board A board object (eg, loaded from
 *   AJAX) to create HTML for
 * @return string HTML for the board
 */
function createBoard(board) {
  var string = '';
  for (var i in board) {
    string += createList(board[i], i);
  }
  return string;
} // end createBoard()

/**
 * Return an HTML string for the given list object.
 * @param object list A list object, with item object array.
 * @return string HTML for the list
 */
function createList(list) {
  var string = '';
  string += '<div class="list">';
  string += '<h2>' + escape(list.title) + '</h2>';
  string += '<ul>';
  for (var i in list.items) {
    string += createItem(list.items[i], i);
  }
  string += '</ul>';
  string += '<button class="list-edit">Edit</button>';
  string += '</div>';
  return string;
} // end createList()

/**
 * Return an HTML string for the given list item object.
 * @param object item The object representing this list item
 * @return string HTML for the item
 */
function createItem(item) {
  var string = '';
  string += '<li>';
  if (item.url) string += '<a href="'+escape(item.url)+'">';
  string += escape(item.text);
  if (item.url) string += '</a>';
  string += '</li>';
  return string;
} // end createItem()



/**
 * UTILITY FUNCTIONS
 **/
 
/**
 * Read the current board and build a lists array from it.
 * @return object A lists array to submit to the controller.
 */
function serialize() {
  var lists = {};
  $('.list').each(function(i, list) {
    lists[i] = {
      title: $(list).children('h2:first-child').text(),
      items: []
    };
    $(list).children('ul').children('li').each(function(j, item) {
      lists[i].items[j] = { text: $(this).text() };
      var link = $(this).children('a:first-child');
      if (link.length) {
        lists[i].items[j].url = link.attr('href'); 
      }
    });
  });
  return lists;
}
 
/**
 * If the board has not been edited, the save button is diabled.
 * @param bool on Whether or not the button should be enabled.
 */
function setSaveButton(on) {
  if (on) {
    $('#header-saveboard').removeAttr('disabled');
    $('#header-saveboard').text('Save');
  }
  else {
    $('#header-saveboard').attr('disabled', 'disabled');
    $('#header-saveboard').text('Saved');
  }
} // end setSaveButton()


/**
 * Pop up a dialog with the specified text.
 * @param string text A message to display
 * @param array messages Optionally, a list of errors to display.
 */
function notify(text, messages) {
  $('#d-notify p').html(text);
  $('#d-notify ul').html('');
  if (messages && messages.length) {
    for (var i in messages) {
      $('#d-notify ul').append('<li>'+messages[i]+'</li>');
    }
  }
  $('#d-notify').dialog('open');
} // end notify()

/**
 * Escape HTML, lazily.
 * @param string string Text to escape
 * @return string de-HTML'd text
 */
function escape(string) {
  if (!string) return '';
  return string
    .replace(/&/g, '&amp;')
    .replace(/>/g, '&gt;')
    .replace(/</g, '&lt;')
    .replace(/"/g, '&quot;');
} // end escape()

/**
 * @return The width of the window.
 */
function windowWidth() {
  if (typeof window.innerWidth != 'undefined') {
    return window.innerWidth;
  }
  else if (
    typeof document.documentElement != 'undefined' &&
    typeof document.documentElement.clientWidth != 'undefined' && 
    document.documentElement.clientWidth != 0
  ) {
    return document.documentElement.clientWidth;
  }
  else {
    return document.getElementsByTagName('body')[0].clientWidth
  }
} // end windowWidth()

