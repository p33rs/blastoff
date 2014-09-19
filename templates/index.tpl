<!DOCTYPE html>
<html lang="en">
<!-- Hi there! -->
<head>

  <meta charset="utf-8" />
  <meta name="description" content="Antisocial Bookmarking." />
  <title>Blastoff!</title>
  
  <link href="style.css" rel="stylesheet" />
  <link href="style-jq.css" rel="stylesheet" />
  
  <script type="text/javascript" src="/j/jquery-1.7.2.min.js"></script>
  <script type="text/javascript" src="/j/jquery-ui-1.8.18.min.js"></script>
  <script type="text/javascript" src="scripts/blastoff.js"></script>
  
</head>

<body>

  <div id="jsWarn">
    Sorry, but you need javascript.
  </div>
  
  <div id="header">
    <h1 id="header-logo">
      <a href="/p/blastoff">blastoff!</a>
    </h1>
    <form id="header-logo-form">
      <label for="header-logo-new" class="hideLabel">Enter a new title:</label>
      <input type="text" name="header-logo-new" id="header-logo-new" />
      <input type="submit" value="Change" />
      <input type="button" value="Cancel" id="header-logo-cancel" />
    </form>
    <div id="header-buttons">
      <button id="header-newboard">New Board</button>
      <button id="header-newboard-confirm">Confirm New Board</button>
      <button id="header-newboard-cancel">Cancel New Board</button>
      <button id="header-addlist">Add List</button>
      <button id="header-saveboard">Save</button>
    </div>
  </div>
  
  <div id="container" data-id="<?php echo $id ?>">
  
  </div>
  
  <div style="clear:both;"></div>
  
  <div id="props">
    &#9400; <a href="/">jon pierce</a>
  </div>
  
  <div id="d-notify">
    <p></p>
    <ul></ul>
  </div>
  
  <div id="d-editlist">
    <form action="#" id="editlist-form" name="editlist-form">
      <dl id="editlist-fields">
        <dt><label for="editlist-name">List Name:</label></dt>
        <dd><input type="text" name="editlist-name" id="editlist-name" /></dd>
      </dl>
      <table>
        <thead>
          <tr>
            <th><span class="hideLabel">Drag to Move Row</span></th>
            <th>Text</th>
            <th>URL (optional)</th> 
            <th><span class="hideLabel">Remove</span></th>
          </tr>
        </thead>
        <tbody>
        <!-- editlist-item- + (text, url, remove), add -->
        </tbody>
      </table>
      <div id="editlist-item-add">
        <a href="#">Add New Link</a>
      </div>
      <input id="editlist-update" type="submit" value="Update" class="hideLabel" />
    </form>
  </div>
  
  <div id="d-addlist">
    <form action="#" id="addlist-form" name="addlist-form">
      <p><label for="addlist-name">A new list will be added to your form with this name.</label></p>
      <input type="text" name="addlist-name" id="addlist-name" />
      <input type="submit" id="addlist-confirm" value="Continue" class="hideLabel" />
    </form>
  </div>
  
  <div id="d-createboard">
    <p>After the save, you'll be redirected to your board's permanent URL - be sure to bookmark it.</p>
    <form action="#" name="createboard-form" id="createboard-form">
      <p><label for="createboard-password1">Pick a password, six characters or more:</label></p>
      <input type="password" name="createboard-password1" id="createboard-password1" />
      <p><label for="createboard-password2">Retype your password:</label></p>
      <input type="password" name="createboard-password2" id="createboard-password2" />
      <ul id="createboard-passwordErrors"></ul>
      <input type="submit" id="createboard-confirm" value="Continue" class="hideLabel" />
    </form>
  </div>
  
  <div id="d-saveboard">
    <form action="#" name="saveboard-form" id="saveboard-form">
      <p>
        <label for="saveboard-password">What is the password for this board?</label>
      </p>
      <input type="password" name="saveboard-password" id="saveboard-password" />
      <input type="submit" id="saveboard-confirm" value="Continue" class="hideLabel" />
    </form>
  </div>
  
</body>

</html>